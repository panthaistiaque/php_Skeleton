<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\PermissionService;
use App\Services\SettingsService;

/**
 * Base controller: shared view/response/validation/authorization helpers.
 */
abstract class Controller
{
    public function __construct(protected Request $request) {}

    // -- Responses ------------------------------------------------------------

    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): string
    {
        if ($layout === 'layouts/main' && Session::isAuthenticated()) {
            $data = array_merge($this->appContext(), $data);
        }

        return View::render($view, $data, $layout);
    }

    protected function json(mixed $data, int $statusCode = 200): string
    {
        return Response::json($data, $statusCode);
    }

    protected function redirect(string $url): never
    {
        Response::redirect($url);
    }

    protected function back(?string $fallback = '/'): never
    {
        Response::back($fallback);
    }

    protected function noContent(): string
    {
        return Response::noContent();
    }

    // -- Input / validation ---------------------------------------------------

    protected function validate(array $rules): array
    {
        $validator = Validator::make($this->request->all(), $rules);
        if ($validator->fails()) {
            Session::withOld($this->request->all());
            foreach ($validator->errors() as $field => $messages) {
                Session::flash('validation.' . $field, $messages);
            }
            $this->back('/');
        }

        return $validator->validated();
    }

    protected function flashError(string $message): void
    {
        Session::flash('error', $message);
    }

    protected function flashSuccess(string $message): void
    {
        Session::flash('success', $message);
    }

    // -- Auth / authorization ---------------------------------------------------

    protected function user(): ?array
    {
        $id = Session::getAuth();

        return $id !== null ? User::find((int)$id) : null;
    }

    protected function userId(): int
    {
        return (int)Session::getAuth();
    }

    /**
     * @throws HttpException 403 when the signed-in user lacks the permission.
     */
    protected function authorize(string $permission): void
    {
        if (!PermissionService::instance()->has($permission)) {
            throw new HttpException('You do not have permission to perform this action.', 403);
        }
    }

    protected function setting(string $key, mixed $default = null): mixed
    {
        return SettingsService::instance()->get($key, $default);
    }

    // -- Shared layout context ------------------------------------------------

    private function appContext(): array
    {
        $user = $this->user();

        return [
            'appUser'            => $user,
            'appSettings'        => SettingsService::instance()->allFlattened('app'),
            'orgSettings'        => SettingsService::instance()->allFlattened('organization'),
            'sidebarMenus'       => PermissionService::instance()->sidebarMenus(),
            'unreadNotifications'=> $user ? NotificationService::instance()->unreadCount((int)$user['id']) : 0,
            'currentPath'        => $this->request->path(),
        ];
    }

    protected function logActivity(string $action, string $description, string $module = 'general', array $context = []): void
    {
        AuditService::instance()->log([
            'user_id'    => Session::getAuth(),
            'action'     => $action,
            'module'     => $module,
            'description'=> $description,
            'method'     => $this->request->method(),
            'url'        => substr($this->request->fullUrl(), 0, 500),
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'context'    => $context !== [] ? json_encode($context, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }
}