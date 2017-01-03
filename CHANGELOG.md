# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added

### Changed

### Removed

## [1.4.1] 2016-12-05
### Changed
- CouchDB plugin fix

## [1.4.0] 2016-12-05
### Added

### Changed
- Fix stderr and stdout ordering in the docker-compose logs output
- Migrated from php 5 to php 7
- Updated all composer dependencies

### Removed

## [1.3.0] 2016-10-31
### Added
- Add config.json mount from the host to rumi container to rumi and rumi.bat binary
- Set git.autocrlf to false (#49)
- Return code 4 in case rumi has no access to clone repository (#50)

### Changed
- Add pull of the trivago/rumi:stable image before execution in the rumi and rumi.bat binaries

### Removed

## [1.2.0] 2016-08-25
### Added
- Add timeout setting per job
- Fix timeout handling

### Changed

### Removed

## [1.1.0] 2016-08-21
### Added
- Add volume validation
- Change log to repository.
- Shell and Batch scripts for local execution.
- Fix tear down - remove also anonymous volumes
- Add local tests execution
- Validate process timeout on runtime
- Add random delay on a job startup
- travis-ci execution (#36)
- Fail on any command (#38)
- Remove anonymous volumes on tear down (#33)
- Add new option to override the config path to RunCommand (#37)
- Block using ~ in volume specification (#41)
- Pass checkout information into the test container (#42)

### Changed
- Directory where tests reside.
- PHPUnit configuration file extension to use `.dist` as suffix.
- Formatting of some meta files to comply with conventions.
- `entrypoint` files were moved to `bin` directory and renamed.
- Handle notice on ports unset
- Use IIFE for application invocation

### Removed
- Special `bin` directory configuration from composer.
- Remove obsolete metrics code

## [1.0.0] - 2016-06-08
- Initial Release

[Unreleased]: https://github.com/trivago/rumi/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/trivago/rumi/compare/6b56539df6c9975dc28249c5959e33388451dd72...v1.0.0
