<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Response;
use App\Core\Session;
use App\Models\Upload;
use App\Models\User;

final class UploadController extends Controller
{
    private const ALLOWED_MIME = [
        'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'png' => 'image/png',
        'gif' => 'image/gif', 'webp' => 'image/webp', 'pdf' => 'application/pdf',
        'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'csv' => 'text/csv', 'txt' => 'text/plain', 'zip' => 'application/zip',
        'json' => 'application/json', 'sql' => 'application/sql',
    ];

    public function index(): string
    {
        $page = (int)($this->request->query('page', 1));
        $perPage = (int)setting('general.records_per_page', 20);
        $category = trim((string)$this->request->query('category', ''));

        $pdo = \App\Core\Database::pdo();

        $where = '';
        $params = [];
        if ($category !== '') {
            $where = ' WHERE f.category = :category';
            $params[':category'] = $category;
        }

        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM `uploads` f' . $where);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $lastPage = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $lastPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare(
            'SELECT f.*, u.name AS user_name
             FROM `uploads` f
             LEFT JOIN `users` u ON u.id = f.user_id'
            . $where . '
             ORDER BY f.id DESC
             LIMIT :limit OFFSET :offset'
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $categories = $pdo->query(
            'SELECT category, COUNT(*) AS total FROM `uploads`
             WHERE category IS NOT NULL AND category <> ""
             GROUP BY category ORDER BY category'
        )->fetchAll();

        return $this->view('files/index', [
            'pageTitle'  => 'File Uploads',
            'files'      => $stmt->fetchAll(),
            'categories' => $categories,
            'filters'    => ['category' => $category],
            'pagination' => ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'last_page' => $lastPage],
        ]);
    }

    public function upload()
    {
        $file = $this->request->file('upload');
        if ($file === null) {
            Session::flash('error', 'Please choose a file to upload.');

            return $this->back('/files');
        }

        $category = trim((string)$this->request->input('category', ''));
        if (mb_strlen($category) > 120) {
            Session::flash('error', 'Category name is too long (max 120 characters).');
            $this->redirect('/files');
        }

        $original = (string)$file['name'];
        $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $mime = (string)(mime_content_type($file['tmp_name']) ?: $file['type']);

        if (!array_key_exists($extension, self::ALLOWED_MIME)) {
            Session::flash('error', 'File type .' . $extension . ' is not allowed.');
            $this->redirect('/files');
        }

        $maxBytes = 10 * 1024 * 1024; // 10 MB
        if ($file['size'] > $maxBytes) {
            Session::flash('error', 'File exceeds the 10 MB limit.');
            $this->redirect('/files');
        }

        $storedName = date('YmdHis') . '-' . bin2hex(random_bytes(10)) . '.' . $extension;
        $destination = STORAGE_PATH . '/uploads/' . $storedName;
        $path = 'uploads/' . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            Session::flash('error', 'Failed to store the uploaded file.');
            $this->redirect('/files');
        }

        Upload::insertGetId([
            'user_id'       => $this->userId(),
            'original_name' => $original,
            'stored_name'   => $storedName,
            'path'          => $path,
            'mime_type'     => $mime,
            'extension'     => $extension,
            'category'      => $category !== '' ? $category : null,
            'size'          => (int)$file['size'],
        ]);

        $this->logActivity('file_uploaded', 'File uploaded: ' . $original, 'files');
        $this->flashSuccess('File uploaded successfully.');

        $this->redirect('/files');
    }

    public function download(string $id)
    {
        $upload = Upload::find((int)$id);
        if ($upload === null) {
            throw new HttpException('File not found.', 404);
        }

        $absolute = STORAGE_PATH . '/' . ltrim($upload['path'], '/');
        if (!is_file($absolute)) {
            throw new HttpException('File is missing on disk.', 404);
        }

        Response::download($absolute, $upload['original_name']);
    }

    public function destroy(string $id)
    {
        $upload = Upload::find((int)$id);
        if ($upload === null) {
            throw new HttpException('File not found.', 404);
        }

        $isOwner = (int)($upload['user_id'] ?? 0) === $this->userId();
        if (!$isOwner && !User::isSuperAdmin($this->userId())) {
            throw new HttpException('You can only delete files that you uploaded.', 403);
        }

        $absolute = STORAGE_PATH . '/' . ltrim($upload['path'], '/');
        if (is_file($absolute)) {
            @unlink($absolute);
        }
        Upload::deleteById((int)$upload['id']);

        $this->logActivity('file_deleted', 'File deleted: ' . $upload['original_name'], 'files');
        $this->flashSuccess('File deleted.');

        $this->redirect('/files');
    }
}