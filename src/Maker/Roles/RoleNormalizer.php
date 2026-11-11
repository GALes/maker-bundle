<?php

namespace GALes\MakerBundle\Maker\Roles;

/**
 * Normaliza y valida los roles que se eligen al generar un CRUD.
 *
 * Un rol válido empieza con ROLE_ y sólo usa mayúsculas, números y guion bajo: es lo que vota el RoleVoter
 * de Symfony y lo único que se escribe en el código generado.
 */
final class RoleNormalizer
{
    public const PATTERN = '/^ROLE_[A-Z0-9_]+$/';

    /**
     * Convierte una lista de valores (cada uno puede traer varios roles separados por coma) en roles únicos,
     * en mayúsculas y en el orden en que llegaron.
     *
     * @param iterable<mixed>|string|null $values
     *
     * @return list<string>
     *
     * @throws \InvalidArgumentException si algún valor no es un rol válido
     */
    public static function normalize(iterable|string|null $values): array
    {
        if (null === $values) {
            return [];
        }
        if (is_string($values)) {
            $values = [$values];
        }

        $roles = [];
        foreach ($values as $value) {
            foreach (explode(',', (string) $value) as $role) {
                $role = strtoupper(trim($role));
                if ('' === $role) {
                    continue;
                }
                if (1 !== preg_match(self::PATTERN, $role)) {
                    throw new \InvalidArgumentException(sprintf('"%s" no es un rol válido: debe empezar con ROLE_ y usar sólo letras, números y guion bajo (por ejemplo ROLE_REPORTE).', trim($role)));
                }
                if (!in_array($role, $roles, true)) {
                    $roles[] = $role;
                }
            }
        }

        return $roles;
    }

    /**
     * Une los roles fijos (primero) con los elegidos, sin repetir.
     *
     * @param list<string> $defaultRoles
     * @param list<string> $chosenRoles
     *
     * @return list<string>
     */
    public static function merge(array $defaultRoles, array $chosenRoles): array
    {
        return array_values(array_unique(array_merge($defaultRoles, $chosenRoles)));
    }
}
