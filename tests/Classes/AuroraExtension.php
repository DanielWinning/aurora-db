<?php

namespace Luma\Tests\Classes;

use Luma\DatabaseComponent\Model\Aurora;

class AuroraExtension extends Aurora
{
    protected static ?string $schema = 'DatabaseComponentTest';
    protected static ?string $table = 'AuroraExtension';

    private int $id;
    private string $name;

    public function getName(): string
    {
        return $this->name;
    }
}