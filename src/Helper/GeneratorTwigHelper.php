<?php

/*
 * This file is part of the Symfony MakerBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GALes\MakerBundle\Helper;

use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Str;

/**
 * @author Sadicov Vladimir <sadikoff@gmail.com>
 */
final class GeneratorTwigHelper
{
    private $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public function getEntityFieldPrintCode($entity, $field): string
    {
        $twigField = preg_replace_callback('/(?!^)_([a-z0-9])/', function ($s) {
            return strtoupper($s[1]);
        }, $field['fieldName']);
        $printCode = $entity.'.'.str_replace('_', '', $twigField);

        switch ($field['type']) {
            case 'datetimetz_immutable':
            case 'datetimetz':
                $printCode .= ' ? '.$printCode.'|date(\'d-m-Y H:i:s T\') : \'\'';
                break;
            case 'datetime_immutable':
            case 'datetime':
                $printCode .= ' ? '.$printCode.'|date(\'d-m-Y H:i:s\') : \'\'';
                break;
            case 'dateinterval':
                $printCode .= ' ? '.$printCode.'.format(\'%y año/s, %m mes/es, %d día/s\') : \'\'';
                break;
            case 'date_immutable':
            case 'date':
                $printCode .= ' ? '.$printCode.'|date(\'d-m-Y\') : \'\'';
                break;
            case 'time_immutable':
            case 'time':
                $printCode .= ' ? '.$printCode.'|date(\'H:i:s\') : \'\'';
                break;
            case 'json':
                $printCode .= ' ? '.$printCode.'|json_encode : \'\'';
                break;
            case 'array':
                $printCode .= ' ? '.$printCode.'|join(\', \') : \'\'';
                break;
            case 'boolean':
                $printCode .= ' ? \'Si\' : \'No\'';
                break;
        }

        return $printCode;
    }

    public function getHeadPrintCode( $title, $baseTemplate = null ): string
    {
        if ($baseTemplate === null && $this->fileManager->fileExists($this->fileManager->getPathForTemplate('base.html.twig'))) {
            return <<<TWIG
{% extends 'base.html.twig' %}

{% block title %}$title{% endblock %}

TWIG;
        }
        else if ( is_string($baseTemplate) && trim($baseTemplate) != '' ) {
            return <<<TWIG
{% extends '$baseTemplate' %}

{% block title %}$title{% endblock %}

TWIG;
        }

        return <<<HTML
<!DOCTYPE html>

<title>$title</title>

HTML;
    }

    public function getFileLink($path, $text = null, $line = 0): string
    {
        $text = $text ?: $path;

        return "<a href=\"{{ '$path'|file_link($line) }}\">$text</a>";
    }

    public function asHumanWords(string $variableName): string
    {
        return Str::asHumanWords($variableName);
    }
}
