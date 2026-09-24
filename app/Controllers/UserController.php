<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Session;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Role;
use App\Models\User;
use App\Repositories\AuditRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\MailService;

final class UserController extends Controller
{
    public function index(): string
    {
        $page = (int)($this->request->query('page', 1));
        $perPage = (int)setting('general.records_per_page', 20);

        $filters = [
            'search'        => (string)$this->request->query('search', ''),
            'status'        => (string)$this->request->query('status', ''),
            'role_id'       => (string)$this->request->query('role_id', ''),
            'department_id' => (string)$this->request->query('department_id', ''),
        ];

        $result = UserRepository::paginate($filters, $page, $perPage);

        return $this->view('users/index', [
            'pageTitle'   => 'User Management',
            'users'       => $result['items'],
            'pagination'  => $result,
            'filters'     => $filters,
            'roles'       => Role::all(['name' => 'ASC']),
            'departments' => Department::active(),
        ]);
    }

    public function create(): string
    {
        return $this->view('users/create', [
            'pageTitle'   => 'Create User',
            'departments' => Department::active(),
            'designations'=> Designation::active(),
            'roles'       => Role::all(['name' => 'ASC']),
        ]);
    }

    public function store()
    {
        $data = $this->validate([
            'name'         => 'required|max:150',
            'email'        => 'required|email|max:190|unique:users,email',
            'password'     => 'required|min:' . (int)setting('security.min_password_length', 8),
            'status'       => 'in:active,inactive,pending',
            'department_id'=> 'nullable|numeric|exists:departments,id',
            'designation_id'=> 'nullable|numeric|exists:designations,id',
        ]);

        $userId = (int)User::insertGetId([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'password'     => AuthService::instance()->hash($data['password']),
            'status'       => $data['status'] ?? 'active',
            'email_verified_at' => ($data['status'] ?? 'active') === 'active' ? date('Y-m-d H:i:s') : null,
            'password_changed_at'=> date('Y-m-d H:i:s'),
            'department_id'=> $data['department_id'] !== '' ? (int)$data['department_id'] : null,
            'designation_id'=> $data['designation_id'] !== '' ? (int)$data['designation_id'] : null,
            'created_by'   => $this->userId(),
        ]);

        $this->syncRoles($userId, (array)$this->request->input('roles', []));

        if ($data['email']) {
            MailService::instance()->sendWelcome($data['email'], $data['name'], $data['password']);
        }

        $this->logActivity('user_created', 'User created: ' . $data['email'], 'users');
        $this->flashSuccess('User created. A welcome email with the temporary password was sent.');

        $this->redirect('/users');
    }

    public function show(string $id): string
    {
        $user = User::find((int)$id);
        if ($user === null) {
            throw new HttpException('User not found.', 404);
        }

        return $this->view('users/show', [
            'pageTitle'     => 'View User',
            'user'          => $user,
            'userRoles'     => User::rolesFor((int)$user['id']),
            'allRoles'      => Role::all(['name' => 'ASC']),
            'departments'   => Department::active(),
            'designations'  => Designation::active(),
            'activities'    => AuditRepository::userWorkTimeline((int)$user['id'], 25),
            'logins'        => AuditRepository::loginHistory(['user_id' => (string)$user['id']], 1, 10)['items'],
        ]);
    }

    public function edit(string $id): string
    {
        $user = User::find((int)$id);
        if ($user === null) {
            throw new HttpException('User not found.', 404);
        }

        $userRoleIds = array_column(User::rolesFor((int)$user['id']), 'id');

        return $this->view('users/edit', [
            'pageTitle'     => 'Edit User',
            'user'          => $user,
            'userRoleIds'   => $userRoleIds,
            'departments'   => Department::active(),
            'designations'  => Designation::active(),
            'roles'         => Role::all(['name' => 'ASC']),
        ]);
    }

    public function update(string $id)
    {
        $user = User::find((int)$id);
        if ($user === null) {
            throw new HttpException('User not found.', 404);
        }

        $data = $this->validate([
            'name'          => 'required|max:150',
            'email'         => 'required|email|max:190|unique:users,email,' . (int)$id,
            'status'        => 'in:active,inactive,pending',
            'password'      => 'nullable|min:' . (int)setting('security.min_password_length', 8),
            'department_id' => 'nullable|numeric|exists:departments,id',
            'designation_id'=> 'nullable|numeric|exists:designations,id',
        ]);

        // Prevent deactivating yourself.
        if ($data['status'] !== 'active' && (int)$id === $this->userId()) {
            Session::flash('error', 'You cannot deactivate your own account.');

            return $this->back('/users/' . (int)$id . '/edit');
        }

        $payload = [
            'name'          => $data['name'],
            'email'         => $data['email'],
            'status'        => $data['status'] ?? 'active',
            'department_id' => $data['department_id'] !== '' ? (int)$data['department_id'] : null,
            'designation_id'=> $data['designation_id'] !== '' ? (int)$data['designation_id'] : null,
        ];

        if (!empty($data['password'])) {
            $payload['password'] = AuthService::instance()->hash($data['password']);
            $payload['password_changed_at'] = date('Y-m-d H:i:s');
        }

        User::updateById((int)$user['id'], $payload);
        $this->syncRoles((int)$user['id'], (array)$this->request->input('roles', []));

        $this->logActivity('user_updated', 'User updated: ' . $data['email'], 'users');
        $this->flashSuccess('User updated.');

        $this->redirect('/users/' . (int)$user['id']);
    }

    public function destroy(string $id)
    {
        $user = User::find((int)$id);
        if ($user === null) {
            throw new HttpException('User not found.', 404);
        }

        if ((int)$id === $this->userId()) {
            Session::flash('error', 'You cannot delete your own account.');

            return $this->back('/users');
        }

        if (User::isSuperAdmin((int)$id)) {
            Session::flash('error', 'Super Administrator accounts cannot be deleted.');

            return $this->back('/users');
        }

        User::deleteById((int)$id);

        $this->logActivity('user_deleted', 'User deleted: ' . $user['email'], 'users');
        $this->flashSuccess('User deleted.');

        $this->redirect('/users');
    }

    public function toggleStatus(string $id)
    {
        $user = User::find((int)$id);
        if ($user === null) {
            throw new HttpException('User not found.', 404);
        }

        if ((int)$id === $this->userId()) {
            Session::flash('error', 'You cannot change your own account status.');

            return $this->back('/users');
        }

        if (User::isSuperAdmin((int)$id)) {
            Session::flash('error', 'Super Administrator accounts cannot be deactivated from the UI.');

            return $this->back('/users');
        }

        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        User::updateById((int)$user['id'], ['status' => $newStatus]);

        $this->logActivity('user_status_changed', "User {$user['email']} set to {$newStatus}", 'users');
        $this->flashSuccess("Account {$newStatus}.");

        $this->redirect('/users');
    }

    public function assignRoles(string $id)
    {
        $user = User::find((int)$id);
        if ($user === null) {
            throw new HttpException('User not found.', 404);
        }

        $this->syncRoles((int)$user['id'], (array)$this->request->input('roles', []));

        $this->logActivity('roles_assigned', 'Roles updated for ' . $user['email'], 'users');
        $this->flashSuccess('Roles updated.');

        $this->redirect('/users/' . (int)$user['id']);
    }

    private function syncRoles(int $userId, array $roleIds): void
    {
        $pdo = \App\Core\Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('DELETE FROM `user_role` WHERE `user_id` = :uid');
            $stmt->execute([':uid' => $userId]);

            $insert = $pdo->prepare('INSERT INTO `user_role` (`user_id`,`role_id`) VALUES (:uid,:rid)');
            foreach (array_unique(array_filter(array_map('intval', $roleIds))) as $roleId) {
                $insert->execute([':uid' => $userId, ':rid' => $roleId]);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}