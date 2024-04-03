DROP DATABASE IF EXISTS DatabaseComponentTest;
DROP DATABASE IF EXISTS Security;

CREATE DATABASE DatabaseComponentTest;
CREATE DATABASE Security;

USE DatabaseComponentTest;

DROP TABLE IF EXISTS AuroraExtension, User, Article, AddressDetails;

CREATE TABLE AuroraExtension (
    id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL
);

INSERT INTO AuroraExtension
    (name)
VALUES
    ('Extension One'),
    ('Extension Two'),
    ('Extension Three');

CREATE TABLE User (
    intUserId INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL,
    strUsername VARCHAR(60) NOT NULL,
    strEmailAddress VARCHAR(255) NOT NULL,
    strPassword VARCHAR(255) NOT NULL,
    dtmCreated DATETIME NOT NULL DEFAULT now(),
    UNIQUE KEY (strUsername),
    UNIQUE KEY (strEmailAddress)
);

INSERT INTO User
    (strUsername, strEmailAddress, strPassword)
VALUES
    ('Danny', 'danny@test.com', '');

CREATE TABLE Article (
    intArticleId INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    strTitle varchar(255) NOT NULL,
    intAuthorId INT(11) UNSIGNED NOT NULL ,
    dtmCreated DATETIME NOT NULL DEFAULT now(),
    FOREIGN KEY (intAuthorId) REFERENCES User(intUserId)
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
    intAddressDetailsId INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    strAddressLineOne VARCHAR(255) NOT NULL ,
    strAddressLineTwo VARCHAR(255),
    strCity VARCHAR(255) NOT NULL ,
    strPostcode VARCHAR(255) NOT NULL ,
    intUserId INT(11) unsigned NOT NULL ,
    FOREIGN KEY (intUserId) REFERENCES User(intUserId)
);

INSERT INTO AddressDetails
    (strAddressLineOne, strAddressLineTwo, strCity, strPostcode, intUserId)
VALUES
    ('1 Main Street', NULL, 'London', 'E1 1AB', 1);

USE Security;

CREATE TABLE tblRole (
    intRoleId INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    strRoleName VARCHAR(110) NOT NULL UNIQUE,
    strRoleHandle VARCHAR(110) NOT NULL UNIQUE
);

CREATE TABLE tblPermission (
    intPermissionId INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    strPermissionName VARCHAR(110) NOT NULL UNIQUE,
    strPermissionHandle VARCHAR(110) NOT NULL UNIQUE
);

CREATE TABLE tblPermissionRole (
    intPermissionId INT(11) UNSIGNED NOT NULL,
    intRoleId INT(11) UNSIGNED NOT NULL,
    FOREIGN KEY (intPermissionId) REFERENCES tblPermission(intPermissionId),
    FOREIGN KEY (intRoleId) REFERENCES tblRole(intRoleId)
);

CREATE TABLE tblUserRole (
    intUserId INT(11) UNSIGNED NOT NULL,
    intRoleId INT(11) UNSIGNED NOT NULL,
    FOREIGN KEY (intUserId) REFERENCES DatabaseComponentTest.user(intUserId),
    FOREIGN KEY (intRoleId) REFERENCES tblRole(intRoleId)
);