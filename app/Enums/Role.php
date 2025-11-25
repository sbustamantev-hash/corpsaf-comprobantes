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
     * - Crear y gestionar áreas/empresas (CRUD completo)
     * - Crear y gestionar usuarios y perfiles (CRUD completo)
     * - Ver todos los comprobantes de todas las áreas
     * - Acceso total al sistema
     */
    public const ADMIN = 'admin';

    /**
     * Administrador de Área/Empresa
     * 
     * Permisos:
     * - Ver comprobantes solo de su área
     * - Aprobar/Rechazar comprobantes de su área
     * - Agregar observaciones a comprobantes de su área
     * - NO puede crear comprobantes
     * - NO puede editar comprobantes
     * - NO puede gestionar áreas ni usuarios
     */
    public const AREA_ADMIN = 'area_admin';

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
     * Verificar si un rol es administrador de área
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
        return match($role) {
            self::ADMIN => 'Super Administrador',
            self::AREA_ADMIN => 'Administrador de Área',
            self::OPERADOR => 'Operador',
            self::TRABAJADOR => 'Operador',
            default => 'Desconocido',
        };
    }
}

