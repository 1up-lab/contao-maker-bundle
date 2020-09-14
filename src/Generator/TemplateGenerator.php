<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\MakerBundle\Generator;

use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateGenerator implements GeneratorInterface
{
    private $generator;
    private $fileManager;

    public function __construct(Generator $generator, FileManager $fileManager)
    {
        $this->generator = $generator;
        $this->fileManager = $fileManager;
    }

    public function generate(array $options): string
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $options = $resolver->resolve($options);

        $this->generator->generateTemplate(
            $options['target'],
            $this->getSourcePath($options['source']), $options['variables']);

        return $this->fileManager->getPathForTemplate($options['target']);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'target',
            'source',
        ]);

        $resolver->setDefaults([
            'variables' => [],
        ]);
    }

    private function getSourcePath(string $path)
    {
        return sprintf('%s/../Resources/skeleton/%s', __DIR__, ltrim($path, '/'));
    }
}
