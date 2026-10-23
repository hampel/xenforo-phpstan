# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this package is

`hampel/xenforo-phpstan` — a PHPStan extension for add-ons developed inside a XenForo 2 install.
It carries what is a property of XenForo and identical for every add-on: the configuration that
lets PHPStan scan the forum without analysing it, the legacy class names XenForo 2.3 aliases at
runtime, the `XFCP_` parent of every class extension, the methods those extensions add to core
classes, and the container shortcuts given the short `Vendor\AddOn:Name` identifier. `README.md`
is the consumer's view; this file is the maintainer's.

## Commands

```bash
composer install
composer check                                  # lint, analyse, test - what CI runs
composer test                                   # phpunit, including the end-to-end run
composer analyse                                # phpstan on this package, level 10
composer format                                 # pint, PSR-12
vendor/bin/phpunit --filter AnalysisTest        # the end-to-end run alone
bin/xenforo-phpstan generate --root=tests/Fixtures/forum --out=/tmp/x   # the generator by hand
bin/xenforo-phpstan generate --check --root=tests/Fixtures/forum --out=/tmp/x   # is it current
```

## Nothing here needs XenForo, and that is the design constraint

**XenForo's source is licensed and not on Packagist, so CI can never have a forum.** Everything
is tested against the stand-in under `tests/Fixtures/forum`: a `src/XF.php` with the four
members the generator uses — `start()`, `$versionId`, `getAliasForClass()`, `getClassForAlias()`
— a dozen XenForo-shaped classes, and two add-ons. `AnalysisTest` generates against it, writes
the `phpstan.neon` an add-on would, runs the real PHPStan from `vendor/bin`, and asserts that
exactly the deliberately wrong calls in the fixture controller are reported, each against the
type the package gave it. **That test is
the specification**: every kind of call the package exists to type is in that controller, and a
new capability gets a line there before it gets code.

The fixture's `XF.php` is also what lets this package's own PHPStan run see the `\XF` symbol
the generator calls — `phpstan.neon.dist` and `.github/phpstan-nodev.neon` both scan it, and
`.github/composer-require-checker.json` whitelists `XF` for the same reason.

**A real forum is still the only check that the numbers are right.** The alias mapping, the
suffix table, the shortcut formatters and the hint naming rule were all read from XenForo 2.3.13
and verified against real add-ons on the day they were written; when XenForo changes any of
them, the fixture will keep passing. Re-verify against a forum when touching the generator or
`Shortcut::classFor()`.

## Structure

```text
extension.neon          the fragment an add-on includes: parameters, schema, services
crates.neon             opt-in universalObjectCratesClasses, with its cost stated
stubs/                  PHPDoc stubs: return types XenForo leaves undeclared, and two corrections
bin/xenforo-phpstan     the generator's entry point
src/
  Classes.php           loads the generated map, lazily; refuses a stale one by versionId
  Generator/            Generator (boots XF, walks the forum, writes three files), Command (argv),
                        ClassExtension (one XML line), Stub (grouped declarations), Summary
  Reflection/           ClassExtensionMethodsExtension - methods added by class extensions;
                        AddOnPropertiesExtension - the keys XF\AddOn\AddOn answers by magic
  Type/                 Shortcut (the identifier expansion), ShortcutTypes (the method table and
                        the type it yields), the instance and static return type extensions;
                        FilterTypes (filter string to type) and FilterReturnTypeExtension;
                        ActionReplyExtension (every undeclared action returns a reply);
                        RelationReturnTypeExtension (a relation by its @property-read)
tests/Fixtures/forum/   the stand-in XenForo install
```

## Decisions that look like style and are not

- **The generated files are per forum, under `internal_data/phpstan/` by default.** What they
  describe — which classes the forum aliases, which extensions every installed add-on registers
  — is a property of the install, and forty add-ons each regenerating their own copy after an
  upgrade is forty chances to forget. `xenforo.generated` overrides it.
- **The generator boots XenForo and asks it, rather than deriving the alias mapping from
  filenames.** A filename rule misses the six suffixes `getAliasableNamespaces()` abbreviates
  (`ControllerPlugin` → `Plugin`, `FindNew` → `Handler`) and invents names core never aliases.
  `\XF::start()` registers autoloaders and connects to nothing.
- **It walks `src/addons` as well as `src/XF`**, because XenForo aliases a renamed add-on class
  exactly as it aliases core; an add-on that has adopted the suffixed names depends on it.
- **The class shape is read from the file, not from reflection.** Loading every aliased class to
  ask `isAbstract()` would execute third-party add-on code in the generator process; a regex on
  the declaration line answers the same question. A trait or enum cannot be subclassed and is
  skipped; an interface is declared as an interface.
- **A legacy name is declared as a subclass of the renamed class.** PHPStan has no alias, and a
  subclass satisfies every call through the legacy name. The cost, recorded in the README: a
  value typed to the renamed class is not a legacy-named one. So `ShortcutTypes` returns the
  legacy name when PHPStan knows it, because that is a subtype of both and the add-on's own
  return types use it.
- **The extension map is keyed by the class XenForo resolves the registered name to**, because
  `Extension::extendClass()` normalises through `getClassForAlias()`; an extension registered on
  `XF\Repository\User` applies to `UserRepository`. The methods extension walks the receiver's
  parents, so a call through either name finds it.
- **A shortcut returns the analysing add-on's own extension of the class, and the core class
  everywhere else.** An extension that overrides a core method with a wider signature is callable
  that way only through the extension type, and a `MethodsClassReflectionExtension` is never
  asked about a method the core class already has, so no reflection trick reaches it. Returning
  the extension to every add-on would be wrong - another add-on's code never sees a type from
  this one - so ownership is by file: the generator records each extension's add-on directory,
  and `Classes::extensionOwnedBy()` matches the analysed file against it, tests included. A
  `::class` argument takes the same check, because XenForo's own annotation would otherwise name
  the core class and lose the override; everywhere else it is left to that annotation.
- **`ShortcutTypes::typeFor()` is a dependency of another package, so its signature is a
  contract.** `hampel/xenforo-test-framework` (from its 5.36.0) ships a return type extension
  for its `makeEntity()` and `createEntity()` helpers that injects the `ShortcutTypes` service as
  `extension.neon` registers it, passes `Shortcut::ENTITY`, and relies on
  `typeFor(string $kind, bool $nullable, Type $argument, string $file): ?Type` answering `null`
  to mean "leave PHPStan's own type standing". Changing any of those three is a minor here and a
  coordinated move there; say so to that package before tagging. It is a class, not an interface,
  by choice so far: one consumer, and the framework said it would take an interface the same way.
- **The methods extension offers only what the extension class itself declares.** A method the
  core class already has never reaches a `MethodsClassReflectionExtension`; one inherited back
  through `XFCP_` must not be offered, and checking the declaring class against the receiver's
  lineage is also what stops the lookup recursing.
- **The `class-string` argument report is ignored by pattern, not fixed by a stub.** A stub
  saying `class-string<T>|string` does not work: PHPStan simplifies the union to `string` and
  loses `T`, and it validates stubs against classes it cannot see. The pattern names the exact
  shortcut methods and a string argument, with `reportUnmatched: false` so an add-on that
  passes `::class` everywhere is not penalised for it.
- **The stubs carry return types, and parameters only as XenForo declares them.** A return type
  is a fact about the method body, read from 2.3.13; a parameter type is a claim about every
  caller, and the first draft of `core.stub` typed `redirect()`'s URL as a string, which
  reported an add-on passing a route parameter - a new error from a stub meant to remove them.
  Parameters are `mixed` unless XenForo's own docblock says otherwise, and are declared at all
  because PHPStan checks a stub at the consumer's level, where an untyped parameter is itself a
  report. A method whose result depends on data, such as a database fetch, is not stubbed.
  The one parameter typed beyond XenForo's declaration is `Tree::__construct()`'s `$data`,
  because a template binds nowhere else and the body iterates it on its first line, so nothing
  that runs is refused; its callbacks stay plain `Closure`, since typing their parameters would
  report every callback declared narrower than `int|string` for the id. The one parameter
  corrected against XenForo's declaration is `AbstractAdapter::query()`'s `$params`, documented
  `array` and passed as a scalar by core itself, which the documented type reported at level 5.
- **The add-on object's properties are typed one by one, not crated.** `XF\AddOn\AddOn` answers
  nine keys itself and then the installed entity's columns, and every add-on that reports its
  version reads them. A crate would silence a mistyped key with the rest; the extension offers
  exactly the keys the class answers, with the getters' types and the entity's `@property`
  lines. It builds the reflection with PHPStan's `AnnotationPropertyReflection`, which is not
  `@api`; its constructor is the same from 2.1.22 to the current release, and the lowest CI
  job is what notices if that changes.
- **A stub return that is wider than what callers read from it is a regression, not an
  improvement.** 0.7.1 declared `getRelationOrDefault()` as returning `Entity`; the reason to call
  it is to read a column of the relation, and `Entity` has none without crates, so every such
  read became `property.notFound` from level 2 where `mixed` had been silent. An adopter at
  level 9 went from clean to 23 the same day. The test before stubbing a return: what does the
  caller do with it next, and does the type carry that. Relations are now typed by name from the
  receiver's `@property-read`, which XenForo and extensions both declare, and an unknown name
  stays undeclared.
- **Every undeclared controller action returns `AbstractReply`, by extension rather than by
  stub.** `XF\Mvc\Dispatcher` throws on anything else, so it is a fact about every action at
  once, and one stub per core action an add-on happens to extend would never be complete. The
  extension answers for a method named `action*` whose return type is implicit `mixed`, on
  instance and static calls, the static form being what `parent::actionIndex()` is to PHPStan;
  a declared return type, native or docblock, is left as it is.
- **A filter result is typed from the filter string alone, because the filterer casts.**
  `XF\InputFilterer::filter()` returns the named type whatever the input was, so the result does
  not depend on data; `num` is `float|int` because the body does `strval($value) + 0`. The
  mapping was read from 2.3.13 and the names are XenForo's constants; an unknown name answers
  null rather than guessing, since the filterer throws on one.
- **A stale generated set is refused, not tolerated.** It fails as a flood of undefined-method
  errors naming renamed classes, which reads as the add-on being broken. `Classes` compares the
  recorded `versionId` with the forum's `src/XF.php` on first use and throws with the command to
  run; PHPStan surfaces that as one internal error with the message intact.
- **No console component, no dependencies beyond PHPStan.** The generator is one command with
  two options and a flag; a dependency here lands in every add-on's dev install.
- **`--check` reuses the loader's refusals rather than restating them.** `Classes::load()` is
  the test PHPStan applies on first use, and its messages already name both version ids and the
  command to run; `--check` constructs the loader against the set and prints what it throws. A
  second implementation of the staleness test would be a second place for it to drift.

## Traps met while building it

- **`"\XF"` in a double-quoted PHP string is a hex escape**, 0x0F. Test strings naming XenForo
  classes are single-quoted.
- **PHPStan writes advice to stderr** in some environments; the end-to-end test keeps stderr
  apart from the JSON it parses.
- **A service used by another service must be registered**, even with autowiring. `ShortcutTypes`
  is in `extension.neon` for that reason; omitting it fails only at container build, in the
  consumer's run.
- **A path written through a parameter the fragment defines itself is not absolutised** by
  PHPStan's loader — only `%rootDir%`, `%currentWorkingDirectory%` and `%env.*%` are known at
  load time. The default root therefore goes through `%currentWorkingDirectory%`, and
  `FileExcluder` normalises the `..` segments before matching.
- **An error message's wording differs across the PHPStan range, and the lowest corner is the
  only check.** PHPStan 2.1.22 reports an unresolved template type on a static call as
  `in call to method static method XF::repository()`; the current release says
  `in call to static method`. The ignore pattern in `extension.neon` accepts both, and the
  level-6 end-to-end test is what catches a regression, but only when run against 2.1.22: it
  passed locally on the current PHPStan and failed on the `--prefer-lowest` job on 2026-10-22.
  Before tagging, run the suite once on the lowest set, as the `lowest` CI job does.
- **A class may be declared in one stub file only**, so every correction to `AbstractDdl` is in
  `db-schema.stub` although `addColumn()` belongs with the return types. **A stub class with a
  docblock replaces the real class's docblock**, so a generic class restated in a stub repeats its
  `@template` and `@implements` lines in full or loses them; a stub class with no docblock
  keeps the real one, which is how `User` and `Node` keep XenForo's `@property` lines (measured
  on a forum on 2026-10-23: `$node->title` is a string either way). **A stub supplies docblocks
  and never members**: a method declared in a stub on a class that does not have it is not
  added, silently. A trait declared in a stub types its methods on every class that really uses
  it, parents and traits of a stub-declared class survive, and a probe that reports a method
  undefined is reporting the forum, not the stub. On 2026-10-23 an hour went on a "lost trait"
  that was a service which never used it; the first probe should have been the real class.
  **A stub needs bracketed namespaces** to declare a global class such as `XF` beside
  namespaced ones; PHP refuses to mix the forms.
- **A class declared in a stub keeps its real method docblocks, except on a method that
  implements an interface PHPStan stubs itself.** `getIterator()`, from `IteratorAggregate`, is
  resolved from PHPStan's stub of the interface instead, so 0.3.0 made every `foreach` over a
  collection yield `mixed` while `first()`, `filter()` and `fetchOne()` kept their types. Found by
  an adopter on 2026-10-22, the day of the release; the fixture now carries the real
  `getIterator()` declarations and a loop in `Typed.php`. Restate such a method in the stub.
- **PHPStan validates a stub against the classes in stubs and the autoloader, not the scanned
  forum.** A stub may name a class, as a parent or in a return type, only if that class is also
  in a stub: `Node` extends `Entity`, which the stub declares, because `AbstractCollection<Node>`
  has to satisfy the bound; `NodeRepository` names no parent, because the real `Repository` is
  unknown to the validator and naming it is reported as an unknown class in the consumer's run;
  `AddOn` is declared empty so that `Manager::getById()` may return it. The end-to-end test
  reports the violation as `core.stub:<line> ... has invalid return type`, which is how the
  `AddOn` case was found on 2026-10-23.
- **A new PHPStan symbol in `src/` fails the `Declared dependencies` job twice.** The
  require-checker cannot open the phar, so every PHPStan class the package touches is named in
  `.github/composer-require-checker.json`; and the dev-free PHPStan run has its own config, so an
  ignore added to `phpstan.neon.dist` is not in force there. The 0.6.0 tag went out red on that
  job on 2026-10-23 for both reasons, with the other four jobs green, after a local pre-tag check
  that ran `composer check` and the lowest suite but neither half of that job. Run both before
  tagging anything that adds a PHPStan import; the require-checker is a one-line scratch install.
- **An exclusion pattern needs a trailing `/*`.** PHPStan matches patterns per file with
  `fnmatch()`, so `*/vendor` matches the directory entry and nothing in it.

## Version support

PHP 8.3, 8.4 and 8.5, PHPStan `^2.1.22`. PHPStan runs at level 10 with `phpVersion` spanning the
range; CI tests the floor with `--prefer-lowest`, which is also what proves the extension loads
and the end-to-end test passes on the oldest PHPStan the constraint allows.

## Git

Commit freely; never push.
