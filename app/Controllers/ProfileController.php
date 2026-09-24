<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Session;
use App\Models\ApiToken;
use App\Services\AuthService;

final class ProfileController extends Controller
{
    public function show(): string
    {
        $user = $this->user();
        if ($user === null) {
            throw new HttpException('User not found.', 404);
        }

        return $this->view('profile/show', [
            'pageTitle'   => 'My Profile',
            'user'        => $user,
            'userRoles'   => \App\Models\User::rolesFor((int)$user['id']),
            'apiTokens'   => $this->apiTokens((int)$user['id']),
        ]);
    }

    public function update()
    {
        $user = $this->user();

        $data = $this->validate([
            'name' => 'required|max:150',
        ]);

        $payload = ['name' => $data['name']];

        if ($this->request->hasFile('avatar')) {
            $file = $this->request->file('avatar');
            $allowed = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                Session::flash('error', 'Avatar must be a JPEG, PNG, GIF or WEBP image.');

                return $this->back('/profile');
            }
            $stored = 'avatar-' . $user['id'] . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], STORAGE_PATH . '/uploads/' . $stored)) {
                if ($user['avatar'] && is_file(STORAGE_PATH . '/uploads/' . $user['avatar'])) {
                    @unlink(STORAGE_PATH . '/uploads/' . $user['avatar']);
                }
                $payload['avatar'] = $stored;
            }
        }

        \App\Models\User::updateById((int)$user['id'], $payload);

        $this->logActivity('profile_updated', 'Profile details updated', 'profile');
        $this->flashSuccess('Profile updated.');

        $this->redirect('/profile');
    }

    public function changePassword()
    {
        $data = $this->validate([
            'current_password'          => 'required',
            'password'                  => 'required|min:' . (int)setting('security.min_password_length', 8)
                                       . '|confirmed',
            'password_confirmation'     => 'required',
        ]);

        $result = AuthService::instance()->changePassword(
            $this->userId(),
            $data['current_password'],
            $data['password'],
            $data['password_confirmation']
        );

        Session::flash($result['success'] ? 'success' : 'error', $result['message']);

        $this->redirect('/profile');
    }

    private function apiTokens(int $userId): array
    {
        return ApiToken::where(['user_id' => $userId], ['id' => 'DESC']);
    }
}