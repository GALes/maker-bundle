<?php

namespace GALes\MakerBundle\Maker\Roles;

/**
 * Roles que gales:make:crud ofrece con autocompletado al elegir quién accede al CRUD.
 *
 * Junta, en este orden y sin repetir: los roles que devuelve el origen configurado en gales_maker.crud.roles_source
 * (por ejemplo la lista de roles de la entidad de usuarios) y los de security.role_hierarchy (claves y valores).
 * No ofrece los roles fijos (gales_maker.crud.default_roles), que siempre se incluyen, ni los roles especiales de Symfony.
 */
final class AvailableRolesProvider
{
    /** Roles que no se sugieren: ROLE_USER lo tiene cualquier usuario autenticado; los otros son de impersonación. */
    private const NOT_SUGGESTED = ['ROLE_USER', 'ROLE_PREVIOUS_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'];

    private ?string $sourceError = null;

    /**
     * @param array<string, mixed> $roleHierarchy El parámetro security.role_hierarchy.roles
     * @param string|null          $rolesSource   "Clase::metodoEstatico" o "Clase::CONSTANTE"
     * @param list<string>         $defaultRoles  Roles que siempre tienen acceso
     */
    public function __construct(
        private readonly array $roleHierarchy,
        private readonly ?string $rolesSource = null,
        private readonly array $defaultRoles = ['ROLE_ADMIN'],
    ) {
    }

    /**
     * @return list<string>
     */
    public function getDefaultRoles(): array
    {
        return RoleNormalizer::normalize($this->defaultRoles);
    }

    /**
     * @return list<string>
     */
    public function getSuggestedRoles(): array
    {
        $candidates = $this->readSource();
        foreach ($this->roleHierarchy as $role => $children) {
            $candidates[] = $role;
            foreach ((array) $children as $child) {
                $candidates[] = $child;
            }
        }

        $excluded = array_merge($this->getDefaultRoles(), self::NOT_SUGGESTED);
        $roles = [];
        foreach ($candidates as $candidate) {
            if (!is_string($candidate) || 1 !== preg_match(RoleNormalizer::PATTERN, $candidate)) {
                continue;
            }
            if (!in_array($candidate, $excluded, true) && !in_array($candidate, $roles, true)) {
                $roles[] = $candidate;
            }
        }

        return $roles;
    }

    /**
     * Motivo por el que no se pudo leer gales_maker.crud.roles_source en la última llamada a getSuggestedRoles(), o null.
     */
    public function getSourceError(): ?string
    {
        return $this->sourceError;
    }

    /**
     * @return list<mixed>
     */
    private function readSource(): array
    {
        $this->sourceError = null;
        if (null === $this->rolesSource || '' === $this->rolesSource) {
            return [];
        }

        [$class, $member] = explode('::', $this->rolesSource, 2) + [1 => ''];

        try {
            if (!class_exists($class)) {
                return $this->fail(sprintf('la clase %s no existe', $class));
            }
            if (method_exists($class, $member)) {
                $method = new \ReflectionMethod($class, $member);
                if (!$method->isStatic() || !$method->isPublic()) {
                    return $this->fail(sprintf('%s debe ser un método público y estático', $this->rolesSource));
                }
                $value = $method->invoke(null);
            } elseif (defined($class.'::'.$member)) {
                $value = constant($class.'::'.$member);
            } else {
                return $this->fail(sprintf('%s no es un método ni una constante de %s', $member, $class));
            }
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }

        if (!is_array($value)) {
            return $this->fail(sprintf('%s debe devolver un array', $this->rolesSource));
        }

        $roles = [];
        array_walk_recursive($value, static function ($role) use (&$roles): void {
            $roles[] = $role;
        });

        return $roles;
    }

    /**
     * @return list<mixed>
     */
    private function fail(string $reason): array
    {
        $this->sourceError = sprintf('No se pudieron leer los roles de gales_maker.crud.roles_source (%s): %s.', $this->rolesSource, $reason);

        return [];
    }
}
