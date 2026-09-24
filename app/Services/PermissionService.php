<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\User;

/**
 * Single place that answers "can this user do X" and builds the sidebar.
 */
final class PermissionService
{
    private static ?self $instance = null;

    private ?array $permissionSlugs = null;
    private ?bool $superAdmin = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        $this->prime();
    }

    private function prime(): void
    {
        // Eager-load once per request with the current session user.
        $userId = Session::getAuth();
        if ($userId !== null) {
            $this->superAdmin = User::isSuperAdmin($userId);
            $this->permissionSlugs = $this->superAdmin ? ['*'] : Permission::slugsForUser($userId);
        } else {
            $this->superAdmin = false;
            $this->permissionSlugs = [];
        }
    }

    public function has(string $slug): bool
    {
        return $this->superAdmin === true || in_array($slug, $this->permissionSlugs ?? [], true);
    }

    public function hasAny(array $slugs): bool
    {
        foreach ($slugs as $slug) {
            if ($this->has($slug)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->superAdmin === true;
    }

    public function permissionSlugs(): array
    {
        return $this->permissionSlugs ?? [];
    }

    /**
     * @return array<int, array{item:array,children:array}>
     */
    public function sidebarMenus(): array
    {
        $menus = Menu::activeMenus();

        $byParent = [];
        $hadChildren = [];
        foreach ($menus as $menu) {
            $parentId = (int)$menu['parent_id'];
            if ($parentId !== 0) {
                $hadChildren[$parentId] = true;
            }
            if ($this->isMenuVisible($menu)) {
                $byParent[$parentId][$menu['id']] = $menu;
            }
        }

        $tree = [];
        foreach ($byParent[0] ?? [] as $menuId => $menu) {
            $children = array_values($byParent[$menuId] ?? []);

            // A group without permissions of its own only makes sense when at
            // least one child is accessible; otherwise hide the empty group.
            $permission = $menu['permission'] ?? null;
            if (($permission === null || $permission === '')
                && isset($hadChildren[$menuId])
                && $children === []) {
                continue;
            }

            $tree[] = [
                'item'     => $menu,
                'children' => $children,
            ];
        }

        return $tree;
    }

    private function isMenuVisible(array $menu): bool
    {
        $permission = $menu['permission'] ?? null;

        return $permission === null
            || $permission === ''
            || $this->has((string)$permission);
    }
}