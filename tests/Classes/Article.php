<?php

namespace Luma\Tests\Classes;

use Luma\AuroraDatabase\Attributes\Column;
use Luma\AuroraDatabase\Attributes\Identifier;
use Luma\AuroraDatabase\Attributes\Schema;
use Luma\AuroraDatabase\Model\Aurora;

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

    #[Column(name: 'dtmCreated')]
    private \DateTimeInterface $created;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return User
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->created;
    }

    /**
     * @param \DateTimeInterface $created
     *
     * @return void
     */
    public function setCreatedAt(\DateTimeInterface $created): void
    {
        $this->created = $created;
    }
}