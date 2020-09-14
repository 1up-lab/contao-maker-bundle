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

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DcaGenerator implements GeneratorInterface
{
    private $filesystem;
    private $fileManager;
    private $generator;
    private $resourcesPaths;
    private $projectDir;

    public function __construct(
        Filesystem $filesystem,
        FileManager $fileManager,
        Generator $generator,
        array $resourcesPaths,
        string $projectDir)
    {
        $this->filesystem = $filesystem;
        $this->fileManager = $fileManager;
        $this->generator = $generator;
        $this->resourcesPaths = $resourcesPaths;
        $this->projectDir = $projectDir;
    }

    public function generate(array $options): string
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $options = $resolver->resolve($options);

        $source = $this->getSourcePath($options['source']);
        $target = sprintf('%s/dca/%s.php', $this->getConfigRoot(), ltrim($options['domain'], '/'));

        $fileExists = $this->filesystem->exists($target);

        $variables = array_merge([
            'append' => $fileExists,
            'element_name' => $options['element'],
        ], $options['variables']);

        $contents = $this->fileManager->parseTemplate($source, $variables);
        $contents = ltrim($contents);

        if ($fileExists) {
            $contents = sprintf("%s\n\n%s", rtrim(file_get_contents($target)), $contents);
        }

        $this->filesystem->dumpFile($target, $contents);

        $comment = !$fileExists ? '<fg=blue>created</>' : '<fg=yellow>updated</>';
        $this->addCommentLine($options['io'], $comment, $target);

        return $target;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'domain',
            'source',
            'element',
            'io',
        ]);

        $resolver->setDefaults([
            'variables' => [],
        ]);

        $resolver->setAllowedTypes('io', [
            ConsoleStyle::class,
        ]);
    }

    protected function addCommentLine(ConsoleStyle $io, $action, $target): void
    {
        $io->comment(sprintf(
            '%s: %s',
            $action,
            $this->fileManager->relativizePath($target)
        ));
    }

    private function getConfigRoot(): string
    {
        return $this->resourcesPaths[array_key_last($this->resourcesPaths)];
    }

    private function getSourcePath(string $path)
    {
        return sprintf('%s/../Resources/skeleton/%s', __DIR__, ltrim($path, '/'));
    }
}
