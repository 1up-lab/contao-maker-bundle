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
    private $returnType;
    private $parameters;

    public function __construct(?string $returnType, array $parameters)
    {
        $this->returnType = $returnType;
        $this->parameters = $parameters;
    }

    public function getReturnType(): ?string
    {
        return $this->returnType;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getUses(): array
    {
        $objectTypeHints = array_filter($this->parameters, static function ($type) {
            if (null === $type) {
                return false;
            }

            return class_exists($type, true);
        });

        return array_unique($objectTypeHints);
    }

    public function getMethodSignature(string $methodName): string
    {
        $template = 'public function %s(%s)%s';

        $returnType = $this->getReturnType() ? ': '.$this->getReturnType() : '';

        $parameterTemplates = [];

        foreach ($this->getParameters() as $name => $type) {
            $parameterTemplate = '%s %s$%s';

            $paramName = str_replace('&', '', $name);
            [$paramType, $paramDefaultValue] = \is_array($type) ? $type : [$type, null];

            if (null !== $paramType && class_exists($paramType, true)) {
                $paramType = Str::getShortClassName($paramType);
            }

            $paramReference = '&' === substr($name, 0, 1);

            $parameterTemplate = sprintf($parameterTemplate, $paramType, $paramReference ? '&' : '', $paramName);
            $parameterTemplate = trim($parameterTemplate);

            $parameterTemplates[] = $parameterTemplate;
        }

        return sprintf($template, $methodName, implode(', ', $parameterTemplates), $returnType);
    }
}
