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

class MakeHook extends AbstractMaker
{
    private ClassGenerator $classGenerator;

    public function __construct(ClassGenerator $classGenerator)
    {
        $this->classGenerator = $classGenerator;
    }

    public static function getCommandName(): string
    {
        return 'make:contao:hook';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription('Creates a hook')
            ->addArgument('className', InputArgument::OPTIONAL, 'Choose a class name for your hook')
        ;
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $definition = $command->getDefinition();

        $command->addArgument('hook', InputArgument::OPTIONAL, 'Choose a hook to implement.');
        $argument = $definition->getArgument('hook');

        $hooks = $this->getAvailableHooks();

        $io->writeln(' <fg=green>Suggested Tables:</>');
        $io->listing(array_keys($hooks));

        $question = new Question($argument->getDescription());
        $question->setAutocompleterValues(array_keys($hooks));

        $input->setArgument('hook', $io->askQuestion($question));
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $availableHooks = $this->getAvailableHooks();
        $hook = $input->getArgument('hook');
        $name = $input->getArgument('className');

        if (!\array_key_exists($hook, $availableHooks)) {
            $io->error(sprintf('Hook definition "%s" not found.', $hook));

            return;
        }

        /** @var MethodDefinition $definition */
        $definition = $availableHooks[$hook];
        $signature = $definition->getMethodSignature('__invoke');
        $uses = $definition->getUses();

        $elementDetails = $generator->createClassNameDetails($name, 'EventListener\\');

        $this->classGenerator->generate([
            'source' => 'hook/Hook.tpl.php',
            'fqcn' => $elementDetails->getFullName(),
            'variables' => [
                'className' => $elementDetails->getShortName(),
                'hook' => $hook,
                'signature' => $signature,
                'uses' => $uses,
            ],
        ]);

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
    }

    private function getAvailableHooks(): array
    {
        return [
            'activateAccount' => new MethodDefinition('void', [
                'member' => 'Contao\MemberModel',
                'module' => 'Contao\Module',
            ]),
            'activateRecipient' => new MethodDefinition('void', [
                'mail' => 'string',
                'recipientIds' => 'array',
                'channelIds' => 'array',
            ]),
            'addComment' => new MethodDefinition('void', [
                'commentId' => 'int',
                'commentData' => 'array',
                'comments' => 'Contao\Comments',
            ]),
            'addCustomRegexp' => new MethodDefinition('bool', [
                'regexp' => 'string',
                'input' => '',
                'widget' => 'Contao\Widget',
            ]),
            'addLogEntry' => new MethodDefinition('void', [
                'message' => 'string',
                'func' => 'string',
                'action' => 'string',
            ]),
            'checkCredentials' => new MethodDefinition('bool', [
                'username' => 'string',
                'credentials' => 'string',
                'user' => 'Contao\User',
            ]),
            'closeAccount' => new MethodDefinition('void', [
                'userId' => 'int',
                'mode' => 'string',
                'module' => 'Contao\Module',
            ]),
            'colorizeLogEntries' => new MethodDefinition('string', [
                'row' => 'array',
                'label' => 'string',
            ]),
            'compareThemeFiles' => new MethodDefinition('string', [
                'xml' => '\DOMDocument',
                'zip' => 'Contao\ZipReader',
            ]),
            'compileArticle' => new MethodDefinition('void', [
                'template' => 'Contao\FrontendTemplate',
                'data' => 'array',
                'module' => 'Contao\Module',
            ]),
            'compileDefinition' => new MethodDefinition('string', [
                'row' => 'array',
                'writeToFile' => 'bool',
                'vars' => 'array',
                'parent' => 'array',
            ]),
            'compileFormFields' => new MethodDefinition('array', [
                'fields' => 'array',
                'formId' => 'string',
                'form' => 'Contao\Form',
            ]),
            'createDefinition' => new MethodDefinition('?array', [
                'key' => 'string',
                'value' => 'string',
                'definition' => 'string',
                '&dataSet' => 'array',
            ]),
            'createNewUser' => new MethodDefinition('void', [
                'userId' => 'int',
                'userData' => 'array',
                'module' => 'Contao\Module',
            ]),
            'customizeSearch' => new MethodDefinition('void', [
                '&pageIds' => 'array',
                'keywords' => 'string',
                'queryType' => 'string',
                'fuzzy' => 'bool',
                'module' => 'Contao\Module',
            ]),
            'executePostActions' => new MethodDefinition('void', [
                'action' => 'string',
                'dc' => 'Contao\DataContainer',
            ]),
            'executePreActions' => new MethodDefinition('void', [
                'action' => 'string',
            ]),
            'executeResize' => new MethodDefinition('?string', [
                'image' => 'Contao\Image',
            ]),
            'exportTheme' => new MethodDefinition('void', [
                'xml' => '\DomDocument',
                'zipArchive' => 'Contao\ZipWriter',
                'themeId' => 'int',
            ]),
            'extractThemeFiles' => new MethodDefinition('void', [
                'xml' => '\DomDocument',
                'zipArchive' => 'Contao\ZipReader',
                'themeId' => 'int',
                'mapper' => 'array',
            ]),
            'generateBreadcrumb' => new MethodDefinition('array', [
                'items' => 'array',
                'module' => 'Contao\Module',
            ]),
            'generateFrontendUrl' => new MethodDefinition('string', [
                'page' => 'array',
                'params' => 'string',
                'url' => 'string',
            ]),
            'generatePage' => new MethodDefinition('void', [
                'pageModel' => 'Contao\PageModel',
                'layout' => 'Contao\LayoutModel',
                'pageRegular' => 'Contao\PageRegular',
            ]),
            'generateXmlFiles' => new MethodDefinition('void', []),
            'getAllEvents' => new MethodDefinition('array', [
                'events' => 'array',
                'calendars' => 'array',
                'timeStart' => 'int',
                'timeEnd' => 'int',
                'module' => 'Contao\Module',
            ]),
            'getArticle' => new MethodDefinition('void', [
                'article' => 'Contao\ArticleModel',
            ]),
            'getArticles' => new MethodDefinition('?string', [
                'pageId' => 'int',
                'column' => 'string',
            ]),
            'getAttributesFromDca' => new MethodDefinition('array', [
                'attributes' => 'array',
                'dc' => ['Contao\DataContainer', 'null'],
            ]),
            'getCombinedFile' => new MethodDefinition('string', [
                'content' => 'string',
                'key' => 'string',
                'mode' => 'string',
                'file' => 'array',
            ]),
            'getContentElement' => new MethodDefinition('string', [
                'contentModel' => 'Contao\ContentModel',
                'buffer' => 'string',
                'contentElement' => 'Contao\ContentElement',
            ]),
            'getCountries' => new MethodDefinition('void', [
                '&translatedCountries' => 'array',
                'allCountries' => 'array',
            ]),
            'getForm' => new MethodDefinition('string', [
                'form' => 'Contao\FormModel',
                'buffer' => 'string',
            ]),
            'getFrontendModule' => new MethodDefinition('string', [
                'moduleModel' => 'Contao\ModuleModel',
                'buffer' => 'string',
                'module' => 'Contao\Module',
            ]),
            'getImage' => new MethodDefinition('?string', [
                'originalPath' => 'string',
                'width' => 'int',
                'height' => 'int',
                'mode' => 'string',
                'cacheName' => 'string',
                'file' => 'Contao\File',
                'targetPath' => 'string',
                'imageObject' => 'Contao\Image',
            ]),
            'getLanguages' => new MethodDefinition('void', [
                '&compiledLanguages' => 'array',
                'languages' => 'array',
                'langsNative' => 'array',
                'installedOnly' => 'bool',
            ]),
            'getPageIdFromUrl' => new MethodDefinition('array', [
                'fragments' => 'array',
            ]),
            'getPageLayout' => new MethodDefinition('void', [
                'pageModel' => 'Contao\PageModel',
                'layout' => 'Contao\LayoutModel',
                'pageRegular' => 'Contao\PageRegular',
            ]),
            'getPageStatusIcon' => new MethodDefinition('string', [
                'page' => 'object',
                'image' => 'string',
            ]),
        ];
    }
}
