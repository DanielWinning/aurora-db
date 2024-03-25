<?php

namespace Luma\Tests\Classes;

use Luma\AuroraDatabase\Attributes\Column;
use Luma\AuroraDatabase\Attributes\Identifier;
use Luma\AuroraDatabase\Attributes\Schema;
use Luma\AuroraDatabase\Attributes\Table;
use Luma\AuroraDatabase\Model\Aurora;

#[Schema('Security')]
#[Table('tblPermission')]
class Permission extends Aurora
{
    #[Identifier]
    #[Column('intPermissionId')]
    protected int $id;

    #[Column('strPermissionName')]
    private string $name;

    #[Column('strPermissionHandle')]
    private string $handle;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}