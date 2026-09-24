<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Session;
use App\Models\Department;

final class DepartmentsController extends Controller
{
    public function index(): string
    {
        $page = (int)($this->request->query('page', 1));
        $perPage = (int)setting('general.records_per_page', 20);

        $result = Department::paginate([], ['name' => 'ASC'], $page, $perPage);

        return $this->view('departments/index', [
            'pageTitle'   => 'Departments',
            'departments' => $result['items'],
            'pagination'  => $result,
        ]);
    }

    public function create(): string
    {
        return $this->view('departments/form', [
            'pageTitle' => 'New Department',
            'department'=> null,
        ]);
    }

    public function store()
    {
        $data = $this->validate([
            'name'        => 'required|max:150|unique:departments,name',
            'code'        => 'nullable|max:30',
            'description' => 'nullable|max:500',
            'status'      => 'in:active,inactive',
        ]);

        Department::insertGetId([
            'name'        => $data['name'],
            'code'        => $data['code'] !== '' ? $data['code'] : null,
            'description' => $data['description'] !== '' ? $data['description'] : null,
            'status'      => $data['status'] ?? 'active',
        ]);

        $this->logActivity('department_created', 'Department created: ' . $data['name'], 'organization');
        $this->flashSuccess('Department created.');

        $this->redirect('/departments');
    }

    public function edit(string $id): string
    {
        $department = Department::find((int)$id);
        if ($department === null) {
            throw new HttpException('Department not found.', 404);
        }

        return $this->view('departments/form', [
            'pageTitle'  => 'Edit Department',
            'department' => $department,
        ]);
    }

    public function update(string $id)
    {
        $department = Department::find((int)$id);
        if ($department === null) {
            throw new HttpException('Department not found.', 404);
        }

        $data = $this->validate([
            'name'        => 'required|max:150|unique:departments,name,' . (int)$id,
            'code'        => 'nullable|max:30',
            'description' => 'nullable|max:500',
            'status'      => 'in:active,inactive',
        ]);

        Department::updateById((int)$department['id'], [
            'name'        => $data['name'],
            'code'        => $data['code'] !== '' ? $data['code'] : null,
            'description' => $data['description'] !== '' ? $data['description'] : null,
            'status'      => $data['status'] ?? 'active',
        ]);

        $this->logActivity('department_updated', 'Department updated: ' . $data['name'], 'organization');
        $this->flashSuccess('Department updated.');

        $this->redirect('/departments');
    }

    public function destroy(string $id)
    {
        $department = Department::find((int)$id);
        if ($department === null) {
            throw new HttpException('Department not found.', 404);
        }

        if (Department::hasUsers((int)$department['id'])) {
            Session::flash('error', 'Cannot delete a department that has users assigned.');
            $this->redirect('/departments');
        }

        Department::deleteById((int)$department['id']);

        $this->logActivity('department_deleted', 'Department deleted: ' . $department['name'], 'organization');
        $this->flashSuccess('Department deleted.');

        $this->redirect('/departments');
    }
}