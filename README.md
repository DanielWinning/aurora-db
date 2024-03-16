# Luma | Database Component

<div>
<!-- Version Badge -->
<img src="https://img.shields.io/badge/Version-1.0.0-blue" alt="Version 1.0.0">
<!-- PHP Coverage Badge -->
<img src="https://img.shields.io/badge/PHP Coverage-98.91%25-green" alt="PHP Coverage 98.91%">
<!-- License Badge -->
<img src="https://img.shields.io/badge/License-GPL--3.0--or--later-34ad9b" alt="License GPL--3.0--or--later">
</div>

*README is a work in progress/dev notes*

## Installation

--

## Usage

In your applications entrypoint, connect to your database:

```php
use Luma\AuroraDatabase\DatabaseConnection;
use Luma\AuroraDatabase\Model\Aurora;
 
$connection = new DatabaseConnection(
    'mysql:host=localhost;port=3306;',
    'username',
    'password'
);
Aurora::setDatabaseConnection($connection);
```

This allows you to use `Aurora` models to interact with your database via a single shared connection.

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

We can create the following `Aurora` models which will allow us to create, retrieve, update and delete records within these tables.

```php
use Luma\AuroraDatabase\Attributes\Column;
use Luma\AuroraDatabase\Attributes\Identifier;
use Luma\AuroraDatabase\Attributes\Schema;
use Luma\AuroraDatabase\Model\Aurora;

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
use Luma\AuroraDatabase\Attributes\Column;
use Luma\AuroraDatabase\Attributes\Identifier;
use Luma\AuroraDatabase\Attributes\Schema;
use Luma\AuroraDatabase\Model\Aurora;

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
    * @return string
    */
    public function setTitle(): string
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
}
```

All `Aurora` models have a `getId()` method which returns the value of the property with the `#[Identifier]` attribute. 
**Identifiers must be `protected` and are required as part of a valid `Aurora` model.** All other properties which you
also wish to map to a database column should use the `#[Column($name)]` attribute.

By default, the table name associated with your `Aurora` class will be the same name as your class - so `User` and `Article` in this case.
In addition, no schema will be set for your class - output queries will look something like this `SELECT * FROM User ...`.

You may wish to specify a schema and table name:

```php
#[Schema('Core')]
#[Table('User')]
class User extends Aurora {
    ...
}
```

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

// Find by takes the PROPERTY name (not column name, unless they're the same of course)
$user = User::findBy('username', 'Dan');

$article = Article::getLatest();

$articles = Article::all();

$userCount = User::count();
```

The `save()` method can also be used to update existing records:

```php
$article = Article::find(1);

$article->setTitle('My Updated Article Title');

$article->save();
```

And we can also delete existing records:

```php
$article = Article::find(1);

$article->delete();
```

#### Pagination

Aurora also allows you to paginate your data:

```php
$articles = Article::paginate($page = 1, $perPage = 10, $orderBy = null, $orderDirection = null);
```

### Query Builder

The `Aurora` model includes a handy query builder. Some important notes:

> **In order to execute any query builder statement and retrieve the results, you must call the `get` method.**
> 
> **All query builder results are returned with the specified columns as well as their primary identifier, so this does not need to be specified.**
> 
> **There are 3 possible return types following a get() call:**
> - When **one** result is returned, this returns as an instance of the calling class.
> - When **more than one** result is returned, this returns an array containing instances of the calling class.
> - When **zero** results are returned, NULL is returned by the query builder.




Here are some of the methods that you can use:

#### `select`
> select(array $columns = ['*']): static

The select method adds a `SELECT` statement to `Aurora`'s internal query string.

```php
$users = User::select();

// Execute the query. Calling select()->get() with no arguments is effectively the same as calling User::all()
$users = $users->get();
```

We can specify the individual columns we wish to fetch - you can provide either column or property names:

```php
$articles = Article::select('title')->get();

echo $articles[0]->getTitle(); // Hello, Aurora!
echo $articles[0]->getId(); // 1
echo $articles[0]->getAuthor(); // Will fail, author column not fetched
```

*`whereIs`*

> `whereIs(string $column, string|int $value): static`

Adds a `WHERE` clause to the internal query string. Can be chained, in which case it will convert the `WHERE` to 
and `AND`.

```php
$user = User::select()->whereIs('name', 'Dan')->get();

echo $user->getId(); // 1
```