<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Lightweight validation engine with prepared-statement based
 * unique/exists rules. Usage:
 *
 *   $v = Validator::make($data, ['email' => 'required|email|max:190|unique:users,email']);
 *   if ($v->fails()) { ... }
 *
 * File rules: `file|mimes:png,jpg,pdf|max:2048` (max is in KB).
 */
final class Validator
{
    private array $errors = [];
    private array $validated = [];
    private array $data = [];

    private const MESSAGES = [
        'required' => 'The :field field is required.',
        'email'    => 'The :field must be a valid email address.',
        'min'      => 'The :field must be at least :min characters.',
        'max'      => 'The :field may not be greater than :max characters.',
        'between'  => 'The :field must be between :min and :max.',
        'confirmed'=> 'The :field confirmation does not match.',
        'same'     => 'The :field and :other must match.',
        'different'=> 'The :field and :other must be different.',
        'numeric'  => 'The :field must be a number.',
        'integer'  => 'The :field must be an integer.',
        'url'      => 'The :field format is invalid.',
        'date'     => 'The :field is not a valid date.',
        'unique'   => 'The :field has already been taken.',
        'exists'   => 'The selected :field is invalid.',
        'in'       => 'The selected :field is invalid.',
        'alpha'    => 'The :field may only contain letters.',
        'alpha_num'=> 'The :field may only contain letters and numbers.',
        'alpha_dash'=> 'The :field may only contain letters, numbers, dashes and underscores.',
        'regex'    => 'The :field format is invalid.',
        'boolean'  => 'The :field must be true or false.',
        'string'   => 'The :field must be a string.',
        'array'    => 'The :field must be an array.',
        'mimes'    => 'The :field must be a file of type: :mimes.',
        'file_max' => 'The :field may not be greater than :max kilobytes.',
        'file'     => 'The :field must be a file.',
        'ip'       => 'The :field must be a valid IP address.',
    ];

    public function __construct(array $data)
    {
        // Merge uploaded files so `file`/`mimes` rules can read $_FILES values.
        $this->data = array_merge($_FILES ?: [], $data);
    }

    public static function make(array $data, array $rules): self
    {
        $instance = new self($data);
        foreach ($rules as $field => $fieldRules) {
            $fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            $instance->processField($field, $fieldRules);
        }

        return $instance;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function errorBag(): string
    {
        $first = reset($this->errors);

        return is_array($first) ? implode(' ', array_map('strval', array_values($first))) : '';
    }

    /**
     * Only the values of fields that were validated and passed.
     */
    public function validated(): array
    {
        return $this->validated;
    }

    // -------------------------------------------------------------------------

    private function processField(string $field, array $rules): void
    {
        $raw = $this->data[$field] ?? null;
        $isFile = isset($_FILES[$field]) && is_array($_FILES[$field]);

        // File rules
        if ($isFile && in_array('file', $rules, true)) {
            $this->processFile($field, $rules);
            return;
        }

        $hasFileRule = in_array('file', $rules, true);
        // When a file rule is present but no file uploaded, treat as file
        if ($hasFileRule && $isFile) {
            $this->processFile($field, $rules);
            return;
        }

        $value = $isFile ? null : $this->normalizeValue($raw);

        // nullable short-circuit
        if (in_array('nullable', $rules, true) && ($value === null || $value === '')) {
            if (!$this->containsRule($rules, 'nullable')) {
                return;
            }
            $this->validated[$field] = null;
            return;
        }

        foreach ($rules as $rule) {
            if ($rule === 'nullable' || $rule === 'file') {
                continue;
            }
            $this->applyRule($field, $rule, $value);
            if (isset($this->errors[$field])) {
                return;
            }
        }

        $this->validated[$field] = $value;
    }

    private function containsRule(array $rules, string $name): bool
    {
        foreach ($rules as $rule) {
            if (explode(':', $rule, 2)[0] === $name) {
                return true;
            }
        }

        return false;
    }

    private function processFile(string $field, array $rules): void
    {
        $file = $_FILES[$field];

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            if ($this->containsRule($rules, 'required')) {
                $this->addError($field, 'required');
            }
            return;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[$field][] = 'The ' . str_replace('_', ' ', $field) . ' file failed to upload.';
            return;
        }

        foreach ($rules as $rule) {
            $ruleName = explode(':', $rule, 2)[0];
            if ($ruleName === 'required' || $ruleName === 'nullable' || $ruleName === 'file') {
                continue;
            }

            $failed = false;
            if ($ruleName === 'mimes') {
                $allowed = explode(',', explode(':', $rule, 2)[1] ?? '');
                $mime = (string)(mime_content_type($file['tmp_name']) ?: $file['type']);
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $failed = !in_array($extension, array_map('strtolower', $allowed), true)
                    && !in_array($mime, $allowed, true);
            } elseif ($ruleName === 'max') {
                $kb = (float)explode(':', $rule, 2)[1];
                $failed = ($file['size'] / 1024) > $kb;
            } elseif ($ruleName === 'min') {
                $kb = (float)explode(':', $rule, 2)[1];
                $failed = ($file['size'] / 1024) < $kb;
            }

            if ($failed) {
                $this->errors[$field][] = strtr(
                    self::MESSAGES[$ruleName] ?? 'The :field file is invalid.',
                    [':field' => str_replace('_', ' ', $field), ':mimes' => explode(':', $rule, 2)[1] ?? '', ':max' => explode(':', $rule, 2)[1] ?? '', ':min' => explode(':', $rule, 2)[1] ?? '']
                );
                return;
            }
        }

        $this->validated[$field] = $file;
    }

    private function normalizeValue(mixed $value): mixed
    {
        return is_string($value) ? trim($value) : $value;
    }

    private function applyRule(string $field, string $rule, mixed &$value): void
    {
        [$name, $params] = $this->parseRule($rule);
        $failed = false;

        if ($name === 'required') {
            $failed = $value === null || $value === '' || (is_array($value) && $value === []);
        } elseif ($name === 'email') {
            $failed = !is_string($value) || filter_var($value, FILTER_VALIDATE_EMAIL) === false;
        } elseif ($name === 'min') {
            $failed = $this->strLength($value) < (int)($params[0] ?? 0);
        } elseif ($name === 'max') {
            $failed = $this->strLength($value) > (int)($params[0] ?? 0);
        } elseif ($name === 'between') {
            $length = $this->strLength($value);
            $failed = $length < (int)($params[0] ?? 0) || $length > (int)($params[1] ?? PHP_INT_MAX);
        } elseif ($name === 'confirmed') {
            $failed = ($this->data[$field] ?? null) !== ($this->data[$field . '_confirmation'] ?? null);
        } elseif ($name === 'same') {
            $failed = ($this->data[$field] ?? null) !== ($this->data[$params[0] ?? ''] ?? null);
        } elseif ($name === 'different') {
            $failed = ($this->data[$field] ?? null) === ($this->data[$params[0] ?? ''] ?? null);
        } elseif ($name === 'numeric') {
            $failed = !is_numeric($value);
        } elseif ($name === 'integer') {
            $failed = filter_var($value, FILTER_VALIDATE_INT) === false;
        } elseif ($name === 'url') {
            $failed = !is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false;
        } elseif ($name === 'date') {
            $failed = !is_string($value) || strtotime($value) === false;
        } elseif ($name === 'ip') {
            $failed = filter_var($value, FILTER_VALIDATE_IP) === false;
        } elseif ($name === 'alpha') {
            $failed = !is_string($value) || preg_match('/^[a-zA-Z]+$/', $value) !== 1;
        } elseif ($name === 'alpha_num') {
            $failed = !is_string($value) || preg_match('/^[a-zA-Z0-9]+$/', $value) !== 1;
        } elseif ($name === 'alpha_dash') {
            $failed = !is_string($value) || preg_match('/^[a-zA-Z0-9_-]+$/', $value) !== 1;
        } elseif ($name === 'regex') {
            $failed = (bool)($params[0] ?? '') && preg_match($params[0], (string)$value) !== 1;
        } elseif ($name === 'boolean') {
            $failed = !in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true);
        } elseif ($name === 'string') {
            $failed = $value !== null && !is_string($value);
        } elseif ($name === 'array') {
            $failed = !is_array($this->data[$field] ?? null);
        } elseif ($name === 'in') {
            $failed = !in_array($value, $params, true);
        } elseif ($name === 'unique') {
            $failed = $this->ruleUnique($value, $params);
        } elseif ($name === 'exists') {
            $failed = $this->ruleExists($value, $params);
        } else {
            return;
        }

        if ($failed) {
            $this->addError($field, $name, $params);
        }
    }

    private function ruleUnique(mixed $value, array $params): bool
    {
        $table = $params[0] ?? null;
        $column = $params[1] ?? 'email';
        if ($table === null || $value === null) {
            return false;
        }

        $pdo = Database::pdo();
        $except = $params[2] ?? null;

        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :value";
        if ($except !== null && is_numeric($except)) {
            $sql .= ' AND `id` <> :except';
        }
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':value', (string)$value);
        if ($except !== null && is_numeric($except)) {
            $stmt->bindValue(':except', (int)$except, \PDO::PARAM_INT);
        }
        $stmt->execute();

        return (int)$stmt->fetchColumn() > 0;
    }

    private function ruleExists(mixed $value, array $params): bool
    {
        $table = $params[0] ?? null;
        $column = $params[1] ?? 'id';
        if ($table === null || $value === null || $value === '') {
            return false;
        }

        $stmt = Database::pdo()->prepare("SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :value");
        $stmt->bindValue(':value', (string)$value);
        $stmt->execute();

        return (int)$stmt->fetchColumn() > 0;
    }

    private function parseRule(string $rule): array
    {
        $parts = explode(':', $rule, 2);
        $name = $parts[0];
        $params = [];
        if (isset($parts[1])) {
            $params = explode(',', $parts[1]);
        }

        return [$name, $params];
    }

    private function strLength(mixed $value): int
    {
        if ($value === null) {
            return 0;
        }

        return function_exists('mb_strlen') ? mb_strlen((string)$value, 'UTF-8') : strlen((string)$value);
    }

    private function addError(string $field, string $rule, array $params = []): void
    {
        $message = self::MESSAGES[$rule] ?? 'The :field field is invalid.';
        $replacements = [
            ':field' => str_replace('_', ' ', $field),
            ':min'   => $params[0] ?? '',
            ':max'   => $params[0] ?? '',
            ':other' => $params[0] ?? '',
            ':mimes' => implode(', ', $params),
        ];
        $this->errors[$field][] = strtr($message, $replacements);
    }
}