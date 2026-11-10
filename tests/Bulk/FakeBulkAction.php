<?php

namespace GALes\MakerBundle\Tests\Bulk;

use GALes\MakerBundle\Bulk\BulkActionInterface;
use GALes\MakerBundle\Bulk\BulkResult;

final class FakeBulkAction implements BulkActionInterface
{
    public int $executions = 0;

    /** @var object[] */
    public array $received = [];

    public function __construct(
        private readonly string $name = 'fake',
        private readonly bool $supports = true,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return strtoupper($this->name);
    }

    public function getConfirmMessage(): string
    {
        return 'Confirmar '.$this->name;
    }

    public function supports(string $entityClass): bool
    {
        return $this->supports;
    }

    public function execute(array $entities): BulkResult
    {
        ++$this->executions;
        $this->received = $entities;

        return new BulkResult(count($entities));
    }
}
