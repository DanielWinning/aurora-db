<?php

namespace Luma\Tests\Classes;

use Luma\DatabaseComponent\Attributes\Column;
use Luma\DatabaseComponent\Aurora;

class Article extends Aurora
{
    protected static ?string $schema = 'DatabaseComponentTest';
    protected static ?string $identifier = 'intArticleId';

    #[Column('strTitle')]
    private string $title;

    #[Column('intAuthorId')]
    private int $intAuthorId;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->intAuthorId;
    }
}