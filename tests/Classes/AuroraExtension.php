<?php

namespace Luma\Tests\Classes;

use Luma\DatabaseComponent\Attributes\Column;
use Luma\DatabaseComponent\Attributes\Identifier;
use Luma\DatabaseComponent\Attributes\Schema;
use Luma\DatabaseComponent\Attributes\Table;
use Luma\DatabaseComponent\Model\Aurora;

#[Schema('DatabaseComponentTest')]
#[Table('AuroraExtension')]
class AuroraExtension extends Aurora
{
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