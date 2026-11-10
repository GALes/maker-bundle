<?php

namespace GALes\MakerBundle\Bulk;

use Doctrine\Persistence\ManagerRegistry;

/**
 * Eliminación masiva de registros.
 */
class DeleteBulkAction implements BulkActionInterface
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    public function getName(): string
    {
        return 'delete';
    }

    public function getLabel(): string
    {
        return 'ELIMINAR';
    }

    public function getConfirmMessage(): string
    {
        return '¿Está seguro?';
    }

    public function supports(string $entityClass): bool
    {
        return true;
    }

    public function execute(array $entities): BulkResult
    {
        foreach ($entities as $entity) {
            $this->managerRegistry->getManagerForClass($entity::class)->remove($entity);
        }

        return new BulkResult(count($entities), 'El/Los registro/s se ha/n eliminado exitosamente');
    }
}
