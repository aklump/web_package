<!--
id: changelog
tags: ''
-->

# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Build numbers. Set `build_number: true` in your configuration and `bump build` will stamp the version with the number of commits since the newest release tag, as SemVer build metadata, e.g. `3.4.0+12`, before any hook runs. Commits at the tip that changed only the version file are not counted. `bump done` still tags the bare version. See the configuration page for details.

### Changed

- **Breaking:** The `ROOT_PATH`, `WEB_PACKAGE_ROOT` and `INSTALL_DIR` PHP constants no longer exist. PHP hooks, and any code in `.web_package/src`, that read them will fail. Use the hook arguments instead: `$argv[7]` for the project root and `$argv[12]` for the web_package root. Shell hooks can use the `$ROOT` and `$WEB_PACKAGE_ROOT` environment variables.

### Fixed

- The global config at `~/.web_package/config` was never loaded. It now serves as the base, and the project config overrides it. If you have a global config you forgot about, its values will now apply.
- `bump patch`, `minor` and `major` crashed with "$this->output has not been set" outside an initialized project; they now print the "not initialized" message.
- Support for Symfony Console 7.4 and 8, and Symfony Config 8, which were allowed by `composer.json` but did not work.
- `bump init` on PHP 7.3 with `jawira/case-converter` 2.3.
- A PHP 8.1+ deprecation when running outside a git repository.
- A PHP 8.4 deprecation for an implicitly nullable parameter.
- Version files with SemVer build metadata (e.g. `1.2.3+4`) now work with every version file type. A Drupal `.info` file was corrupted on write (`1.2.4+5+4`), and a Symfony Console entry script was not recognized as one.

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
