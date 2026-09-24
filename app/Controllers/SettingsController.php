<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Session;
use App\Models\Menu;
use App\Services\MailService;
use App\Services\SettingsService;

final class SettingsController extends Controller
{
    public function index(): string
    {
        $tab = (string)$this->request->query('tab', 'general');

        return $this->view('settings/index', [
            'pageTitle' => 'System Settings',
            'tab'       => $tab,
            'app'       => SettingsService::instance()->allFlattened('app'),
            'org'       => SettingsService::instance()->allFlattened('organization'),
            'general'   => SettingsService::instance()->allFlattened('general'),
            'smtp'      => SettingsService::instance()->allFlattened('smtp'),
            'security'  => SettingsService::instance()->allFlattened('security'),
            'audit'     => SettingsService::instance()->allFlattened('audit'),
        ]);
    }

    public function updateApplication()
    {
        $data = $this->validate([
            'app.name'      => 'required|max:120',
            'app.logo_text' => 'nullable|max:60',
        ]);

        $service = SettingsService::instance();
        $service->set('app.name', $data['app.name'], ['group' => 'app']);
        $service->set('app.logo_text', $data['app.logo_text'] ?? '', ['group' => 'app']);

        $this->logActivity('settings_updated', 'Application settings updated', 'settings');
        $this->flashSuccess('Application settings saved.');

        $this->redirect('/settings?tab=application');
    }

    public function updateOrganization()
    {
        $data = $this->validate([
            'organization.name'    => 'required|max:150',
            'organization.address' => 'nullable|max:500',
            'organization.phone'   => 'nullable|max:30',
            'organization.email'   => 'nullable|email|max:190',
            'organization.website' => 'nullable|url|max:190',
        ]);

        $service = SettingsService::instance();
        foreach ($data as $key => $value) {
            $service->set($key, $value, ['group' => 'organization']);
        }

        $this->logActivity('settings_updated', 'Organization settings updated', 'settings');
        $this->flashSuccess('Organization settings saved.');

        $this->redirect('/settings?tab=organization');
    }

    public function updateGeneral()
    {
        $data = $this->validate([
            'general.timezone'       => 'required|max:40',
            'general.date_format'    => 'required|max:20',
            'general.datetime_format'=> 'required|max:30',
            'general.records_per_page' => 'required|integer|min:5|max:200',
        ]);

        $service = SettingsService::instance();
        $service->set('general.timezone', $data['general.timezone'], ['type' => 'string']);
        $service->set('general.date_format', $data['general.date_format'], ['type' => 'string']);
        $service->set('general.datetime_format', $data['general.datetime_format'], ['type' => 'string']);
        $service->set('general.records_per_page', (int)$data['general.records_per_page'], ['type' => 'int']);

        date_default_timezone_set($data['general.timezone']);

        $this->logActivity('settings_updated', 'General settings updated', 'settings');
        $this->flashSuccess('General settings saved.');

        $this->redirect('/settings?tab=general');
    }

    public function updateSmtp()
    {
        $data = $this->validate([
            'smtp.enabled'    => 'nullable|boolean',
            'smtp.host'       => 'required|max:190',
            'smtp.port'       => 'required|integer|min:1|max:65535',
            'smtp.username'   => 'nullable|max:190',
            'smtp.password'   => 'nullable|max:190',
            'smtp.encryption' => 'in:,tls,ssl',
            'smtp.from_email' => 'required|email|max:190',
            'smtp.from_name'  => 'required|max:190',
        ]);

        $service = SettingsService::instance();

        $service->set('smtp.enabled', (bool)($this->request->input('smtp.enabled') ?? false), ['type' => 'bool']);
        $service->set('smtp.host', $data['smtp.host']);
        $service->set('smtp.port', (int)$data['smtp.port'], ['type' => 'int']);
        $service->set('smtp.username', $data['smtp.username'] ?? '');
        $service->set('smtp.encryption', $data['smtp.encryption'] ?? 'tls');
        $service->set('smtp.from_email', $data['smtp.from_email']);
        $service->set('smtp.from_name', $data['smtp.from_name']);

        // Only overwrite the password when a new one was typed.
        if (!empty($data['smtp.password'])) {
            $service->set('smtp.password', $data['smtp.password'], ['encrypt' => true, 'group' => 'smtp']);
        }

        $this->logActivity('settings_updated', 'SMTP settings updated', 'settings');
        $this->flashSuccess('SMTP settings saved.');

        $this->redirect('/settings?tab=smtp');
    }

    public function updateSecurity()
    {
        $data = $this->validate([
            'security.min_password_length'  => 'required|integer|min:6|max:64',
            'security.max_failed_attempts'  => 'required|integer|min:3|max:20',
            'security.lockout_minutes'      => 'required|integer|min:1|max:1440',
            'security.session_timeout'      => 'required|integer|min:5|max:1440',
            'security.password_expiry_days' => 'required|integer|min:0|max:365',
        ]);

        $service = SettingsService::instance();
        $service->set('security.min_password_length', (int)$data['security.min_password_length'], ['type' => 'int']);
        $service->set('security.max_failed_attempts', (int)$data['security.max_failed_attempts'], ['type' => 'int']);
        $service->set('security.lockout_minutes', (int)$data['security.lockout_minutes'], ['type' => 'int']);
        $service->set('security.session_timeout', (int)$data['security.session_timeout'], ['type' => 'int']);
        $service->set('security.password_expiry_days', (int)$data['security.password_expiry_days'], ['type' => 'int']);
        $service->set('security.require_verification', (bool)($this->request->input('security.require_verification') ?? false), ['type' => 'bool']);
        $service->set('security.register_enabled', (bool)($this->request->input('security.register_enabled') ?? false), ['type' => 'bool']);
        $service->set('security.remember_me', (bool)($this->request->input('security.remember_me') ?? false), ['type' => 'bool']);

        $this->logActivity('settings_updated', 'Security settings updated', 'settings');
        $this->flashSuccess('Security settings saved.');

        $this->redirect('/settings?tab=security');
    }

    public function updateAudit()
    {
        $service = SettingsService::instance();
        $service->set('audit.log_activities', (bool)($this->request->input('audit.log_activities') ?? false), ['type' => 'bool']);
        $service->set('audit.track_page_views', (bool)($this->request->input('audit.track_page_views') ?? false), ['type' => 'bool']);
        $service->set('audit.log_security_events', (bool)($this->request->input('audit.log_security_events') ?? false), ['type' => 'bool']);

        $this->logActivity('settings_updated', 'Audit settings updated', 'settings');
        $this->flashSuccess('Audit settings saved.');

        $this->redirect('/settings?tab=audit');
    }

    public function testMail()
    {
        $user = $this->user();
        try {
            MailService::instance()->test($user['email']);
            $this->flashSuccess('Test email sent to ' . $user['email'] . ' (' . (MailService::instance()->smtpConfig()['enabled'] ? 'via SMTP' : 'logged to storage/mails') . ').');
        } catch (\Throwable $e) {
            Session::flash('error', 'Test email failed: ' . $e->getMessage());
        }

        $this->redirect('/settings?tab=smtp');
    }

    // -- Menu management ------------------------------------------------------

    public function menus(): string
    {
        $pdo = \App\Core\Database::pdo();
        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = max(1, (int)setting('general.records_per_page', 20));

        $total = (int)$pdo->query('SELECT COUNT(*) FROM `menus`')->fetchColumn();
        $lastPage = max(1, (int)ceil($total / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare(
            'SELECT m.*, p.title AS parent_title
             FROM `menus` m
             LEFT JOIN `menus` p ON p.id = m.parent_id
             ORDER BY COALESCE(m.sort_order,0), m.id ASC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $this->view('settings/menus/index', [
            'pageTitle'  => 'Menu Management',
            'menus'      => $stmt->fetchAll(),
            'pagination' => ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'last_page' => $lastPage],
        ]);
    }

    public function createMenu(): string
    {
        return $this->view('settings/menus/form', [
            'pageTitle' => 'New Menu Item',
            'menu'      => null,
            'parents'   => $this->menuParentOptions(),
        ]);
    }

    public function storeMenu()
    {
        $data = $this->validate([
            'title'      => 'required|max:120',
            'slug'       => 'required|max:120|alpha_dash|unique:menus,slug',
            'route'      => 'nullable|max:190',
            'icon'       => 'nullable|max:60',
            'permission' => 'nullable|max:120',
            'module'     => 'required|max:60',
            'sort_order' => 'nullable|integer',
            'parent_id'  => 'nullable|numeric|exists:menus,id',
            'status'     => 'in:active,inactive',
        ]);

        Menu::insertGetId([
            'parent_id'  => $data['parent_id'] !== '' ? (int)$data['parent_id'] : null,
            'title'      => $data['title'],
            'slug'       => $data['slug'],
            'route'      => $data['route'] !== '' ? $data['route'] : null,
            'icon'       => $data['icon'] !== '' ? $data['icon'] : null,
            'permission' => $data['permission'] !== '' ? $data['permission'] : null,
            'module'     => $data['module'],
            'sort_order' => $data['sort_order'] !== '' ? (int)$data['sort_order'] : 0,
            'status'     => $data['status'] ?? 'active',
        ]);

        $this->logActivity('menu_created', 'Menu item created: ' . $data['title'], 'settings');
        $this->flashSuccess('Menu item created.');

        $this->redirect('/settings/menus');
    }

    public function editMenu(string $id): string
    {
        $menu = Menu::find((int)$id);
        if ($menu === null) {
            throw new HttpException('Menu item not found.', 404);
        }

        return $this->view('settings/menus/form', [
            'pageTitle' => 'Edit Menu Item',
            'menu'      => $menu,
            'parents'   => $this->menuParentOptions((int)$menu['id']),
        ]);
    }

    public function updateMenu(string $id)
    {
        $menu = Menu::find((int)$id);
        if ($menu === null) {
            throw new HttpException('Menu item not found.', 404);
        }

        $data = $this->validate([
            'title'      => 'required|max:120',
            'slug'       => 'required|max:120|alpha_dash|unique:menus,slug,' . (int)$id,
            'route'      => 'nullable|max:190',
            'icon'       => 'nullable|max:60',
            'permission' => 'nullable|max:120',
            'module'     => 'required|max:60',
            'sort_order' => 'nullable|integer',
            'parent_id'  => 'nullable|numeric|exists:menus,id',
            'status'     => 'in:active,inactive',
        ]);

        $parentId = $data['parent_id'] !== '' ? (int)$data['parent_id'] : null;
        if ($parentId !== null && $parentId === (int)$menu['id']) {
            Session::flash('error', 'A menu item cannot be its own parent.');
            $this->redirect('/settings/menus/' . (int)$menu['id'] . '/edit');
        }

        Menu::updateById((int)$menu['id'], [
            'parent_id'  => $parentId,
            'title'      => $data['title'],
            'slug'       => $data['slug'],
            'route'      => $data['route'] !== '' ? $data['route'] : null,
            'icon'       => $data['icon'] !== '' ? $data['icon'] : null,
            'permission' => $data['permission'] !== '' ? $data['permission'] : null,
            'module'     => $data['module'],
            'sort_order' => $data['sort_order'] !== '' ? (int)$data['sort_order'] : 0,
            'status'     => $data['status'] ?? 'active',
        ]);

        $this->logActivity('menu_updated', 'Menu item updated: ' . $data['title'], 'settings');
        $this->flashSuccess('Menu item updated.');

        $this->redirect('/settings/menus');
    }

    public function destroyMenu(string $id)
    {
        $menu = Menu::find((int)$id);
        if ($menu === null) {
            throw new HttpException('Menu item not found.', 404);
        }

        Menu::deleteById((int)$menu['id']);

        $this->logActivity('menu_deleted', 'Menu item deleted: ' . $menu['title'], 'settings');
        $this->flashSuccess('Menu item deleted.');

        $this->redirect('/settings/menus');
    }

    private function menuParentOptions(int $excludeSelf = 0): array
    {
        $menus = Menu::all(['sort_order' => 'ASC', 'id' => 'ASC']);
        $options = [];
        foreach ($menus as $menu) {
            if ((int)$menu['id'] === $excludeSelf) {
                continue;
            }
            if ($menu['parent_id'] !== null) {
                continue; // only top-level items can be parents, keeps tree flat
            }
            $options[] = $menu;
        }

        return $options;
    }
}