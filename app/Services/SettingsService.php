<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Models\Setting;

/**
 * DB-backed application settings with per-request caching,
 * typed values and optional AES-256-CBC encryption.
 */
final class SettingsService
{
    private static ?self $instance = null;
    private array $cache = [];

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        // Load all autoload+rows into cache on first use.
        foreach (Setting::allRows() as $row) {
            $this->cache[$row['setting_key']] = $this->decode($row);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $value = Config::get('default_settings.' . $key, $default);

        return $value;
    }

    public function set(string $key, mixed $value, array $options = []): void
    {
        $type = $options['type'] ?? $this->detectType($value);
        $group = $options['group'] ?? $this->inferGroup($key);
        $encrypt = (bool)($options['encrypt'] ?? false);
        $autoload = (bool)($options['autoload'] ?? true);

        $encoded = $this->encodeValue($value, $type, $encrypt);

        Setting::upsert([
            'setting_key'   => $key,
            'setting_value' => $encoded,
            'setting_type'  => $type,
            'group_name'    => $group,
            'is_encrypted'  => $encrypt ? 1 : 0,
            'autoload'      => $autoload ? 1 : 0,
        ]);

        $this->cache[$key] = $value;
    }

    public function forget(string $key): void
    {
        unset($this->cache[$key]);
    }

    public function flush(): void
    {
        $this->cache = [];
        foreach (Setting::allRows() as $row) {
            $this->cache[$row['setting_key']] = $this->decode($row);
        }
    }

    /**
     * key => value map for a group (e.g. 'app', 'organization', 'smtp').
     */
    public function allFlattened(string $group): array
    {
        $result = [];
        foreach (Config::get('default_settings', []) as $key => $value) {
            if (str_starts_with($key, $group . '.')) {
                $simpleKey = substr($key, strlen($group) + 1);
                $result[$simpleKey] = $this->get($key, $value);
            }
        }

        foreach ($this->cache as $key => $value) {
            if (str_starts_with((string)$key, $group . '.')) {
                $result[substr((string)$key, strlen($group) + 1)] = $value;
            }
        }

        // DB rows not covered by defaults (e.g. org data)
        foreach (Setting::where(['group_name' => $group]) as $row) {
            $key = $row['setting_key'];
            if (str_starts_with($key, $group . '.')) {
                $result[substr($key, strlen($group) + 1)] = $this->decode($row);
            }
        }

        return $result;
    }

    // -- encoding -------------------------------------------------------------

    /**
     * @param array{setting_value:mixed,setting_type:string,is_encrypted:int} $row
     */
    private function decode(array $row): mixed
    {
        $value = $row['setting_value'];

        if ((int)($row['is_encrypted'] ?? 0) === 1 && is_string($value) && $value !== '') {
            $value = $this->decrypt($value);
        }

        return $this->castValue($value, (string)($row['setting_type'] ?? 'string'));
    }

    private function encodeValue(mixed $value, string $type, bool $encrypt): ?string
    {
        if ($value === null) {
            return null;
        }

        $raw = match ($type) {
            'bool'    => $value ? '1' : '0',
            'int'     => (string)(int)$value,
            'json'    => json_encode($value, JSON_UNESCAPED_UNICODE),
            'array'   => json_encode($value, JSON_UNESCAPED_UNICODE),
            default   => (string)$value,
        };

        if ($raw === false || $raw === null) {
            return '';
        }

        return $encrypt ? $this->encrypt($raw) : $raw;
    }

    private function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'bool'  => is_string($value) ? (filter_var($value, FILTER_VALIDATE_BOOL) || $value === '1') : (bool)$value,
            'int'   => (int)$value,
            'json', 'array' => is_string($value) && $value !== '' ? (json_decode($value, true) ?? []) : [],
            default => $value,
        };
    }

    private function detectType(mixed $value): string
    {
        if (is_bool($value)) {
            return 'bool';
        }
        if (is_int($value)) {
            return 'int';
        }
        if (is_array($value)) {
            return 'json';
        }

        return 'string';
    }

    private function inferGroup(string $key): string
    {
        return explode('.', $key, 2)[0];
    }

    private function encrypt(string $value): string
    {
        $key = $this->cryptoKey();
        $iv = random_bytes(16);

        return base64_encode($iv) . ':' . base64_encode(openssl_encrypt($value, 'aes-256-cbc', $key, 0, $iv));
    }

    private function decrypt(string $value): string
    {
        $key = $this->cryptoKey();
        [$iv64, $enc64] = array_pad(explode(':', $value, 2), 2, '');
        $iv = base64_decode($iv64, true) ?: str_repeat("\0", 16);

        return (string)openssl_decrypt(base64_decode($enc64) ?: '', 'aes-256-cbc', $key, 0, $iv);
    }

    private function cryptoKey(): string
    {
        $key = (string)Config::get('app.key', '');

        return hash('sha256', $key, true);
    }
}