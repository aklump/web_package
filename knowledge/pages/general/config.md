<!--
id: config
tags: ''
-->

# Configuration

* Located at _.web\_package/config.yml_ && _.web\_package/config.local.yml_

## version_file

* This path is relative to the initialized directory<sup>1</sup>.
* You may use the token `__DIR__` when creating templates.

## build_number

* Opt-in; the default is `false`. Set `build_number: true` to enable it.
* When enabled, every `bump build` stamps a build number onto the version before any hook runs, using [SemVer build metadata](https://semver.org/#spec-item-10): `3.4.0` becomes `3.4.0+12`. The stamped version is written to the `version_file`, and the build hooks receive it; see [Hooks](@hooks).
* Only `bump build` stamps. `dev`, `publish` and `unbuild` do not touch the version file.
* The build number is the count of commits from the newest release tag to `HEAD`, i.e. `git rev-list --count <tag>..HEAD`. With no release tag, every commit is counted.
  * A release tag is a tag that looks like a version, such as `3.4.0` or `v3.4.0`, and that has the same prefix as your version, such as `8.x-` in `8.x-1.2.0`. Other tags are ignored. When several qualify, the one created most recently wins, not the highest version.
  * Commits at the tip of the branch that changed only the version file are not counted. This is so that committing the stamp does not raise the number, and running `bump build` twice in a row gives the same number. A version-file-only commit that is followed by any other commit is counted like every other commit.
  * Uncommitted changes are never counted.
* `bump release`, `hotfix`, `patch`, `minor` and `major` write a bare version, without `+N`, as incrementing drops the build metadata.
* `bump done` tags the bare version (`3.4.0`, never `3.4.0+12`).
* Your project must be in a git repository, and `bump build` fails if the version file cannot be written.

```yaml
build_number: true
```

---
<sup>1</sup> The initialized directory is the one that has _.web_package_ in it.
