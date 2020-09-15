<?php

declare(strict_types=1);

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
