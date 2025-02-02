# CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres 
to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.8.2] - 2025-02-02
### Added
- N/A

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- Fix issue with retrieving `null` date/datetime columns

### Security
- N/A

---

## [2.8.1] - 2025-01-27
### Added
- N/A

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- Release version issue.

### Security
- N/A

---

## [2.8.0] - 2025-01-27
### Added
- N/A

### Changed
- Changed `with` method to accept associative array of relations and sub-relations

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- N/A

---

## [2.7.3] - 2024-08-27
### Added
- N/A

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- Fix to remove typo

### Security
- N/A

---

## [2.7.2] - 2024-08-27
### Added
- N/A

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- Fix to always convert booleans to integers when inserting into the database

### Security
- N/A

---

## [2.7.1] - 2024-08-24
### Added
- N/A

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- Fix for `Collection::remove` method to properly remove `Aurora` models
- Fix for `Aurora::update` method to ensure deleted relations are properly deleted from the database

### Security
- N/A

---

## [2.7.0] - 2024-08-23
### Added
- Implemented new `isEmpty` method on `Collection`

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- N/A

---

## [2.6.13] - 2024-07-15
### Added
- N/A

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- Fix to update the `.env` path used by the `Populator`

### Security
- N/A

---

## [2.6.12] - 2024-05-01
### Added
- N/A

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- Fix for fetching associations when the column is `null`

### Security
- N/A

---

## [2.6.11] - 2024-04-20
### Added
- N/A

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- Fix incorrect config path when included in Luma

### Security
- N/A

---

## [2.6.10] - 2024-04-20
### Added
- N/A

### Changed
- Updated panel SVG

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- N/A

---

## [2.6.9] - 2024-04-20
### Added
- N/A

### Changed
- Increase panel heading margin

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- Additional unit tests to cover QueryPanel

---

## [2.6.8] - 2024-04-20
### Added
- N/A

### Changed
- Add margin bottom to panel heading
- Update text colour when no queries are ran to be more readable

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- Fixed error in calculating total execution time

### Security
- N/A

---

## [2.6.7] - 2024-04-20
### Added
- N/A

### Changed
- Added total number of queries and total execution time to debug bar
- Added display when no queries were ran on the request

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- N/A

---

## [2.6.6] - 2024-04-20
### Added
- N/A

### Changed
- Added title to database queries panel
- Improved parameter output HTML/styles

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- N/A

---

## [2.6.5] - 2024-04-20
### Added
- N/A

### Changed
- Updated query panel styles
- Display query time in milliseconds
- Display parameters in a more readable format

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- N/A

---

## [2.6.4] - 2024-04-20
### Added
- N/A

### Changed
- Updated query panel styles

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- N/A

---

## [2.6.3] - 2024-04-19
### Added
- N/A

### Changed
- Updated HTML for query panel

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- N/A

---

## [2.6.2] - 2024-04-19
### Added
- N/A

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- Fix for query panel not being displayed; now can call `Aurora::createQueryPanel()` after enabling the debugger

### Security
- N/A

---

## [2.6.1] - 2024-04-19
### Added
- Added custom query panel to debug bar

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- N/A

---

## [2.6.0] - 2024-04-19
### Added
- Added `Tracy\Debugger` to support query logging

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- N/A

---

## [2.5.0] - 2024-04-18
### Added
- Added `Populator` class to ease initial data population

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- N/A

---

## [2.4.0] - 2024-04-03
### Added
- Added support for `ManyToMany` relationships - relates to issue [#3](https://github.com/DanielWinning/aurora-db/issues/3)

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- N/A

---

## [2.3.0] - 2024-03-24
### Added
- Added `find` method to `Collection` helper to allow searching based on provided callback.
- Added `toArray` method to `Collection` to allow retrieving all items as an array.

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- N/A

---

## [2.2.0] - 2024-03-24
### Added
- Added new `Collection` class
- Added new `AuroraCollection` attribute
- Added basic handling for OneToMany by way of the `AuroraCollection` attribute and appropriate property setup
- Can now call the `->with([AssociatedClass::class])` method to fetch associated models

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- N/A

---

## [2.1.0] - 2024-03-22
### Added
- Added additional argument to `AuroraMapper::map` to take the FQCN of the class to map into.

### Changed
- Renamed `Aurora::make` -> `Aurora::create`
- Changed `AuroraMapper::map` first argument type from `Aurora` to `array`
- Update `Aurora::executeQuery` method to fetch records as an associative array

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- Fixes issue for PHP version 8.2 or greater to stop creating dynamic class properties during model mapping due to 
deprecation ([#1](https://github.com/DanielWinning/aurora-db/issues/1))

### Security
- N/A

---

## [2.0.0] - 2024-03-18
### Added
- N/A

### Changed
- Renamed `Aurora::make` -> `Aurora::create`

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- Added additional unit tests

---

## [1.0.0] - 2024-03-16
### Added
- Initial release of Aurora DB.
- Implemented the `Aurora` model for interacting with database tables.
- Added database mapping functionality to map database rows to instances of `Aurora`.
- Added support for CRUD operations.
- Added support for pagination.
- Implemented a query builder for more complex database interactions.

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- N/A