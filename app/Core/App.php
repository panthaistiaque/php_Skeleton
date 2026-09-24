<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\AuthMiddleware;
use App\Middleware\ApiAuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\PermissionMiddleware;

/**
 * Front-controller dispatcher: routing + middleware pipeline + output.
 */
final class App
{
    private readonly Router $router;
    private readonly Request $request;

    public function __construct(array $routes)
    {
        $this->router = new Router($routes);
        $this->request = new Request();
    }

    public function request(): Request
    {
        return $this->request;
    }

    public function dispatch(): void
    {
        try {
            $this->handle();
        } catch (RedirectResponse $e) {
            header('Location: ' . $e->url(), true, $e->statusCode());
            exit;
        } catch (HttpException $e) {
            $this->renderHttpError($e);
        } catch (\Throwable $e) {
            Logger::exception($e);
            $this->renderFatal($e);
        }
    }

    private function handle(): void
    {
        $path = $this->computePath();
        $this->request->setPath($path);

        $route = $this->router->match($this->request->method(), $path);
        if ($route === null) {
            throw new HttpException('404 Not Found', 404);
        }

        $controllerClass = 'App\\Controllers\\' . $route['controller'];
        if (!class_exists($controllerClass)) {
            throw new HttpException("Controller [{$controllerClass}] not found.", 500);
        }

        /** @var Controller $controller */
        $controller = new $controllerClass($this->request);

        $handler = function () use ($controller, $route) {
            return $controller->{$route['action']}(...array_values($route['params']));
        };

        // Build middleware pipeline (outer -> inner).
        foreach (array_reverse($route['middleware']) as $middleware) {
            $instance = $this->resolveMiddleware($middleware);
            $previous = $handler;
            $handler = function () use ($instance, $previous) {
                return $instance->handle($this->request, $previous);
            };
        }

        $output = $handler();

        $this->trackPageAccess($route);

        if (is_array($output)) {
            echo Response::json($output);
            return;
        }
        if (is_string($output) && $output !== '') {
            echo $output;
            return;
        }
        if ($output === null) {
            return;
        }
        if (is_scalar($output)) {
            echo (string)$output;
        }
    }

    private function trackPageAccess(array $route): void
    {
        if ((bool)setting('audit.track_page_views', false) === false) {
            return;
        }
        if (!in_array('auth', $route['middleware'], true)) {
            return;
        }
        if (!$this->request->isMethod('GET')) {
            return;
        }

        $userId = Session::getAuth();
        if ($userId === null) {
            return;
        }

        \App\Services\AuditService::instance()->log([
            'user_id'   => (int)$userId,
            'action'    => 'page_access',
            'module'    => 'routing',
            'description' => 'Visited ' . $this->request->path(),
            'method'    => $this->request->method(),
            'url'       => substr($this->request->fullUrl(), 0, 500),
            'ip_address'=> $this->request->ip(),
            'user_agent'=> $this->request->userAgent(),
        ]);
    }

    private function resolveMiddleware(string $definition): \App\Middleware\MiddlewareInterface
    {
        [$name, $parameter] = array_pad(explode(':', $definition, 2), 2, null);

        return match ($name) {
            'auth'       => new AuthMiddleware(),
            'guest'      => new GuestMiddleware(),
            'csrf'       => new CsrfMiddleware(),
            'permission' => new PermissionMiddleware($parameter ?? ''),
            'api'        => new ApiAuthMiddleware(),
            default      => throw new HttpException("Middleware [{$name}] is not registered.", 500),
        };
    }

    private function computePath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = (string)parse_url($uri, PHP_URL_PATH);
        if ($path === '' || $path === false) {
            $path = '/';
        }

        $base = $this->resolveBasePath();
        if ($base !== '' && $base !== '/' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        return $path === '' ? '/' : '/' . ltrim($path, '/');
    }

    private function resolveBasePath(): string
    {
        $url = (string)Config::get('app.url', '');
        if ($url !== '') {
            $base = parse_url($url, PHP_URL_PATH) ?: '';

            return rtrim($base, '/');
        }

        // Auto-detect: /php_Skeleton/public/index.php -> /php_Skeleton
        $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
        $dirname = rtrim(dirname($script), '/');
        if (str_ends_with($dirname, '/public')) {
            return rtrim(dirname($dirname), '/');
        }

        return $dirname;
    }

    private function renderHttpError(HttpException $e): void
    {
        http_response_code($e->statusCode);

        if ($this->request->isAjax()) {
            echo Response::json(['message' => $e->getMessage(), 'status' => $e->statusCode], $e->statusCode);
            return;
        }

        $view = match (true) {
            $e->statusCode === 403 => 'errors/403',
            $e->statusCode === 404 => 'errors/404',
            $e->statusCode === 419 => 'errors/419',
            $e->statusCode === 429 => 'errors/429',
            default                => 'errors/500',
        };

        $html = View::render($view, [
            'status'  => $e->statusCode,
            'message' => $e->getMessage(),
        ]);

        echo $html;
    }

    private function renderFatal(\Throwable $e): void
    {
        http_response_code(500);

        if ((bool)Config::get('app.debug', false)) {
            echo '<h1>500 Internal Server Error</h1>'
                . '<pre style="white-space:pre-wrap;font:12px/1.4 monospace;background:#f6f8fa;padding:16px;border-radius:6px;">'
                . e(get_class($e)) . ': ' . e($e->getMessage())
                . "\n\n" . e($e->getFile() . ':' . $e->getLine())
                . "\n\n" . e($e->getTraceAsString())
                . '</pre>';
            return;
        }

        echo View::render('errors/500', ['message' => 'An internal error occurred. If this persists, contact the administrator.']);
    }
}