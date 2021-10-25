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

class CallbackDefinition
{
    private MethodDefinition $methodDefinition;
    private array $dependencies;

    public function __construct(MethodDefinition $methodDefinition, array $dependencies = [])
    {
        $this->methodDefinition = $methodDefinition;
        $this->dependencies = $dependencies;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function getMethodDefinition(): MethodDefinition
    {
        return $this->methodDefinition;
    }
}
