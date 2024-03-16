<?php

namespace Luma\AuroraDatabase\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Table
{
    public string $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }
}