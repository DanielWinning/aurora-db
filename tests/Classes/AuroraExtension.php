<?php

namespace Luma\Tests\Classes;

use Luma\DatabaseComponent\Attributes\Column;
use Luma\DatabaseComponent\Attributes\Identifier;
use Luma\DatabaseComponent\Model\Aurora;

class AuroraExtension extends Aurora
{
    protected static ?string $schema = 'DatabaseComponentTest';
    protected static ?string $table = 'AuroraExtension';

    #[Identifier]
    #[Column('id')]
    private int $id;

    private string $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}