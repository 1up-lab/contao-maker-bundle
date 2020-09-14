<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\MakerBundle\Maker;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\MakerBundle\Generator\ClassGenerator;
use Contao\MakerBundle\Util\MethodDefinition;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\SplFileInfo;

class MakeDcaCallback extends AbstractMaker
{
    private $framework;
    private $classGenerator;
    private $resourceFinder;

    public function __construct(ContaoFramework $framework, ClassGenerator $classGenerator, ResourceFinder $resourceFinder)
    {
        $this->framework = $framework;
        $this->classGenerator = $classGenerator;
        $this->resourceFinder = $resourceFinder;
    }

    public static function getCommandName(): string
    {
        return 'make:contao:dca-callback';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription('Creates a DCA Callback')
            ->addArgument('className', InputArgument::OPTIONAL, sprintf('Choose a class name for your callback'))
        ;
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $definition = $command->getDefinition();

        // Tables
        $command->addArgument('table', InputArgument::OPTIONAL, 'Choose a table for this callback');
        $argument = $definition->getArgument('table');

        $tables = $this->getTables();

        $question = new Question($argument->getDescription());
        $question->setAutocompleterValues($tables);

        $input->setArgument('table', $io->askQuestion($question));

        // Targets
        $command->addArgument('target', InputArgument::OPTIONAL, 'Choose a target for this callback');
        $argument = $definition->getArgument('target');

        $targets = $this->getTargets();

        $question = new Question($argument->getDescription());
        $question->setAutocompleterValues(array_keys($targets));

        $input->setArgument('target', $io->askQuestion($question));
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $availableTargets = $this->getTargets();

        $target = $input->getArgument('target');
        $table = $input->getArgument('table');

        $name = $input->getArgument('className');

        if (!\array_key_exists($target, $availableTargets)) {
            $io->error(sprintf('Callback definition "%s" not found.', $target));

            return;
        }

        $methodName = 'onCallback';

        /** @var MethodDefinition $definition */
        $definition = $availableTargets[$target];
        $signature = $definition->getMethodSignature($methodName);

        $elementDetails = $generator->createClassNameDetails($name, 'EventListener\\');

        $this->classGenerator->generate([
            'source' => 'dca-callback/Callback.tpl.php',
            'fqcn' => $elementDetails->getFullName(),
            'variables' => [
                'class_name' => $elementDetails->getShortName(),
                'target' => $target,
                'table' => $table,
                'signature' => $signature,
            ],
        ]);

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
    }

    private function getTables(): array
    {
        $this->framework->initialize();

        $files = $this->resourceFinder->findIn('dca')->depth(0)->files()->name('*.php');

        $tables = array_map(function (SplFileInfo $input) {
            return str_replace('.php', '', $input->getRelativePathname());
        }, iterator_to_array($files->getIterator()));

        $tables = array_values($tables);

        return array_unique($tables);
    }

    private function getTargets(): array
    {
        return [
            'config.onload' => new MethodDefinition('void', [
                'dataContainer' => '\Contao\DataContainer',
            ]),
            'config.oncreate' => new MethodDefinition('void', [
                'table' => 'string',
                'insertId' => 'int',
                'fields' => 'array',
                'dataContainer' => '\Contao\DataContainer',
            ]),
            'config.onsubmit' => new MethodDefinition('void', [
                '',
            ]),
        ];
    }
}
