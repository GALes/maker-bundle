<?php

namespace GALes\MakerBundle\Bulk;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Contrato de una acción masiva. Las clases que lo implementan se registran solas
 * con el tag "gales_maker.bulk_action" y cada CRUD decide cuáles expone en su BULK_ACTIONS.
 */
#[AutoconfigureTag('gales_maker.bulk_action')]
interface BulkActionInterface
{
    /** Identificador de la acción: /^[a-z][a-z0-9_]*$/ */
    public function getName(): string;

    public function getLabel(): string;

    public function getConfirmMessage(): string;

    /** @param class-string $entityClass */
    public function supports(string $entityClass): bool;

    /**
     * Aplica la acción al lote. No debe llamar a flush(): lo hace el ejecutor dentro de la transacción.
     *
     * @param object[] $entities
     */
    public function execute(array $entities): BulkResult;
}
