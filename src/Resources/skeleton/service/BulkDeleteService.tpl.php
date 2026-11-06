<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Eliminación masiva de registros.
 *
 * Verifica el permiso sobre todo el lote antes de borrar la primera entidad:
 * si se niega alguna, lanza AccessDeniedException (403) sin borrar nada.
 * Los ids inexistentes se ignoran. El borrado se hace con un único flush
 * dentro de una transacción.
 */
class <?= $class_name ?>
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
    ) {
    }

    /**
     * @param class-string               $entityClass
     * @param array<int|string>          $ids
     * @param string|null                $permiso Atributo del Voter evaluado sobre cada entidad;
     *                                            sin él, sólo ROLE_ADMIN puede borrar en lote.
     *
     * @return int Cantidad de registros eliminados
     *
     * @throws AccessDeniedException si se niega el permiso sobre alguna entidad del lote
     */
    public function eliminar(string $entityClass, array $ids, ?string $permiso = null): int
    {
        $ids = array_values(array_unique(array_filter(
            $ids,
            static fn ($id) => (is_int($id) || is_string($id)) && '' !== $id
        )));

        if ([] === $ids) {
            return 0;
        }

        // findBy con un array de ids ignora los inexistentes
        $entidades = $this->em->getRepository($entityClass)->findBy(['id' => $ids]);

        if (null === $permiso) {
            if (!$this->security->isGranted('ROLE_ADMIN')) {
                throw new AccessDeniedException('Sólo un administrador puede eliminar en lote.');
            }
        } else {
            foreach ($entidades as $entidad) {
                if (!$this->security->isGranted($permiso, $entidad)) {
                    throw new AccessDeniedException(sprintf('Acceso denegado: "%s" sobre %s.', $permiso, $entityClass));
                }
            }
        }

        if ([] === $entidades) {
            return 0;
        }

        $this->em->wrapInTransaction(function () use ($entidades): void {
            foreach ($entidades as $entidad) {
                $this->em->remove($entidad);
            }
            $this->em->flush();
        });

        return count($entidades);
    }
}
