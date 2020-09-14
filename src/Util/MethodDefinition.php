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

    public function getMethodSignature(string $methodName): string
    {
        $template = 'public function %s(%s)%s';

        $returnType = $this->getReturnType() ? ': '.$this->getReturnType() : '';

        $parameterTemplates = [];

        foreach ($this->getParameters() as $name => $type) {
            $parameterTemplate = '%s %s$%s';

            $paramName = str_replace('&', '', $name);
            [$paramType, $paramDefaultValue] = \is_array($type) ? $type : [$type, null];
            $paramReference = '&' === substr($name, 0, 1);

            $parameterTemplate = sprintf($parameterTemplate, $paramType, $paramReference ? '&' : '', $paramName);
            $parameterTemplate = trim($parameterTemplate);

            $parameterTemplates[] = $parameterTemplate;
        }

        return sprintf($template, $methodName, implode(', ', $parameterTemplates), $returnType);
    }
}
