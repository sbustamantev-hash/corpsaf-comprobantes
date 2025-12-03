<?php

namespace App\Enums;

/**
 * Roles del sistema
 * 
 * Define los roles disponibles y sus permisos
 */
class Role
{
    /**
     * Super Administrador (Super Root)
     * 
     * Permisos:
     * - Crear y gestionar Empresa (CRUD completo)
     * - Crear y gestionar usuarios y perfiles (CRUD completo)
     * - Ver todos los comprobantes de todas las Empresas
     * - Acceso total al sistema
     */
    public const ADMIN = 'admin';

    /**
     * Administrador de Empresa
     * 
     * Permisos:
     * - Ver comprobantes solo de su Empresa
     * - Aprobar/Rechazar comprobantes de su Empresa
     * - Agregar observaciones a comprobantes de su Empresa
     * - NO puede crear comprobantes
     * - NO puede editar comprobantes
     * - NO puede gestionar Empresas ni usuarios
     */
    public const AREA_ADMIN = 'area_admin';

    /**
     * Administrador de Marketing
     * 
     * Permisos:
     * - Ver todos los requerimientos de todas las empresas
     * - Responder a requerimientos
     * - Actualizar porcentaje de avance
     * - Cambiar estado de requerimientos
     * - NO puede acceder al Sistema de Gestión
     */
    public const MARKETING = 'marketing';

    /**
     * Operador/Trabajador
     * 
     * Permisos:
     * - Ver solo sus propios comprobantes
     * - Crear nuevos comprobantes
     * - Editar sus propios comprobantes (solo si están pendientes)
     * - Agregar observaciones a sus propios comprobantes
     * - Ver el estado de sus comprobantes
     */
    public const OPERADOR = 'operador';

    /**
     * Trabajador (sinónimo de operador, mantenido por compatibilidad)
     */
    public const TRABAJADOR = 'operador';

    /**
     * Obtener todos los roles disponibles
     * 
     * @return array
     */
    public static function all(): array
    {
        return [
            self::ADMIN,
            self::AREA_ADMIN,
            self::MARKETING,
            self::OPERADOR,
        ];
    }

    /**
     * Verificar si un rol es válido
     * 
     * @param string $role
     * @return bool
     */
    public static function isValid(string $role): bool
    {
        return in_array($role, self::all(), true);
    }

    /**
     * Obtener roles de operador/trabajador
     * 
     * @return array
     */
    public static function operadores(): array
    {
        return [self::OPERADOR, self::TRABAJADOR];
    }

    /**
     * Verificar si un rol es de operador/trabajador
     * 
     * @param string $role
     * @return bool
     */
    public static function isOperador(string $role): bool
    {
        return in_array($role, self::operadores(), true);
    }

    /**
     * Verificar si un rol es administrador de Empresa
     * 
     * @param string $role
     * @return bool
     */
    public static function isAreaAdmin(string $role): bool
    {
        return $role === self::AREA_ADMIN;
    }

    /**
     * Verificar si un rol es super administrador
     * 
     * @param string $role
     * @return bool
     */
    public static function isSuperAdmin(string $role): bool
    {
        return $role === self::ADMIN;
    }

    /**
     * Obtener etiqueta legible del rol
     * 
     * @param string $role
     * @return string
     */
    public static function label(string $role): string
    {
        return match ($role) {
            self::ADMIN => 'Super Administrador',
            self::AREA_ADMIN => 'Administrador de Empresa',
            self::MARKETING => 'Administrador de Marketing',
            self::OPERADOR => 'Usuario / Trabajador',
            self::TRABAJADOR => 'Usuario / Trabajador',
            default => 'Desconocido',
        };
    }
}

