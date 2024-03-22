# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres 
to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] - 2024-03-22
### Added
- Added additional argument to `AuroraMapper::map` to take an instance of the class to map into.

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