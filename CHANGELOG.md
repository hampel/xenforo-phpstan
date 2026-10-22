# Changelog

## Unreleased

- within the add-on that extends a class, a shortcut given `::class` returns that extension, as
  it already did for the short identifier; 0.2.0 left a class-name argument to XenForo's own
  annotation, which names the core class and loses an overridden signature

## 0.2.1 (2026-10-22)

- the level-6 template-type ignore also matches the message PHPStan 2.1.22 writes for a static
  call, `in call to method static method XF::...`, which the pattern released in 0.2.0 missed

## 0.2.0 (2026-10-22)

- within the add-on that extends a class, a container shortcut returns that extension rather than
  the core class, so a method the extension overrides with a different signature is called
  correctly; `xenforo-phpstan generate` records the add-on each extension belongs to, and a file
  it wrote before this release is refused with the command to run
- the shortcut argument is no longer reported as an unresolved template type at level 6
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
