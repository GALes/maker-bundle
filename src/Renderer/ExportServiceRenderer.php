<?php

/*
 * This file is part of the Symfony MakerBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GALes\MakerBundle\Renderer;

use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

/**
 * @internal
 */
final class ExportServiceRenderer
{
    private $generator;

    private $fileManager;

    public function __construct(Generator $generator, FileManager $fileManager)
    {
        $this->generator    = $generator;
        $this->fileManager  = $fileManager;
    }

    public function generateClass(string $className, string $templateName, array $variables = []): string
    {
        $targetPath = $targetPath = $this->fileManager->getRelativePathForFutureClass($className);

        $variables = array_merge($variables, [
            'class_name' => Str::getShortClassName($className),
            'namespace' => Str::getNamespace($className),
        ]);

        $this->generator->generateFile($targetPath, $templateName, $variables);

        return $targetPath;
    }
    
}
