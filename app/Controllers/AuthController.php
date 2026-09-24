<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Services\AuthService;

final class AuthController extends Controller
{
    public function showLogin(): string
    {
        return $this->view('auth/login', ['pageTitle' => 'Sign In'], 'layouts/auth');
    }

    public function login()
    {
        $data = $this->validate([
            'email'    => 'required|email|max:190',
            'password' => 'required|min:1',
        ]);

        $result = AuthService::instance()->login(
            $data['email'],
            $data['password'],
            (bool)($this->request->input('remember') ?? false)
        );

        if (!$result['success']) {
            Session::flash('error', $result['message']);

            return $this->back('/login');
        }

        $this->flashSuccess($result['message']);

        $this->redirect($result['redirect'] ?? '/home');
    }

    public function showRegister(): string
    {
        if (!(bool)setting('security.register_enabled', true)) {
            Session::flash('error', 'Registration is disabled.');

            $this->redirect('/login');
        }

        return $this->view('auth/register', ['pageTitle' => 'Create Account'], 'layouts/auth');
    }

    public function register()
    {
        $data = $this->validate([
            'name'                  => 'required|max:150',
            'email'                 => 'required|email|max:190|unique:users,email',
            'password'              => 'required|min:' . (int)setting('security.min_password_length', 8)
                                   . '|confirmed',
            'password_confirmation' => 'required',
            'terms'                 => 'required',
        ]);

        $result = AuthService::instance()->register([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
        ]);

        if (!$result['success']) {
            Session::flash('error', $result['message']);

            return $this->back('/register');
        }

        $this->flashSuccess($result['message'] . ' Please check your inbox to verify your email.');

        $this->redirect('/login');
    }

    public function verifyEmail(string $token)
    {
        $result = AuthService::instance()->verifyEmail($token);

        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('/login');
    }

    public function resendVerification()
    {
        $email = (string)$this->request->input('email', '');
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            Session::flash('error', 'A valid email address is required.');

            return $this->back('/login');
        }

        $result = AuthService::instance()->resendVerification($email);
        Session::flash($result['success'] ? 'success' : 'error', $result['message']);

        $this->redirect('/login');
    }

    public function showForgot(): string
    {
        return $this->view('auth/forgot', ['pageTitle' => 'Forgot Password'], 'layouts/auth');
    }

    public function forgot()
    {
        $data = $this->validate([
            'email' => 'required|email|max:190',
        ]);

        AuthService::instance()->forgot($data['email']);

        Session::flash('success', 'If that email exists, a password reset link has been sent.');

        $this->redirect('/login');
    }

    public function showReset(string $token): string
    {
        return $this->view('auth/reset', [
            'pageTitle' => 'Reset Password',
            'token'     => $token,
        ], 'layouts/auth');
    }

    public function reset()
    {
        $data = $this->validate([
            'token'                 => 'required',
            'email'                 => 'required|email|max:190',
            'password'              => 'required|min:' . (int)setting('security.min_password_length', 8)
                                   . '|confirmed',
            'password_confirmation' => 'required',
        ]);

        $result = AuthService::instance()->resetPassword(
            $data['token'],
            $data['email'],
            $data['password'],
            $data['password_confirmation']
        );

        Session::flash($result['success'] ? 'success' : 'error', $result['message']);

        $this->redirect('/login');
    }

    public function logout()
    {
        AuthService::instance()->logout();

        $this->redirect('/login');
    }
}