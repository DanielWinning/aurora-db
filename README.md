# Luma | Aurora DB

<div>
<!-- Version Badge -->
<img src="https://img.shields.io/badge/Version-2.8.3-blue" alt="Version 2.8.3">
<!-- PHP Coverage Badge -->
<img src="https://img.shields.io/badge/PHP Coverage-89.67%25-yellow" alt="PHP Coverage 89.67%">
<!-- License Badge -->
<img src="https://img.shields.io/badge/License-GPL--3.0--or--later-34ad9b" alt="License GPL--3.0--or--later">
</div>

The *small but mighty* PHP database component.

- [Installation](#installation)
- [Usage](#usage)
    - [The `Aurora` Model](#the-aurora-model)
        - [Create Database Tables](#create-database-tables)
        - [Create `Aurora` Classes](#create-aurora-classes)
          - [Nullable Columns](#nullable-columns)
          - [OneToMany Relationships](#onetomany-relationships)
          - [Table & Schema](#table--schema)
        - [CRUD Methods](#crud-methods)
        - [Pagination](#pagination)
    - [Query Builder](#query-builder)

## Installation

```shell
composer require lumax/aurora-db
```

## Usage

In your applications entrypoint, connect to your database:

```php
use Luma\AuroraDatabase\DatabaseConnection;
use Luma\AuroraDatabase\Model\Aurora;
 
$databaseConnection = new DatabaseConnection(
    'mysql:host=localhost;port=3306;',
    'username',
    'password'
);
```

In its simplest form, we can use the above `DatabaseConnection` instance to get the `PDO` database connection:

```php
$pdo = $databaseConnection->getConnection();
```

This allows you to use `Aurora` models to interact with your database via a single shared connection.

### The `Aurora` Model

#### Create Database Tables

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

#### Create `Aurora` Classes

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

##### Nullable Columns
If your database table contains any nullable columns it is important to ensure that the associated property has been 
type hinted as nullable:

```php
#[Column('myNullableColumn')]
private ?string $nullableColumn;
```

##### OneToMany Relationships
For handling one-to-many relationships, for example fetching all `Article` models created by a `User`, we can add a property
to hold our articles (on the `User` model):

```php
#[AuroraCollection(class: Article::class, property: 'author')]
private Collection $articles;

/**
 * @return Collection<Article>
 */
public function getArticles(): Collection
{
    return $this->articles;
}
```

It's as simple as that, now calling `getArticles()` from a `User` instance will return a `Collection` containing all the 
articles written by that user.

##### Table & Schema
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

This would create/run queries that look something like this `SELECT * FROM Core.User ...`.
### CRUD Methods

Let's add some new records to the tables we created:

```php
// Create a new user. Not yet saved to the database.
$user = User::create([
    'username' => 'Dan',
]);

// Save to the database.
$user->save();

// Create a new article and save to the database.
Article::create([
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

### Pagination

Aurora also allows you to paginate your data:

```php
$articles = Article::paginate($page = 1, $perPage = 10, $orderBy = null, $orderDirection = null);
```

## Query Builder

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

```php
// Creating some new records in a blank table (with added email address column)
User::create('User One', 'user1@test.com')->save();
User::create('User Two', 'user2@test.com')->save();
User::create('User Three', 'user3@test.com')->save();

// Return all users as an array
$user = User::select()->get(); 

// Specify columns
$users = User::select('email')->get();

$users[0]->getEmailAddress(); // user2@test.com
$users[0]->getId(); // 2 (we always populate the primary key)
$users[0]->getUsername(); // fails

// Add a where clause
$user = User::select()->whereIs('username', 'User Three')->get();

$user->getId(); // 3

// We can chain whereIs (and other WHERE methods), which automatically converts them to an AND
// Returns NULL as no rows match this query
$user = User::select()->whereIs('username', 'User Three')->whereIs('id', 2)->get();

// We can specify not conditions
$users = User::select()->whereNot('username', 'User Three')->get();

count($users); // 2

// We can perform whereIn/whereNotIn queries
$user = User::select()->whereNotIn('id', [1, 2])->get();

$user->getId(); // 3

$users = User::select('username')->whereIn('id', [1, 3])->get();

count($users); // 2
```