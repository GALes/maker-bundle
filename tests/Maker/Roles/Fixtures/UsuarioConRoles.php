<?php

namespace GALes\MakerBundle\Tests\Maker\Roles\Fixtures;

/**
 * Imita la entidad de usuarios de un proyecto: roles para el formulario (etiqueta => rol).
 */
final class UsuarioConRoles
{
    public const ROLES_FOR_CHOICES = [
        'Administrador' => 'ROLE_ADMIN',
        'Reportes'      => 'ROLE_REPORTE',
    ];

    public static function getRolesDetalle(): array
    {
        return self::ROLES_FOR_CHOICES + ['Consultas' => 'ROLE_CONSULTA'];
    }

    public static function getTexto(): string
    {
        return 'ROLE_ADMIN';
    }

    public function getRolesDeInstancia(): array
    {
        return ['ROLE_X'];
    }
}
