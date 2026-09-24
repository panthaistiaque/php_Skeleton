<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Session;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditService;

final class RoleController extends Controller
{
    public function index(): string
    {
        $pdo = \App\Core\Database::pdo();
        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = max(1, (int)setting('general.records_per_page', 20));

        $total = (int)$pdo->query('SELECT COUNT(*) FROM `roles`')->fetchColumn();
        $lastPage = max(1, (int)ceil($total / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare(
            'SELECT r.*, COUNT(DISTINCT ur.user_id) AS user_count, COUNT(DISTINCT rp.permission_id) AS permission_count
             FROM `roles` r
             LEFT JOIN `user_role` ur ON ur.role_id = r.id
             LEFT JOIN `role_permission` rp ON rp.role_id = r.id
             GROUP BY r.id
             ORDER BY r.name ASC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $this->view('roles/index', [
            'pageTitle'  => 'Roles',
            'roles'      => $stmt->fetchAll(),
            'pagination' => ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'last_page' => $lastPage],
        ]);
    }

    public function create(): string
    {
        return $this->view('roles/form', [
            'pageTitle' => 'New Role',
            'role'      => null,
        ]);
    }

    public function store()
    {
        $data = $this->validate([
            'name'        => 'required|max:100|unique:roles,name',
            'slug'        => 'required|max:100|alpha_dash|unique:roles,slug',
            'description' => 'nullable|max:500',
            'status'      => 'in:active,inactive',
        ]);

        Role::insertGetId([
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'] !== '' ? $data['description'] : null,
            'status'      => $data['status'] ?? 'active',
        ]);

        $this->logActivity('role_created', 'Role created: ' . $data['name'], 'rbac');
        $this->flashSuccess('Role created.');

        $this->redirect('/roles');
    }

    public function edit(string $id): string
    {
        $role = Role::find((int)$id);
        if ($role === null) {
            throw new HttpException('Role not found.', 404);
        }

        return $this->view('roles/form', [
            'pageTitle' => 'Edit Role',
            'role'      => $role,
        ]);
    }

    public function update(string $id)
    {
        $role = Role::find((int)$id);
        if ($role === null) {
            throw new HttpException('Role not found.', 404);
        }

        if ((bool)$role['is_system']) {
            Session::flash('error', 'System roles can only be partially edited.');
            // allow name update but keep slug/status immutable
        }

        $data = $this->validate([
            'name'        => 'required|max:100|unique:roles,name,' . (int)$id,
            'slug'        => 'required|max:100|alpha_dash|unique:roles,slug,' . (int)$id,
            'description' => 'nullable|max:500',
            'status'      => 'in:active,inactive',
        ]);

        $payload = [
            'name'        => $data['name'],
            'description' => $data['description'] !== '' ? $data['description'] : null,
        ];
        if (!(bool)$role['is_system']) {
            $payload['slug'] = $data['slug'];
            $payload['status'] = $data['status'] ?? 'active';
        }

        Role::updateById((int)$role['id'], $payload);

        $this->logActivity('role_updated', 'Role updated: ' . $data['name'], 'rbac');
        $this->flashSuccess('Role updated.');

        $this->redirect('/roles');
    }

    public function destroy(string $id)
    {
        $role = Role::find((int)$id);
        if ($role === null) {
            throw new HttpException('Role not found.', 404);
        }

        if ((bool)$role['is_system']) {
            Session::flash('error', 'System roles cannot be deleted.');
            $this->redirect('/roles');
        }
        if (Role::isAssignedToUsers((int)$role['id'])) {
            Session::flash('error', 'Cannot delete a role that is assigned to users.');
            $this->redirect('/roles');
        }

        Role::deleteById((int)$role['id']);

        $this->logActivity('role_deleted', 'Role deleted: ' . $role['name'], 'rbac');
        $this->flashSuccess('Role deleted.');

        $this->redirect('/roles');
    }

    public function permissions(string $id): string
    {
        $role = Role::find((int)$id);
        if ($role === null) {
            throw new HttpException('Role not found.', 404);
        }

        return $this->view('roles/permissions', [
            'pageTitle'     => 'Role Permissions',
            'role'          => $role,
            'permissions'   => Permission::groupedByModule(),
            'assigned'      => Role::permissionIds((int)$role['id']),
        ]);
    }

    public function savePermissions(string $id)
    {
        $role = Role::find((int)$id);
        if ($role === null) {
            throw new HttpException('Role not found.', 404);
        }

        $permissionIds = (array)$this->request->input('permissions', []);
        $validIds = array_column(Permission::all(), 'id');
        $permissionIds = array_values(array_intersect(array_map('intval', $permissionIds), array_map('intval', $validIds)));

        Role::assignPermissions((int)$role['id'], $permissionIds);

        AuditService::instance()->log([
            'user_id'     => $this->userId(),
            'action'      => 'permissions_assigned',
            'module'      => 'rbac',
            'description' => 'Permissions updated for role ' . $role['name'],
            'ip_address'  => request_ip(),
        ]);

        $this->flashSuccess('Role permissions updated.');

        $this->redirect('/roles/' . (int)$role['id'] . '/permissions');
    }
}