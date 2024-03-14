<?php

namespace Luma\Tests\Classes;

use Luma\DatabaseComponent\Model\Aurora;

class User extends Aurora
{
    protected static ?string $identifier = 'intUserId';
    protected static ?string $schema = 'DatabaseComponentTest';
    private int $intUserId;
    private string $strUsername;

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
        return $this->strUsername;
    }
}