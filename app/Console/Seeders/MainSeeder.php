<?php

declare(strict_types=1);

namespace App\Console\Seeders;

use App\Core\Database;
use App\Models\User;

/**
 * Post-SQL seeder: guarantees a super administrator exists and prints a summary.
 */
final class MainSeeder
{
    public function run(): void
    {
        $pdo = Database::pdo();

        $counts = [
            'users'       => (int)$pdo->query('SELECT COUNT(*) FROM `users`')->fetchColumn(),
            'roles'       => (int)$pdo->query('SELECT COUNT(*) FROM `roles`')->fetchColumn(),
            'permissions' => (int)$pdo->query('SELECT COUNT(*) FROM `permissions`')->fetchColumn(),
            'menus'       => (int)$pdo->query('SELECT COUNT(*) FROM `menus`')->fetchColumn(),
            'settings'    => (int)$pdo->query('SELECT COUNT(*) FROM `settings`')->fetchColumn(),
        ];

        $this->ensureSuperAdmin();

        echo "\nSeeder summary:\n";
        foreach ($counts as $label => $count) {
            printf("  %-12s %d\n", $label . ':', $count);
        }
    }

    private function ensureSuperAdmin(): void
    {
        $pdo = Database::pdo();

        $hasSuperAdmin = (int)$pdo->query(
            'SELECT COUNT(*) FROM `user_role` ur
             JOIN `roles` r ON r.id = ur.role_id
             WHERE r.is_system = 1'
        )->fetchColumn();

        if ($hasSuperAdmin > 0) {
            return;
        }

        // Fallback: use seed default if it exists, otherwise create from env.
        $user = User::findByEmail('superadmin@example.com');
        $passwordHash = password_hash('Password@123', PASSWORD_DEFAULT);

        if ($user === null) {
            $name = \App\Core\Config::get('app.name', 'Skeleton App') . ' Administrator';
            $userId = (int)User::insertGetId([
                'name'               => $name,
                'email'              => 'superadmin@example.com',
                'password'           => $passwordHash,
                'status'             => 'active',
                'email_verified_at'  => date('Y-m-d H:i:s'),
                'password_changed_at'=> date('Y-m-d H:i:s'),
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ]);
        } else {
            $userId = (int)$user['id'];
        }

        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO `user_role` (`user_id`, `role_id`)
             SELECT :uid, `id` FROM `roles` WHERE `is_system` = 1 LIMIT 1'
        );
        $stmt->execute([':uid' => $userId]);

        echo "  Super administrator ensured: superadmin@example.com / Password@123\n";
    }
}