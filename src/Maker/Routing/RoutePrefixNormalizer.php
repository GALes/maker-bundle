<?php

namespace GALes\MakerBundle\Maker\Routing;

/**
 * Normaliza y valida el prefijo de ruta del CRUD generado (por ejemplo /admin).
 *
 * El prefijo se escribe tal cual dentro de #[Route("...")], así que sólo admite letras, números, guion, guion bajo,
 * punto, barra y llaves (para parámetros como {_locale}).
 */
final class RoutePrefixNormalizer
{
    public const PATTERN = '/^[A-Za-z0-9_.\-\/{}]+$/';

    /**
     * Devuelve el prefijo con barra inicial y sin barra final ("admin/" -> "/admin"), o '' si no hay prefijo
     * (null, vacío o sólo barras).
     *
     * @throws \InvalidArgumentException si el prefijo trae caracteres no válidos
     */
    public static function normalize(?string $prefix): string
    {
        $prefix = trim((string) $prefix);
        if ('' === $prefix) {
            return '';
        }

        if (1 !== preg_match(self::PATTERN, $prefix)) {
            throw new \InvalidArgumentException(sprintf('"%s" no es un prefijo de ruta válido: usá sólo letras, números, "-", "_", ".", "/" y llaves (por ejemplo /admin).', $prefix));
        }

        $prefix = trim((string) preg_replace('#/+#', '/', $prefix), '/');

        return '' === $prefix ? '' : '/'.$prefix;
    }

    /**
     * Antepone el prefijo a la ruta de la entidad ("/admin" + "/solicitud" -> "/admin/solicitud").
     */
    public static function apply(string $prefix, string $routePath): string
    {
        return $prefix.'/'.ltrim($routePath, '/');
    }
}
