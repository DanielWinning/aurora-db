<?php

namespace Luma\Tests\Classes;

use Luma\DatabaseComponent\Attributes\Column;
use Luma\DatabaseComponent\Attributes\Identifier;
use Luma\DatabaseComponent\Model\Aurora;

class User extends Aurora
{
    protected static ?string $schema = 'DatabaseComponentTest';

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
}