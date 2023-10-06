# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.0.0] - tbd

### Added

- lorem

### Changed

- `push_tags` is now a boolean
- Rebuilt using Symfony Console.
- configuration key `info_file` is now `version_file`.
- hooks, all failures will now stop the build. That is `hook_exception` and `build_fail_exception` now have the same result. They should not be used. Now simple use `exit 1`, etc. PHP hooks should not throw exceptions anymore, but just `exit(1)`, etc.

### Deprecated

- lorem

### Removed

- Project info other than version is no longer handled by this project.
- The "update" route was removed.
- The "info" route was removed.
- The "test" route was removed.
- Removed configuration options `major_step, minor_step, patch_step`

### Fixed

- lorem

### Security

- lorem

## [3.2.0] - 2021-10-14

### Added

- Some route aliases
- Configurable release commit message when calling 'done'; see `release_commit_message`. Giving this configuration a value will cause a commit message to be added automatically at the start of the `done` command; **any changed files are automatically added to the commit.** It allows the following tokens: PREVIOUS, VERSION.
- Configurable hotfix commit message when calling 'hotfix'; see `hotfix_commit_message`. Giving this configuration a value will cause a commit message to be added automatically as soon as the version string changes. It allows the following tokens: PREVIOUS, VERSION. This supercedes the use of `wp_do_version_commit`.

### Changed

- The default branchname from `master` to `main.

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
