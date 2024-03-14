<?php

namespace Luma\Tests\Classes;

use Luma\DatabaseComponent\Attributes\Column;
use Luma\DatabaseComponent\Model\Aurora;

class Article extends Aurora
{
    protected static ?string $schema = 'DatabaseComponentTest';
    protected static ?string $identifier = 'intArticleId';

    #[Column('strTitle')]
    private string $title;

    #[Column('intAuthorId')]
    private User $author;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return User
     */
    public function getAuthor(): User
    {
        return $this->author;
    }
}