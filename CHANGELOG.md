# Changelog

## Unreleased

- a PHPDoc stub for `XF\Db\Schema\AbstractDdl`: `addKey()`, `addUniqueKey()` and `addFullTextKey()`
  accept a string index name

## 0.1.0 (2026-10-21)

- `extension.neon`: the configuration an add-on includes to be analysed against the forum it
  sits in - the forum scanned rather than analysed, other add-ons scanned without their vendored
  dependencies, the forum root defaulting to four directories above the add-on
- `crates.neon`: opt-in object-crate treatment for entities, options and route parameters
- `xenforo-phpstan generate`: the legacy class names the forum aliases, the `XFCP_` parent of
  every class extension in the install, and the map the extensions read, written per forum
- a methods reflection extension for methods class extensions add to core classes, covering
  every add-on installed in the forum
- return type extensions for the container shortcuts given the short `Vendor\AddOn:Name`
  identifier, and the `class-string` argument report they would otherwise raise at level 5
- a stale generated set is refused by XenForo version, naming the command to run
