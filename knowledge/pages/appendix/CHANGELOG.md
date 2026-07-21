<!--
id: changelog
tags: ''
-->

# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.3.0] - 2026-07-21

### Changed

- `push_tags` now accepts a boolean value.
- Rebuilt the CLI with Symfony Console.
- Renamed the `info_file` configuration key to `version_file`.
- Hook failures now stop the build. `hook_exception` and `build_fail_exception` no longer have distinct behavior and should be replaced with a nonzero exit status, such as `exit 1`. PHP hooks should use `exit(1)` rather than throw exceptions.

### Removed

- Support for project metadata other than the version number.
- The `update`, `info`, and `test` routes.
- The `web_package.sh` launcher; use `web-package` instead.
- The `major_step`, `minor_step`, and `patch_step` configuration options.

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
