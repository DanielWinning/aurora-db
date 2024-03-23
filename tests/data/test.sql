DROP DATABASE IF EXISTS DatabaseComponentTest;

CREATE DATABASE DatabaseComponentTest;

USE DatabaseComponentTest;

DROP TABLE IF EXISTS AuroraExtension, User, Article, AddressDetails;

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
    strEmailAddress varchar(255) not null,
    strPassword varchar(255) not null,
    dtmCreated datetime not null default now(),
    unique key (strUsername),
    unique key (strEmailAddress)
);

INSERT INTO User
    (strUsername, strEmailAddress, strPassword)
VALUES
    ('Danny', 'danny@test.com', '');

CREATE TABLE Article (
    intArticleId int primary key auto_increment not null,
    strTitle varchar(255) not null,
    intAuthorId int not null,
    dtmCreated datetime not null default now(),
    foreign key (intAuthorId) references User(intUserId)
);

INSERT INTO Article
    (strTitle, intAuthorId)
VALUES
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1),
    ('Test Article', 1);

CREATE TABLE AddressDetails (
    intAddressDetailsId int auto_increment not null primary key,
    strAddressLineOne varchar(255) not null,
    strAddressLineTwo varchar(255),
    strCity varchar(255) not null,
    strPostcode varchar(255) not null,
    intUserId int not null,
    foreign key (intUserId) references User(intUserId)
);

INSERT INTO AddressDetails
    (strAddressLineOne, strAddressLineTwo, strCity, strPostcode, intUserId)
VALUES
    ('1 Main Street', null, 'London', 'E1 1AB', 1);