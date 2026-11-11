<?php

namespace GALes\MakerBundle\Bulk;

/**
 * Resuelve las acciones masivas registradas con el tag "gales_maker.bulk_action".
 */
class BulkActionRegistry
{
    /** @var array<string, BulkActionInterface> */
    private array $actions = [];

    /** @param iterable<BulkActionInterface> $actions */
    public function __construct(iterable $actions)
    {
        foreach ($actions as $action) {
            $name = $action->getName();
            if (1 !== preg_match('/^[a-z][a-z0-9_]*$/', $name)) {
                throw new \LogicException(sprintf('El nombre "%s" de la acción masiva %s no es válido: debe cumplir /^[a-z][a-z0-9_]*$/.', $name, $action::class));
            }
            if (isset($this->actions[$name])) {
                throw new \LogicException(sprintf('La acción masiva "%s" está duplicada (%s y %s).', $name, $this->actions[$name]::class, $action::class));
            }
            $this->actions[$name] = $action;
        }
    }

    /** @param class-string $entityClass */
    public function get(string $name, string $entityClass): BulkActionInterface
    {
        $action = $this->actions[$name] ?? null;
        if (null === $action || !$action->supports($entityClass)) {
            throw new UnknownBulkActionException(sprintf('La acción masiva "%s" no existe o no aplica a %s.', $name, $entityClass));
        }

        return $action;
    }

    /**
     * @param string[]     $names       Nombres en el orden en que se muestran
     * @param class-string $entityClass
     *
     * @return list<array{name: string, label: string, confirm: string}>
     */
    public function describe(array $names, string $entityClass): array
    {
        $described = [];
        foreach ($names as $name) {
            $action = $this->actions[$name] ?? null;
            if (null === $action || !$action->supports($entityClass)) {
                continue;
            }
            $described[] = ['name' => $name, 'label' => $action->getLabel(), 'confirm' => $action->getConfirmMessage()];
        }

        return $described;
    }
}
