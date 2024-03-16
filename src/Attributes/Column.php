<?php

namespace Luma\AuroraDatabase\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Column
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}