<?php

namespace Luma\Tests\Classes;

use Luma\DatabaseComponent\Attributes\Column;
use Luma\DatabaseComponent\Model\Aurora;

class User extends Aurora
{
    protected static ?string $identifier = 'intUserId';
    protected static ?string $schema = 'DatabaseComponentTest';

    #[Column('intUserId')]
    private int $intUserId;

    #[Column('strUsername')]
    private string $username;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->intUserId;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }
}