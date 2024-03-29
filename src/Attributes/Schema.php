<?php

namespace Luma\AuroraDatabase\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Schema
{
    public string $schema;

    public function __construct(string $schema)
    {
        $this->schema = $schema;
    }
}