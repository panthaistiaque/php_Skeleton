<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Session;
use App\Models\Department;
use App\Models\Designation;

final class DesignationsController extends Controller
{
    public function index(): string
    {
        $pdo = \App\Core\Database::pdo();
        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = max(1, (int)setting('general.records_per_page', 20));

        $total = (int)$pdo->query('SELECT COUNT(*) FROM `designations`')->fetchColumn();
        $lastPage = max(1, (int)ceil($total / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare(
            'SELECT d.*, dpt.name AS department_name
             FROM `designations` d
             LEFT JOIN `departments` dpt ON dpt.id = d.department_id
             ORDER BY d.name ASC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $this->view('designations/index', [
            'pageTitle'    => 'Designations',
            'designations' => $stmt->fetchAll(),
            'pagination'   => ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'last_page' => $lastPage],
        ]);
    }

    public function create(): string
    {
        return $this->view('designations/form', [
            'pageTitle'  => 'New Designation',
            'designation'=> null,
            'departments'=> Department::active(),
        ]);
    }

    public function store()
    {
        $data = $this->validate([
            'name'          => 'required|max:150|unique:designations,name',
            'department_id' => 'nullable|numeric|exists:departments,id',
            'description'   => 'nullable|max:500',
            'status'        => 'in:active,inactive',
        ]);

        Designation::insertGetId([
            'name'          => $data['name'],
            'department_id' => $data['department_id'] !== '' ? (int)$data['department_id'] : null,
            'description'   => $data['description'] !== '' ? $data['description'] : null,
            'status'        => $data['status'] ?? 'active',
        ]);

        $this->logActivity('designation_created', 'Designation created: ' . $data['name'], 'organization');
        $this->flashSuccess('Designation created.');

        $this->redirect('/designations');
    }

    public function edit(string $id): string
    {
        $designation = Designation::find((int)$id);
        if ($designation === null) {
            throw new HttpException('Designation not found.', 404);
        }

        return $this->view('designations/form', [
            'pageTitle'  => 'Edit Designation',
            'designation'=> $designation,
            'departments'=> Department::active(),
        ]);
    }

    public function update(string $id)
    {
        $designation = Designation::find((int)$id);
        if ($designation === null) {
            throw new HttpException('Designation not found.', 404);
        }

        $data = $this->validate([
            'name'          => 'required|max:150|unique:designations,name,' . (int)$id,
            'department_id' => 'nullable|numeric|exists:departments,id',
            'description'   => 'nullable|max:500',
            'status'        => 'in:active,inactive',
        ]);

        Designation::updateById((int)$designation['id'], [
            'name'          => $data['name'],
            'department_id' => $data['department_id'] !== '' ? (int)$data['department_id'] : null,
            'description'   => $data['description'] !== '' ? $data['description'] : null,
            'status'        => $data['status'] ?? 'active',
        ]);

        $this->logActivity('designation_updated', 'Designation updated: ' . $data['name'], 'organization');
        $this->flashSuccess('Designation updated.');

        $this->redirect('/designations');
    }

    public function destroy(string $id)
    {
        $designation = Designation::find((int)$id);
        if ($designation === null) {
            throw new HttpException('Designation not found.', 404);
        }

        if (Designation::hasUsers((int)$designation['id'])) {
            Session::flash('error', 'Cannot delete a designation that has users assigned.');
            $this->redirect('/designations');
        }

        Designation::deleteById((int)$designation['id']);

        $this->logActivity('designation_deleted', 'Designation deleted: ' . $designation['name'], 'organization');
        $this->flashSuccess('Designation deleted.');

        $this->redirect('/designations');
    }
}