<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\AuditRepository;
use App\Repositories\UserRepository;

final class DashboardController extends Controller
{
    public function index(): string
    {
        $stats = UserRepository::dashboardStats();
        $trend = UserRepository::loginTrend(14);

        $loginPage = max(1, (int)$this->request->query('page', 1));
        $logins = AuditRepository::loginHistory(
            ['min_id' => AuditRepository::latestMinId('login_history', 40)],
            $loginPage,
            5
        );

        $activityPage = max(1, (int)$this->request->query('activities_page', 1));
        $activities = AuditRepository::activities(
            ['min_id' => AuditRepository::latestMinId('activities', 40)],
            $activityPage,
            5
        );

        return $this->view('dashboard/index', [
            'pageTitle'            => 'Dashboard',
            'stats'                => $stats,
            'trend'                => $trend,
            'recentActivities'     => $activities['items'],
            'activitiesPagination' => $activities,
            'recentLogins'         => $logins['items'],
            'loginsPagination'     => $logins,
        ]);
    }
}