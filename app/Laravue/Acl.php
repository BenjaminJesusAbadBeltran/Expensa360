<?php
/**
 * File Acl.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */
namespace App\Laravue;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class Acl
 *
 * @package App\Laravue
 */
final class Acl
{
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_DIRECTIVO = 'directivo';
    const ROLE_ADMIN = 'admin';
    const ROLE_SOCIO = 'socio';
    const ROLE_INQUILINO = 'inquilino';

    const PERMISSION_VIEW_MENU_ELEMENT_UI = 'view menu element ui';
    const PERMISSION_VIEW_MENU_PERMISSION = 'view menu permission';
    const PERMISSION_VIEW_MENU_COMPONENTS = 'view menu components';
    const PERMISSION_VIEW_MENU_CHARTS = 'view menu charts';
    const PERMISSION_VIEW_MENU_NESTED_ROUTES = 'view menu nested routes';
    const PERMISSION_VIEW_MENU_TABLE = 'view menu table';
    const PERMISSION_VIEW_MENU_ADMINISTRATOR = 'view menu administrator';
    const PERMISSION_VIEW_MENU_THEME = 'view menu theme';
    const PERMISSION_VIEW_MENU_CLIPBOARD = 'view menu clipboard';
    const PERMISSION_VIEW_MENU_EXCEL = 'view menu excel';
    const PERMISSION_VIEW_MENU_ZIP = 'view menu zip';
    const PERMISSION_VIEW_MENU_PDF = 'view menu pdf';
    const PERMISSION_VIEW_MENU_I18N = 'view menu i18n';

    const PERMISSION_USER_MANAGE = 'manage user';
    const PERMISSION_EXPENSE_MANAGE = 'manage expense';
    const PERMISSION_PROPERTY_MANAGE = 'manage property';
    const PERMISSION_PAID_MANAGE = 'manage paid';
    const PERMISSION_PAYMENT_MANAGE = 'manage payment';
    const PERMISSION_ARTICLE_MANAGE = 'manage article';
    const PERMISSION_OUTFLOW_MANAGE = 'manage outflow';
    const PERMISSION_PERMISSION_MANAGE = 'manage permission';
    const PERMISSION_WATER_SERVICE_MANAGE = 'manage water service';
    const PERMISSION_PAYMENT_METHOD_MANAGE = 'manage payment method';
    const PERMISSION_PETTY_CASH_MANAGE = 'manage petty cash';
    const PERMISSION_REPORT_MANAGE = 'manage report';
    /**
     * @param array $exclusives Exclude some permissions from the list
     * @return array
     */
    public static function permissions(array $exclusives = []): array
    {
        try {
            $class = new \ReflectionClass(__CLASS__);
            $constants = $class->getConstants();
            $permissions = Arr::where($constants, function($value, $key) use ($exclusives) {
                return !in_array($value, $exclusives) && Str::startsWith($key, 'PERMISSION_');
            });

            return array_values($permissions);
        } catch (\ReflectionException $exception) {
            return [];
        }
    }

    public static function menuPermissions(): array
    {
        try {
            $class = new \ReflectionClass(__CLASS__);
            $constants = $class->getConstants();
            $permissions = Arr::where($constants, function($value, $key) {
                return Str::startsWith($key, 'PERMISSION_VIEW_MENU_');
            });

            return array_values($permissions);
        } catch (\ReflectionException $exception) {
            return [];
        }
    }

    /**
     * @return array
     */
    public static function roles(): array
    {
        try {
            $class = new \ReflectionClass(__CLASS__);
            $constants = $class->getConstants();
            $roles =  Arr::where($constants, function($value, $key) {
                return Str::startsWith($key, 'ROLE_');
            });

            return array_values($roles);
        } catch (\ReflectionException $exception) {
            return [];
        }
    }
}
