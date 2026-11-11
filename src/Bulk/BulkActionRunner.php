<?php

namespace GALes\MakerBundle\Bulk;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Ejecuta una acción masiva sobre la selección recibida.
 *
 * Verifica el permiso sobre todo el lote antes de ejecutar: si se niega alguna entidad
 * lanza AccessDeniedException (403) sin ejecutar nada. Los ids inexistentes se ignoran.
 * La acción corre en una única transacción y el flush lo hace la transacción.
 */
class BulkActionRunner
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly Security $security,
        private readonly BulkActionRegistry $registry,
    ) {
    }

    /**
     * @param class-string          $entityClass
     * @param array<mixed>          $ids
     * @param array<string, string> $allowed     Nombre de la acción => atributo del Voter que la autoriza
     *
     * @throws UnknownBulkActionException si la acción no está en el mapa o no aplica a la entidad
     * @throws \InvalidArgumentException  si la acción no declara un permiso
     * @throws AccessDeniedException      si se niega el permiso sobre alguna entidad
     */
    public function run(string $entityClass, string $actionName, array $ids, array $allowed): BulkResult
    {
        if (!array_key_exists($actionName, $allowed)) {
            throw new UnknownBulkActionException(sprintf('La acción masiva "%s" no está habilitada para %s.', $actionName, $entityClass));
        }
        $permission = $allowed[$actionName];
        if (!is_string($permission) || '' === $permission) {
            throw new \InvalidArgumentException(sprintf('La acción masiva "%s" debe declarar un permiso no vacío.', $actionName));
        }

        $action = $this->registry->get($actionName, $entityClass);

        $ids = array_values(array_unique(array_filter(
            $ids,
            static fn ($id) => (is_int($id) || is_string($id)) && '' !== $id
        )));
        if ([] === $ids) {
            return new BulkResult(0);
        }

        $manager = $this->managerRegistry->getManagerForClass($entityClass);
        $idFields = $manager->getClassMetadata($entityClass)->getIdentifierFieldNames();
        if (1 !== count($idFields)) {
            throw new \LogicException(sprintf('%s tiene clave compuesta: las acciones masivas sólo soportan un identificador simple.', $entityClass));
        }

        $entities = $manager->getRepository($entityClass)->findBy([$idFields[0] => $ids]);

        foreach ($entities as $entity) {
            if (!$this->security->isGranted($permission, $entity)) {
                throw new AccessDeniedException(sprintf('Acceso denegado: "%s" sobre %s.', $permission, $entityClass));
            }
        }

        if ([] === $entities) {
            return new BulkResult(0);
        }

        return $manager->wrapInTransaction(static fn () => $action->execute($entities));
    }
}
