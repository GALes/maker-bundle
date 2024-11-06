<?php


namespace GALes\MakerBundle\Helper;


use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\View\TwitterBootstrap4View;
use Symfony\Component\HttpFoundation\Request;

class OrderByHelper
{
    private static function getElements(string $field) : array
    {
        $retorno = [];
        $matches = [];
        while ($field) {
            preg_match_all('/([A-Za-z0-9_-]+)[\.]*/', $field, $matches);
            if ($matches) {
                array_unshift($retorno, $matches[1]);
                $field = str_replace($matches[0], '', $field);
            }
        }
        return $retorno ? $retorno[0] : $retorno;
    }

    private static function addOrderByToQueryRecursive(QueryBuilder $queryBuilder, array $elements, string $rootAlias, string $sortOrder)
    {
        $attribute = array_shift($elements);
        if ( $attribute && $elements ) {
            $queryBuilder
                ->leftJoin($rootAlias . '.' . $attribute, $attribute)
            ;
            $queryBuilder = self::addOrderByToQueryRecursive($queryBuilder, $elements, $attribute, $sortOrder);
        }
        else {
            $attribute = $rootAlias . '.' . $attribute;
            $queryBuilder->orderBy($attribute, $sortOrder);
        }

        return $queryBuilder;
    }
    
    public static function addOrderByToQuery(QueryBuilder $queryBuilder, string $field, string $sortOrder): QueryBuilder
    {
        $elements = self::getElements($field);
        $rootAlias = $queryBuilder->getRootAlias();
        $queryBuilder = self::addOrderByToQueryRecursive($queryBuilder, $elements, $rootAlias, $sortOrder);

        return $queryBuilder;
    }

}