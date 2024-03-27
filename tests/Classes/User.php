<?php

namespace Luma\Tests\Classes;

use Luma\AuroraDatabase\Attributes\AuroraCollection;
use Luma\AuroraDatabase\Attributes\Column;
use Luma\AuroraDatabase\Attributes\Identifier;
use Luma\AuroraDatabase\Attributes\Schema;
use Luma\AuroraDatabase\Model\Aurora;
use Luma\AuroraDatabase\Utils\Collection;

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

    #[Column('strPassword')]
    private string $password;

    #[Column('dtmCreated')]
    private \DateTimeInterface $created;

    /**
     * @var Collection<Article>
     */
    #[AuroraCollection(class: Article::class, property: 'author')]
    private Collection $articles;

    #[AuroraCollection(
        class: Role::class,
        pivotSchema: 'Security',
        pivotTable: 'tblUserRole',
        pivotColumn: 'intRoleId'
    )]
    private Collection $roles;

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

    /**
     * @return Collection<Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }
}