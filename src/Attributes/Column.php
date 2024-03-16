<?php

namespace Luma\AuroraDatabase\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Column
{
    public string $name;
    public bool $nullable;
    public bool $hasDefault = false;

    public function __construct(string $name, bool $nullable = true, bool $hasDefault = false)
    {
        $this->name = $name;
        $this->nullable = $nullable;
        $this->hasDefault = $hasDefault;
    }
}