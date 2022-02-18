<?php


namespace GALes\MakerBundle\Doctrine;

use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;

abstract class DoctrineHelperDecorator implements DoctrineHelperInterface
{
    private $doctrineHelper;

    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @inheritDoc
     */
    public function getRegistry()
    {
        return $this->doctrineHelper->getRegistry();
    }

    public function getEntityNamespace(): string
    {
        return $this->doctrineHelper->getEntityNamespace();
    }

    public function doesClassUseDriver(string $className, string $driverClass): bool
    {
        return $this->doctrineHelper->doesClassUseDriver($className, $driverClass);
    }

    public function isClassAnnotated(string $className): bool
    {
        return $this->doctrineHelper->isClassAnnotated($className);
    }

    public function doesClassUsesAttributes(string $className): bool
    {
        return $this->doctrineHelper->doesClassUsesAttributes($className);
    }

    public function isDoctrineSupportingAttributes(): bool
    {
        return $this->doctrineHelper->isDoctrineSupportingAttributes();
    }

    public function getEntitiesForAutocomplete(): array
    {
        return $this->doctrineHelper->getEntitiesForAutocomplete();
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(string $classOrNamespace = null, bool $disconnected = false)
    {
        return $this->doctrineHelper->getMetadata($classOrNamespace, $disconnected);
    }

    public function createDoctrineDetails(string $entityClassName): ?EntityDetails
    {
        return $this->doctrineHelper->createDoctrineDetails($entityClassName);
    }

    public function isClassAMappedEntity(string $className): bool
    {
        return $this->doctrineHelper->isClassAMappedEntity($className);
    }

    public function getPotentialTableName(string $className): string
    {
        return $this->doctrineHelper->getPotentialTableName($className);
    }

    public function isKeyword(string $name): bool
    {
        return $this->doctrineHelper->isKeyword($name);
    }
}