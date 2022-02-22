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

    public static function addOrderByToQuery(QueryBuilder $queryBuilder, string $field, string $sortOrder): QueryBuilder
    {
        $elements = self::getElements($field);

        $rootAlias = $queryBuilder->getRootAlias();
        $sortCol = array_pop($elements);

        if ( $sortCol && $elements ) {
            $joinTable = array_pop($elements);
            $joinCol = $joinTable . '.' . $sortCol;
            $queryBuilder
                ->innerJoin($rootAlias . '.' . $joinTable, $joinTable)
                ->orderBy($joinCol, $sortOrder)
            ;
        }
        else {
            $sortCol = $rootAlias . '.' . $sortCol;
            $queryBuilder->orderBy($sortCol, $sortOrder);
        }

        return $queryBuilder;
    }

}