# CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres 
to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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