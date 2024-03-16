<?php

namespace Luma\Tests\Classes;

use Luma\AuroraDatabase\Attributes\Column;
use Luma\AuroraDatabase\Attributes\Identifier;
use Luma\AuroraDatabase\Attributes\Schema;
use Luma\AuroraDatabase\Model\Aurora;

#[Schema('DatabaseComponentTest')]
class User extends Aurora
{
    #[Identifier]
    #[Column('intUserId')]
    protected int $id;

    #[Column('strUsername')]
    private string $username;

    #[Column('strEmailAddress')]
    private string $strEmailAddress;

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmailAddress(): string
    {
        return $this->strEmailAddress;
    }
}