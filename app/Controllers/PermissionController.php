<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Session;
use App\Models\Permission;

final class PermissionController extends Controller
{
    public function index(): string
    {
        $page = (int)($this->request->query('page', 1));
        $perPage = (int)setting('general.records_per_page', 20);

        $result = Permission::paginate([], ['module' => 'ASC', 'slug' => 'ASC'], $page, $perPage);

        return $this->view('permissions/index', [
            'pageTitle'   => 'Permissions',
            'permissions' => $result['items'],
            'pagination'  => $result,
        ]);
    }

    public function create(): string
    {
        return $this->view('permissions/form', [
            'pageTitle'  => 'New Permission',
            'permission' => null,
        ]);
    }

    public function store()
    {
        $data = $this->validate([
            'name'        => 'required|max:100',
            'slug'        => 'required|max:100|alpha_dash|unique:permissions,slug',
            'module'      => 'required|max:50',
            'description' => 'nullable|max:500',
        ]);

        Permission::insertGetId([
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'module'      => $data['module'],
            'description' => $data['description'] !== '' ? $data['description'] : null,
        ]);

        $this->logActivity('permission_created', 'Permission created: ' . $data['slug'], 'rbac');
        $this->flashSuccess('Permission created.');

        $this->redirect('/permissions');
    }

    public function edit(string $id): string
    {
        $permission = Permission::find((int)$id);
        if ($permission === null) {
            throw new HttpException('Permission not found.', 404);
        }

        return $this->view('permissions/form', [
            'pageTitle'  => 'Edit Permission',
            'permission' => $permission,
        ]);
    }

    public function update(string $id)
    {
        $permission = Permission::find((int)$id);
        if ($permission === null) {
            throw new HttpException('Permission not found.', 404);
        }

        $data = $this->validate([
            'name'        => 'required|max:100',
            'slug'        => 'required|max:100|alpha_dash|unique:permissions,slug,' . (int)$id,
            'module'      => 'required|max:50',
            'description' => 'nullable|max:500',
        ]);

        Permission::updateById((int)$permission['id'], [
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'module'      => $data['module'],
            'description' => $data['description'] !== '' ? $data['description'] : null,
        ]);

        $this->logActivity('permission_updated', 'Permission updated: ' . $data['slug'], 'rbac');
        $this->flashSuccess('Permission updated.');

        $this->redirect('/permissions');
    }

    public function destroy(string $id)
    {
        $permission = Permission::find((int)$id);
        if ($permission === null) {
            throw new HttpException('Permission not found.', 404);
        }

        Session::flash('error', 'Permissions are referenced by roles; remove them from roles first. Direct deletion is disabled for data integrity.');

        $this->redirect('/permissions');
    }
}