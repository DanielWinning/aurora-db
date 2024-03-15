<?php

namespace Luma\Tests\Classes;

use Luma\DatabaseComponent\Attributes\Column;
use Luma\DatabaseComponent\Attributes\Identifier;
use Luma\DatabaseComponent\Attributes\Schema;
use Luma\DatabaseComponent\Model\Aurora;

#[Schema('DatabaseComponentTest')]
class Article extends Aurora
{
    #[Identifier]
    #[Column('intArticleId')]
    protected int $id;

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