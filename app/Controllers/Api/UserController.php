<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;

final class UserController extends Controller
{
    public function me(): string
    {
        $user = $this->request->getAttribute('api_user');

        return $this->json([
            'id'    => (int)$user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'status'=> $user['status'],
        ]);
    }
}