CREATE DATABASE DatabaseComponentTest;

DROP TABLE IF EXISTS DatabaseComponentTest.AuroraExtension;

USE DatabaseComponentTest;

DROP TABLE IF EXISTS AuroraExtension, User, Article;

CREATE TABLE AuroraExtension (
    id int primary key auto_increment not null,
    name varchar(255) not null
);

INSERT INTO AuroraExtension
    (name)
VALUES
    ('Extension One'),
    ('Extension Two'),
    ('Extension Three');

CREATE TABLE User (
    intUserId int primary key auto_increment not null,
    strUsername varchar(60) not null,
    strEmailAddress varchar(255) not null
);

INSERT INTO User
    (strUsername, strEmailAddress)
VALUES
    ('Danny', 'danny@test.com');

CREATE TABLE Article (
    intArticleId int primary key auto_increment not null,
    strTitle varchar(255) not null,
    intAuthorId int not null,
    foreign key (intAuthorId) references User(intUserId)
);

INSERT INTO Article
    (strTitle, intAuthorId)
VALUES
    ('Test Article', 1);
