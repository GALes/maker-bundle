<?php

namespace GALes\MakerBundle\Doctrine;


use Doctrine\Common\Persistence\ManagerRegistry as LegacyManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata as LegacyClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;

interface DoctrineHelperInterface
{
    /**
     * @return LegacyManagerRegistry|ManagerRegistry
     */
    public function getRegistry();

    public function getEntityNamespace(): string;

    public function doesClassUseDriver(string $className, string $driverClass): bool;

    public function isClassAnnotated(string $className): bool;

    public function doesClassUsesAttributes(string $className): bool;

    public function isDoctrineSupportingAttributes(): bool;

    public function getEntitiesForAutocomplete(): array;

    /**
     * @return array|ClassMetadata|LegacyClassMetadata
     */
    public function getMetadata(string $classOrNamespace = null, bool $disconnected = false);

    public function createDoctrineDetails(string $entityClassName): ?EntityDetails;

    public function isClassAMappedEntity(string $className): bool;

    public function getPotentialTableName(string $className): string;

    public function isKeyword(string $name): bool;
}