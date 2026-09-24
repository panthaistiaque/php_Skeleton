<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Repositories\UserRepository;

final class StatsController extends Controller
{
    public function index(): string
    {
        $stats = UserRepository::dashboardStats();

        return $this->json([
            'data' => $stats,
        ]);
    }
}