<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Menu extends Model
{
    protected string $table = 'menus';
    protected array $fillable = ['parent_id', 'title', 'slug', 'route', 'icon', 'permission', 'module', 'sort_order', 'status'];

    /**
     * All active menus ordered for tree building.
     */
    public static function activeMenus(): array
    {
        return self::where(['status' => 'active'], ['sort_order' => 'ASC', 'id' => 'ASC']);
    }
}