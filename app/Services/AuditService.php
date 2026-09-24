<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\LoginHistory;
use App\Models\SecurityEvent;

/**
 * Writes audit records (activities, logins, security events).
 */
final class AuditService
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Generic activity record. Skipped when activity logging is disabled.
     */
    public function log(array $data): void
    {
        if (!(bool)setting('audit.log_activities', true)) {
            return;
        }

        ActivityLog::createEntry([
            'user_id'    => $data['user_id'] ?? null,
            'action'     => $data['action'] ?? 'generic',
            'module'     => $data['module'] ?? 'general',
            'description'=> $data['description'] ?? '',
            'method'     => $data['method'] ?? null,
            'url'        => $data['url'] ?? null,
            'ip_address' => $data['ip_address'] ?? request_ip(),
            'user_agent' => $data['user_agent'] ?? null,
            'context'    => $data['context'] ?? null,
        ]);
    }

    /**
     * Login history is always recorded (it powers security reports).
     */
    public function login(array $data): void
    {
        LoginHistory::createEntry($data);
    }

    /**
     * Security events respect the audit.log_security_events toggle.
     */
    public function securityEvent(array $data): void
    {
        if (!(bool)setting('audit.log_security_events', true)) {
            return;
        }

        SecurityEvent::createEvent([
            'user_id'    => $data['user_id'] ?? null,
            'event_type' => $data['event_type'] ?? 'generic',
            'severity'   => $data['severity'] ?? 'info',
            'message'    => $data['message'] ?? '',
            'ip_address' => $data['ip_address'] ?? request_ip(),
            'user_agent' => $data['user_agent'] ?? null,
            'context'    => $data['context'] ?? null,
        ]);
    }
}