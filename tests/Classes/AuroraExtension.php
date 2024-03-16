<?php

namespace Luma\Tests\Classes;

use Luma\AuroraDatabase\Attributes\Column;
use Luma\AuroraDatabase\Attributes\Identifier;
use Luma\AuroraDatabase\Attributes\Schema;
use Luma\AuroraDatabase\Attributes\Table;
use Luma\AuroraDatabase\Model\Aurora;

#[Schema('DatabaseComponentTest')]
#[Table('AuroraExtension')]
class AuroraExtension extends Aurora
{
    #[Identifier]
    #[Column('id')]
    protected int $id;

    #[Column('name')]
    private string $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}