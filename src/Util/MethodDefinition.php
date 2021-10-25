<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\MakerBundle\Util;

use Symfony\Bundle\MakerBundle\Str;

class MethodDefinition
{
    private ?string $returnType;

    /**
     * @var array<string, (string|array|null)>
     */
    private array $parameters;

    /**
     * @param array<string, (string|array|null)> $parameters
     */
    public function __construct(?string $returnType, array $parameters)
    {
        $this->returnType = $returnType;
        $this->parameters = $parameters;
    }

    public function getReturnType(): ?string
    {
        return $this->returnType;
    }

    /**
     * @return array<string, (string|array|null)>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return array<int, string>
     */
    public function getUses(): array
    {
        $objectTypeHints = [];

        foreach ($this->parameters as $parameter) {
            if (null === $parameter) {
                continue;
            }

            $type = \is_array($parameter) ? $parameter[0] : $parameter;

            if (!$this->classExists((string) $type)) {
                continue;
            }

            $objectTypeHints[] = $type;
        }

        $returnType = $this->getReturnType();

        // If a return type is set, check if class exists
        // and if so, add it to our imports
        if (null !== $returnType) {
            if ($this->classExists($returnType)) {
                $objectTypeHints[] = $returnType;
            }
        }

        return array_unique($objectTypeHints);
    }

    public function getMethodSignature(string $methodName): string
    {
        $template = 'public function %s(%s)%s';

        $returnType = $this->getReturnType();

        if (null !== $returnType) {
            if ($this->classExists($returnType)) {
                $returnType = Str::getShortClassName($returnType);
            }
        }

        $returnType = $returnType ? ': '.$returnType : '';

        $parameterTemplates = [];

        foreach ($this->getParameters() as $name => $type) {
            $defaultValue = null;

            if (\is_array($type)) {
                [$type, $defaultValue] = $type;
            }

            $parameterTemplate = '%s %s$%s';

            $paramName = str_replace('&', '', $name);
            [$paramType] = \is_array($type) ? $type : [$type, null];

            if (null !== $paramType && class_exists($paramType, true)) {
                $paramType = Str::getShortClassName($paramType);
            }

            $paramReference = 0 === strpos($name, '&');
            $parameterTemplate = sprintf($parameterTemplate, $paramType, $paramReference ? '&' : '', $paramName);

            if (null !== $defaultValue) {
                $parameterTemplate = sprintf('%s = %s', $parameterTemplate, $defaultValue);
            }

            $parameterTemplate = trim($parameterTemplate);
            $parameterTemplates[] = $parameterTemplate;
        }

        return sprintf($template, $methodName, implode(', ', $parameterTemplates), $returnType);
    }

    private function classExists(string $class): bool
    {
        return class_exists($class, true);
    }
}
