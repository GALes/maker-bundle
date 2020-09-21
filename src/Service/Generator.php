<?php

/*
 * This file is part of the Symfony MakerBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GALes\MakerBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\GeneratorTwigHelper;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\MakerBundle\Generator as SymfonyMakerGenerator;

/**
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * @author Ryan Weaver <weaverryan@gmail.com>
 */
class Generator extends SymfonyMakerGenerator
{
    private $fileManager;
    private $twigHelper;
    private $pendingOperations = [];
    private $namespacePrefix;

    public function __construct(FileManager $fileManager, string $namespacePrefix)
    {
        $this->fileManager = $fileManager;
        $this->twigHelper = new GeneratorTwigHelper($fileManager);
        $this->namespacePrefix = trim($namespacePrefix, '\\');
        parent::__construct($fileManager, $namespacePrefix);
    }
    
    private function addOperation(string $targetPath, string $templateName, array $variables)
    {
        if ($this->fileManager->fileExists($targetPath)) {
            throw new RuntimeCommandException(sprintf('The file "%s" can\'t be generated because it already exists.', $this->fileManager->relativizePath($targetPath)));
        }

        $variables['relative_path'] = $this->fileManager->relativizePath($targetPath);

        $templatePath = $templateName;
        if (!file_exists($templatePath)) {
            $templatePath = __DIR__.'/Resources/skeleton/'.$templateName;

            if (!file_exists($templatePath)) {
                throw new \Exception(sprintf('Cannot find template "%s"', $templateName));
            }
        }

        $this->pendingOperations[$targetPath] = [
            'template' => $templatePath,
            'variables' => $variables,
        ];
    }

}
