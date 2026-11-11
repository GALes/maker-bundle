<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class <?= $class_name ?> extends Voter
{
    // Roles con acceso a este CRUD (elegidos al generarlo con gales:make:crud): alcanza con tener uno.
    public const ROLES = [
<?php foreach ($roles as $role): ?>
        '<?= $role ?>',
<?php endforeach ?>
    ];

    // Permisos del módulo (sin entidad): ACCEDER protege todo el controlador (listado y exportación incluidos).
    const ACCEDER   = '<?= $permission_prefix ?>_acceder';
    const CREAR     = '<?= $permission_prefix ?>_crear';

    // Permisos sobre una entidad.
    // Para una acción masiva propia: agregar la constante, incluirla en supports() y en voteOnAttribute(), y registrarla en BULK_ACTIONS del controlador.
    const VER       = '<?= $permission_prefix ?>_ver';
    const EDITAR    = '<?= $permission_prefix ?>_editar';
    const ELIMINAR  = '<?= $permission_prefix ?>_eliminar';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, [self::ACCEDER, self::CREAR], true)) {
            return null === $subject;
        }

        if (!in_array($attribute, [
            self::VER,
            self::EDITAR,
            self::ELIMINAR,
        ], true)) {
            return false;
        }

        return $subject instanceof <?= $entity_class_name ?>;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        switch ($attribute) {
            case self::ACCEDER:
            case self::CREAR:
            case self::VER:
            case self::EDITAR:
            case self::ELIMINAR:
                // Regla por defecto: cualquiera de los roles de self::ROLES. Ajustar por atributo según el proyecto.
                return $this->tieneAlgunRol();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function tieneAlgunRol(): bool
    {
        foreach (self::ROLES as $rol) {
            if ($this->security->isGranted($rol)) {
                return true;
            }
        }

        return false;
    }
}
