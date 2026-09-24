<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;

final class HomeController extends Controller
{
    public function index()
    {
        if (Session::isAuthenticated()) {
            Response::redirect('/home');
        }

        Response::redirect('/login');
    }

    public function home(): string
    {
        $user = $this->user();

        return $this->view('home/index', [
            'pageTitle' => 'Home',
            'homeUser'  => $user,
            'homeRoles' => User::rolesFor((int)$user['id']),
        ]);
    }
}