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
use Contao\MakerBundle\Util\HookDefinition;
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
    private $classGenerator;

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
            ->addArgument('className', InputArgument::OPTIONAL, sprintf('Choose a class name for your hook'))
        ;
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $definition = $command->getDefinition();

        $command->addArgument('hook', InputArgument::OPTIONAL, 'Choose a hook to implement.');
        $argument = $definition->getArgument('hook');

        $hooks = $this->getAvailableHooks();

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

        $methodName = sprintf('on%s', ucfirst($hook));

        /** @var HookDefinition $definition */
        $definition = $availableHooks[$hook];
        $signature = $definition->getMethodSignature($methodName);

        $elementDetails = $generator->createClassNameDetails($name, 'EventListener\\');

        $this->classGenerator->generate([
            'source' => 'hook/Hook.tpl.php',
            'fqcn' => $elementDetails->getFullName(),
            'variables' => [
                'class_name' => $elementDetails->getShortName(),
                'hook' => $hook,
                'signature' => $signature,
            ],
        ]);

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
    }

    private function getAvailableHooks(): array
    {
        return [
            'activateAccount' => new HookDefinition('void', [
                'member' => 'Contao\MemberModel',
                'module' => 'Contao\Module',
            ]),
            'activateRecipient' => new HookDefinition('void', [
                'mail' => 'string',
                'recipientIds' => 'array',
                'channelIds' => 'array',
            ]),
            'addComment' => new HookDefinition('void', [
                'commentId' => 'int',
                'commentData' => 'array',
                'comments' => 'Contao\Comments',
            ]),
            'addCustomRegexp' => new HookDefinition('bool', [
                'regexp' => 'string',
                'input' => '',
                'widget' => 'Contao\Widget',
            ]),
            'addLogEntry' => new HookDefinition('void', [
                'message' => 'string',
                'func' => 'string',
                'action' => 'string',
            ]),
            'checkCredentials' => new HookDefinition('bool', [
                'username' => 'string',
                'credentials' => 'string',
                'user' => 'Contao\User',
            ]),
            'closeAccount' => new HookDefinition('void', [
                'userId' => 'int',
                'mode' => 'string',
                'module' => 'Contao\Module',
            ]),
            'colorizeLogEntries' => new HookDefinition('string', [
                'row' => 'array',
                'label' => 'string',
            ]),
            'compareThemeFiles' => new HookDefinition('string', [
                'xml' => '\DOMDocument',
                'zip' => 'Contao\ZipReader',
            ]),
            'compileArticle' => new HookDefinition('void', [
                'template' => 'Contao\FrontendTemplate',
                'data' => 'array',
                'module' => 'Contao\Module',
            ]),
            'compileDefinition' => new HookDefinition('string', [
                'row' => 'array',
                'writeToFile' => 'bool',
                'vars' => 'array',
                'parent' => 'array',
            ]),
            'compileFormFields' => new HookDefinition('array', [
                'fields' => 'array',
                'formId' => 'string',
                'form' => 'Contao\Form',
            ]),
            'createDefinition' => new HookDefinition('?array', [
                'key' => 'string',
                'value' => 'string',
                'definition' => 'string',
                '&dataSet' => 'array',
            ]),
            'createNewUser' => new HookDefinition('void', [
                'userId' => 'int',
                'userData' => 'array',
                'module' => 'Contao\Module',
            ]),
            'customizeSearch' => new HookDefinition('void', [
                '&pageIds' => 'array',
                'keywords' => 'string',
                'queryType' => 'string',
                'fuzzy' => 'bool',
                'module' => 'Contao\Module',
            ]),
            'executePostActions' => new HookDefinition('void', [
                'action' => 'string',
                'dc' => 'Contao\DataContainer',
            ]),
            'executePreActions' => new HookDefinition('void', [
                'action' => 'string',
            ]),
            'executeResize' => new HookDefinition('?string', [
                'image' => 'Contao\Image',
            ]),
            'exportTheme' => new HookDefinition('void', [
                'xml' => '\DomDocument',
                'zipArchive' => 'Contao\ZipWriter',
                'themeId' => 'int',
            ]),
            'extractThemeFiles' => new HookDefinition('void', [
                'xml' => '\DomDocument',
                'zipArchive' => 'Contao\ZipReader',
                'themeId' => 'int',
                'mapper' => 'array',
            ]),
            'generateBreadcrumb' => new HookDefinition('array', [
                'items' => 'array',
                'module' => 'Contao\Module',
            ]),
            'generateFrontendUrl' => new HookDefinition('string', [
                'page' => 'array',
                'params' => 'string',
                'url' => 'string',
            ]),
            'generatePage' => new HookDefinition('void', [
                'pageModel' => 'Contao\PageModel',
                'layout' => 'Contao\LayoutModel',
                'pageRegular' => 'Contao\PageRegular',
            ]),
            'generateXmlFiles' => new HookDefinition('void', []),
            'getAllEvents' => new HookDefinition('array', [
                'events' => 'array',
                'calendars' => 'array',
                'timeStart' => 'int',
                'timeEnd' => 'int',
                'module' => 'Contao\Module',
            ]),
            'getArticle' => new HookDefinition('void', [
                'article' => 'Contao\ArticleModel',
            ]),
            'getArticles' => new HookDefinition('?string', [
                'pageId' => 'int',
                'column' => 'string',
            ]),
            'getAttributesFromDca' => new HookDefinition('array', [
                'attributes' => 'array',
                'dc' => ['Contao\DataContainer', 'null'],
            ]),
            'getCombinedFile' => new HookDefinition('string', [
                'content' => 'string',
                'key' => 'string',
                'mode' => 'string',
                'file' => 'array',
            ]),
            'getContentElement' => new HookDefinition('string', [
                'contentModel' => 'Contao\ContentModel',
                'buffer' => 'string',
                'contentElement' => 'Contao\ContentElement',
            ]),
            'getCountries' => new HookDefinition('void', [
                '&translatedCountries' => 'array',
                'allCountries' => 'array',
            ]),
            'getForm' => new HookDefinition('string', [
                'form' => 'Contao\FormModel',
                'buffer' => 'string',
            ]),
            'getFrontendModule' => new HookDefinition('string', [
                'moduleModel' => 'Contao\ModuleModel',
                'buffer' => 'string',
                'module' => 'Contao\Module',
            ]),
            'getImage' => new HookDefinition('?string', [
                'originalPath' => 'string',
                'width' => 'int',
                'height' => 'int',
                'mode' => 'string',
                'cacheName' => 'string',
                'file' => 'Contao\File',
                'targetPath' => 'string',
                'imageObject' => 'Contao\Image',
            ]),
            'getLanguages' => new HookDefinition('void', [
                '&compiledLanguages' => 'array',
                'languages' => 'array',
                'langsNative' => 'array',
                'installedOnly' => 'bool',
            ]),
            'getPageIdFromUrl' => new HookDefinition('array', [
                'fragments' => 'array',
            ]),
            'getPageLayout' => new HookDefinition('void', [
                'pageModel' => 'Contao\PageModel',
                'layout' => 'Contao\LayoutModel',
                'pageRegular' => 'Contao\PageRegular',
            ]),
            'getPageStatusIcon' => new HookDefinition('string', [
                'page' => 'object',
                'image' => 'string',
            ]),
        ];
    }
}
