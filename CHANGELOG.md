# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added

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
