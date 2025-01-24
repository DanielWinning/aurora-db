<?php

namespace Luma\Tests\Classes;

use Luma\AuroraDatabase\Attributes\Column;
use Luma\AuroraDatabase\Attributes\Identifier;
use Luma\AuroraDatabase\Attributes\Schema;
use Luma\AuroraDatabase\Attributes\Table;
use Luma\AuroraDatabase\Model\Aurora;

#[Schema('DatabaseComponentTest')]
#[Table('KeepEmpty')]
class KeepEmpty extends Aurora
{
    #[Identifier]
    #[Column('intKeepEmptyId')]
    protected int $id;

    #[Column('strName')]
    protected string $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}