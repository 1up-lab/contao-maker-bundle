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
    private $dependencies;
    private $methodDefinition;

    public function __construct(MethodDefinition $methodDefinition, array $dependencies = [])
    {
        $this->dependencies = $dependencies;
        $this->methodDefinition = $methodDefinition;
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
