<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class <?= $class_name ?> extends Voter
{
    // Permisos funcionalidades
    // Para una acción masiva propia: agregar la constante, incluirla en supports() y en voteOnAttribute(), y registrarla en BULK_ACTIONS del controlador.
    const VER       = '<?= $permission_prefix ?>_ver';
    const EDITAR    = '<?= $permission_prefix ?>_editar';
    const ELIMINAR  = '<?= $permission_prefix ?>_eliminar';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [
            self::VER,
            self::EDITAR,
            self::ELIMINAR,
        ])) {
            return false;
        }

        return $subject instanceof <?= $entity_class_name ?>;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        switch ($attribute) {
            case self::VER:
            case self::EDITAR:
            case self::ELIMINAR:
                // Regla por defecto: sólo ROLE_ADMIN. Ajustar según el proyecto.
                return $this->security->isGranted('ROLE_ADMIN');
        }

        throw new \LogicException('This code should not be reached!');
    }
}
