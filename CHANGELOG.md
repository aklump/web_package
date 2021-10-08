# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.1.0] - 2021-10-07

### Added

- Configuration to enable/disable a single commit for version string change.
- Small improvements to UI and stability.

### Changed

- Single commits with the version string change will no longer be made by default. To keep this legacy behavior you must add `do_version_commit = true` to your configuration file per project _my_project/.web_package/config_ or globally at _~/.web_package/config_.
- Default `create_tags` is now `patch`; formerly `minor`.
- Default `init_version` is now `0.0.1`.

### Removed

- Automatic single commit of version string change.
