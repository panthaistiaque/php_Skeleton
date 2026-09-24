<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Database;
use App\Models\User;
use App\Services\AuditService;

/**
 * php console admin:create --name=... --email=... --password=...
 */
final class AdminCommand
{
    public function execute(array $options): int
    {
        $email = (string)($options['email'] ?? 'admin@example.com');
        $password = (string)($options['password'] ?? '');
        $name = (string)($options['name'] ?? 'Super Administrator');

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            fwrite(STDERR, "Invalid email address.\n");

            return 1;
        }
        if (strlen($password) < 8) {
            fwrite(STDERR, "Password must be at least 8 characters.\n");

            return 1;
        }

        $existing = User::findByEmail($email);
        if ($existing !== null) {
            // Refresh password + assign super admin role again.
            User::updateById((int)$existing['id'], [
                'password'            => password_hash($password, PASSWORD_DEFAULT),
                'password_changed_at' => date('Y-m-d H:i:s'),
                'status'              => 'active',
            ]);
            $userId = (int)$existing['id'];
            echo "Existing user updated: {$email}\n";
        } else {
            $userId = (int)User::insertGetId([
                'name'               => $name,
                'email'              => $email,
                'password'           => password_hash($password, PASSWORD_DEFAULT),
                'status'             => 'active',
                'email_verified_at'  => date('Y-m-d H:i:s'),
                'password_changed_at'=> date('Y-m-d H:i:s'),
            ]);
            echo "Super administrator created: {$email}\n";
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO `user_role` (`user_id`, `role_id`)
             SELECT :uid, `id` FROM `roles` WHERE `is_system` = 1 AND `slug` = "super-admin" LIMIT 1'
        );
        $stmt->execute([':uid' => $userId]);

        AuditService::instance()->log([
            'user_id'     => $userId,
            'action'      => 'admin_created',
            'module'      => 'cli',
            'description' => 'Super administrator ensured via CLI (' . $email . ')',
            'ip_address'  => 'cli',
        ]);

        echo "Role: super-admin (system role).\n";
        echo "You may sign in at " . rtrim((string)\App\Core\Config::get('app.url', ''), '/') . "/login\n";

        return 0;
    }
}