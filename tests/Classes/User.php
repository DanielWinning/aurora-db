<?php

namespace Luma\Tests\Classes;

use Luma\DatabaseComponent\Attributes\Column;
use Luma\DatabaseComponent\Attributes\Identifier;
use Luma\DatabaseComponent\Attributes\Schema;
use Luma\DatabaseComponent\Model\Aurora;

#[Schema('DatabaseComponentTest')]
class User extends Aurora
{
    #[Identifier]
    #[Column('intUserId')]
    private int $id;

    #[Column('strUsername')]
    private string $username;

    #[Column('strEmailAddress')]
    private string $strEmailAddress;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

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