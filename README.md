# Luma | Database Component

<div>
<!-- Version Badge -->
<img src="https://img.shields.io/badge/Version-0.1.0-blue" alt="Version 0.1.0">
<!-- PHP Coverage Badge -->
<img src="https://img.shields.io/badge/PHP Coverage-98.14%25-green" alt="PHP Coverage 98.14%">
<!-- License Badge -->
<img src="https://img.shields.io/badge/License-GPL--3.0--or--later-34ad9b" alt="License GPL--3.0--or--later">
</div>

*README is a work in progress/dev notes*

## Installation

--

## Usage

In your applications entrypoint, connect to your database:

```php
use Luma\DatabaseComponent\DatabaseConnection;
use Luma\DatabaseComponent\Model\Aurora;
 
$connection = new DatabaseConnection(
    'mysql:host=localhost;port=3306;',
    'username',
    'password'
);
Aurora::setDatabaseConnection($connection);
```

This allows you to use `Aurora` models to interact with your database.

### The `Aurora` Model

#### The Basics - Setup

An `Aurora` model is an entity that is specifically designed to interact with a corresponding database table. One instance
of an `Aurora` model relates to one row in its corresponding database table.

Take the following tables:

```mysql
CREATE TABLE User (
    intUserId int(11) auto_increment not null primary key,
    strUsername varchar(60) not null
);

CREATE TABLE Article (
    intArticleId int(11) auto_increment not null primary key,
    strTitle varchar(255) not null,
    intAuthorId int(11) not null,
    foreign key (intAuthorId) references User(intUserId)
);
```

We can create the following `Aurora` models which will allow us to create, retrieve and update records within these tables.

```php
use Luma\DatabaseComponent\Attributes\Column;
use Luma\DatabaseComponent\Attributes\Identifier;
use Luma\DatabaseComponent\Attributes\Schema;
use Luma\DatabaseComponent\Model\Aurora;

class User extends Aurora
{
    #[Identifier]
    #[Column('intUserId')]
    protected int $id;

    #[Column('strUsername')]
    private string $username;

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }
}
```

```php
use Luma\DatabaseComponent\Attributes\Column;
use Luma\DatabaseComponent\Attributes\Identifier;
use Luma\DatabaseComponent\Attributes\Schema;
use Luma\DatabaseComponent\Model\Aurora;

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
```

All `Aurora` models have a `getId()` method which returns the value of the property with the `#[Identifier]` attribute. 
**Identifiers must be `protected` and are required as part of a valid `Aurora` model.**

### Working with `Aurora` models

Let's add some new records to the tables we created:

```php
// Create a new user. Not yet saved to the database.
$user = User::make([
    'username' => 'Dan',
]);

// Save to the database.
$user->save();

// Create a new article and save to the database.
Article::make([
    'title' => 'Hello, Aurora!',
    'author' => $user,
])->save();
```

And we can retrieve them:

```php
$user = User::find(1);

$article = Article::getLatest();
```
