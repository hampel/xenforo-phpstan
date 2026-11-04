# Static analysis for XenForo add-ons with PHPStan

A tutorial on how to use static analysis to find bugs or potential problems with your addon code -
and how to set up `hampel/xenforo-phpstan`, an open-source composer package that helps a developer 
analyse XenForo addons. By the end you should have a clean run at level 2 on an add-on of your 
own and know what the next levels will ask of you.

## What static analysis is

**Static analysis reads your code without running it, and reports what cannot be right.** Unit
tests and feature tests will each run one path through your code with one set of inputs and checks 
one result. In contrast, an analyser reads _every_ path, with every value it can prove something 
about, and complains where the pieces do not fit: a method called on a value that might be null, 
a parameter given a string where an int is declared, a return that contradicts the docblock, a 
branch that can never run, a variable used before anything assigned it.

It does this from the types. PHP checks types at runtime, one call at a time, and tells you about
a mismatch by throwing when it is reached. An analyser checks them all at once, before anything is
reached, from the declarations and docblocks already in the code. That is why it finds the bug in
the branch your tests never visited and the error page your users saw once a month.

It is not a replacement for tests. A test proves a behaviour; an analyser proves an absence of a
class of mistakes. They find different things, and the second is much cheaper to add to an
existing add-on, because it needs no test cases written: it reads what is there.

## Why bother, for an add-on in particular

An add-on is a particularly good candidate, for reasons that are specific to how XenForo works.

- **Most of an add-on's code runs rarely.** Setup steps, upgrade steps, cron entries, jobs, admin
  controllers, the import handler, the branch for a user with no avatar. These are exactly the
  paths a test suite leaves out and a manual test never reaches, and exactly where an analyser
  earns its keep.
- **XenForo's API is large and mostly undocumented by type.** A method that returns `string|false`
  looks, in the editor, like one that returns a string. An analyser that knows the truth tells you
  at the call site, before a database column rejects the `false`.
- **XenForo changes underneath you.** 2.3 renamed hundreds of classes and added return types to
  methods you may have overridden. An analyser reads your add-on against the install it sits in
  and reports every override that no longer matches.
- **It is cheap to run and runs everywhere.** A few seconds per add-on, no database, no browser,
  no test fixtures. It runs in the editor, before a commit, and in CI.

Some of what it has found on our own add-ons, so that this is concrete rather than hopeful:

- An import loop written as `while ($line = trim(fgets($handle)))`, which ends at the first blank
  line in the file and silently drops everything after it. It had shipped. The analyser reported
  a blank-line check inside the loop as always false, and it was always false because the loop
  condition had already stopped on exactly the line the check was waiting for.
- `Request::getUserAgent()` returns `false` when the request sent no agent, and an add-on wrote it
  straight into a string column, so every registration from a client without a user agent failed.
  The analyser reported the assignment the day the return type was known.
- A wrong `@return bool` on one method, which accounted for fifteen of the thirty-five errors the
  add-on had at level 5. Every caller had been treating the result as a bool, and it was not one.
- A test that named XenForo's `User` class where the add-on's own extension of it is what comes
  back, so three calls in it had the wrong number of arguments. The test passed, because it never
  reached those lines with the arguments it would have needed.
- XFMG's `MediaItem::getThumbnailUrl()` returns null until a thumbnail exists, and an add-on had
  been rendering that null as an empty `src` attribute. Level 8 reports a nullable value at the
  place it is used, and that is where it appeared.

None of these were found by a test, and none were reported by a user in terms anyone could act on.

## What PHPStan does

[PHPStan](https://phpstan.org) is the most widely used static analyser for PHP. It is a Composer
package with one command, `vendor/bin/phpstan analyse`, which reads a configuration file,
`phpstan.neon`, walks the files it names, and prints a list of errors with a file, a line and a
message. It changes nothing.

**It works in levels, from 0 to 10, and each level adds a class of check.** Level 0 is the
basics: unknown classes, unknown functions, wrong argument counts. Level 2 adds unknown methods
and properties on every typed value. Level 5 checks the arguments you pass against the parameters
declared. Level 6 asks for a type on every parameter, property and return. Level 8 reports a call
on a value that might be null. Level 10, also written `max`, treats `mixed` as the unknown it is.
You choose the level in the configuration, start low, and raise it when the run is clean. A
higher level never finds fewer things; it finds more of them, and asks more of your declarations
in return.

PHPStan needs to see every class your code touches, but it analyses only the files you ask it to.
Those are different things, and the distinction matters for an add-on. It finds classes through
the Composer autoloader of the project it is installed in, and through any directories it is told
to **scan**, which means read for class names without reporting on them. An add-on's classes are
not in any Composer autoloader, and neither are XenForo's.

It is extensible. A PHPStan extension can tell it that a method exists where none is declared,
what a call returns when the declared type is too vague, and that a particular error is not one.
That is how frameworks with magic, Laravel and Doctrine among them, become analysable, and it is
what the package below is.

## Why a XenForo add-on needs help

Run PHPStan on an add-on with nothing but a path and a level, and the first run reports several
hundred errors on code that works. Every one of them is PHPStan being told less than the truth,
and they come from five places.

- **It cannot see XenForo.** Nothing autoloads `XF\Entity\User` from an add-on's directory, so
  every core class is unknown and every call on one is an error. The fix is to scan the forum's
  `src/XF`, `src/vendor` and `src/addons` without analysing them, which takes some care: the
  add-on's own `vendor/` must not be scanned twice, another add-on's release build must not be
  read while it is being written, and two files XenForo `require()`s rather than autoloads have
  to be named individually.
- **Legacy class names are runtime aliases.** XenForo 2.3 renamed its classes with type suffixes,
  `XF\Repository\User` became `XF\Repository\UserRepository`, and registered an autoloader that
  `class_alias()`es each old name to the new one when it is first used. PHPStan cannot follow a
  runtime alias. An add-on that extends or type-hints a legacy name, which is every add-on that
  supports 2.2, sees every inherited member as undefined.
- **Class extensions have a parent that does not exist.** `Vendor\AddOn\XF\Entity\User extends
  XFCP_User`, and `XFCP_User` is a class XenForo generates in memory when it builds the extension
  chain. XenForo writes an `_output/extension_hint.php` declaring these in development mode, but
  a packaged add-on ships without it, so every other add-on's extensions are unparseable from
  yours.
- **Class extensions add methods and columns to core classes.** When your `XF\Entity\User`
  extension adds `canUseThing()`, every `$user` in the forum can call it. Statically every one of
  those values is an `XF\Entity\User`, and the method is undefined on it. On a set of 44 add-ons
  this one mechanism was more than half of everything left after the configuration was right.
- **Container shortcuts hide the type.** `$this->repository('Vendor\AddOn:Thing')` returns your
  repository at runtime. XenForo 2.3 types the method as returning whatever class the argument
  names, and the short identifier is not a class name, so the call returns the base `Repository`
  and every method on your repository is undefined.

Beyond those five there is a sixth, which costs nothing until level 7 and everything after it:
**XenForo declares no return type on a great many of the methods every add-on calls.** `view()`,
`redirect()`, `error()`, `buildLink()`, `\XF::phrase()`, `filter()`, the finder's fluent methods,
the database adapter's fetch methods. From level 7 each result is `mixed`, and everything you do
with it is reported.

A single add-on could fix all of this in its own configuration, and the first few did, at the
cost of a few days each and a set of files that had to be kept current by hand. It is the same
work for every add-on, because every part of it is a fact about XenForo rather than about the
add-on. That is what the package carries.

## What the package does

[`hampel/xenforo-phpstan`](https://github.com/hampel/xenforo-phpstan) is a PHPStan extension, on
Packagist, for add-ons developed inside a XenForo 2 install. It has three parts.

**A configuration fragment** that your `phpstan.neon` includes. It scans the forum without
analysing it, with the exclusions above, and registers everything else here.

**A generator** that reads the forum once and writes three files into
`internal_data/phpstan/`: a stub declaring every legacy class name the forum aliases, as a
subclass of the renamed class; a stub declaring the `XFCP_` parent of every class extension of
every installed add-on; and a map of those extensions that the rest reads. It asks the forum
rather than guessing from filenames, by booting XenForo's autoloader and calling
`\XF::getAliasForClass()` for each class file, so it never invents a name XenForo does not alias
and never misses one of the ten suffixes XenForo abbreviates. It connects to nothing and executes
no add-on code.

**Reflection and type extensions** that answer the questions PHPStan cannot answer from the
source. The methods, columns and relations your class extensions add are offered on the core
class, with the types you declared. Every container shortcut is expanded as `\XF::stringToClass()`
expands it, on `XF`, `XF\App`, the controller, the entity manager, repositories, entities,
services and widgets, so `$this->repository('Vendor\AddOn:Thing')` is your repository. Inside the
add-on that extends a class, a shortcut returns the extension rather than the core class, which
is what comes back at runtime. Every controller action returns a reply. `filter('id', 'uint')` is
an int, `filter('name', 'str')` a string, and an array of filters is an array shape. A plucked
finder yields the column. A relation fetched by name is the entity the `@property-read` declares.

**And a set of stubs**, docblock-only files PHPStan reads alongside the real classes, that supply
the return types XenForo leaves undeclared, each read from what the method body on 2.3.13
returns. The list is long and in the README; it is what makes the higher levels reachable. Where
XenForo's own docblock contradicts XenForo's own code, the stub corrects it and says so: the
setup step methods a single-step setup falls out of without returning, `noPermission()` taking a
list, the collection filter's callback being judged on truthiness.

Three things it does not do, on purpose. It does not type a parameter beyond what XenForo
declares, because a parameter type is a claim about every caller and a wrong one reports working
code. It does not type a method whose result depends on data, such as a keyed `container()` read
or a database fetch. And it does not run any of your code or XenForo's during analysis.

## Setting it up

You need PHP 8.3 or later on the machine running the analysis, Composer, and an add-on that lives
in a XenForo 2.3 install, at `src/addons/<Vendor>/<AddOn>`. The add-on itself can support any
XenForo 2 version; the forum it is analysed against is a 2.3 one, because the alias mapping is
asked of the forum.

### Install and generate

In the add-on's directory:

```bash
composer require --dev hampel/xenforo-phpstan
vendor/bin/xenforo-phpstan generate
```

The first line installs PHPStan and the package into the add-on's own `vendor/`, which is where
PHPStan must run from. The second finds the forum four directories up, which is where it is from
an add-on's directory, and writes the three generated files into the forum's
`internal_data/phpstan/`. It prints what it wrote: on a 2.3.13 install with a handful of add-ons
that is around nine hundred legacy names and however many class extensions the install has.

The generated set belongs to the forum, not to the add-on, and every add-on on the forum shares
it. **Run `generate` again after upgrading XenForo, and after adding or removing a class
extension in any add-on.** A set left over from a previous XenForo version is refused rather than
tolerated: PHPStan stops with one error naming both versions and the command to run. A set that
predates a new class extension reports that extension's `XFCP_` parent as unknown, which is loud
enough. To ask whether the set is current without touching it:

```bash
vendor/bin/xenforo-phpstan generate --check
```

One thing about the version Composer records is worth knowing now, because it will puzzle you
later. The `composer require` line above writes something like this into the add-on's
`composer.json`:

```json
"require-dev": {
    "hampel/xenforo-phpstan": "^0.10"
}
```

The `^` is Composer's "compatible with" marker, and **for a package below version 1.0 it means
something narrower than it usually does.** On a package at 1.x, `^1.4` allows any release up to
but not including 2.0. On a package at 0.x, `^0.10` allows any release up to but not including
0.11, so `composer update` will bring in 0.10.6 and 0.10.7, and will not bring in 0.11.0. The
package is at 0.x, so the second rule applies.

That is how it is meant to work. A patch release, 0.10.5 to 0.10.6, only adds or corrects a type
and arrives on its own. A minor release, 0.10 to 0.11, is one that can change what PHPStan
reports on your add-on at the level you run, and you should take it when you have a few minutes
to read the changelog and look at the run, not in the middle of something else. To take it, name
the new minor:

```bash
composer require --dev hampel/xenforo-phpstan:^0.11
```

The changelog entry for each minor says what changed and carries that line. If you ever run
`composer update` after a release and Composer reports nothing to do, this is why: the
constraint, not Packagist.

### Configure

Create `phpstan.neon` in the add-on's directory:

```yaml
includes:
    - vendor/hampel/xenforo-phpstan/extension.neon

parameters:
    level: 2

    paths:
        - .

    excludePaths:
        # Scanned through the Composer autoloader already; scanning the directory as well lets a
        # dependency's scaffold shadow the add-on's own classes of the same name.
        analyseAndScan:
            - vendor (?)
        # Generated by XenForo, and not the add-on's code.
        analyse:
            - _output (?)
            - _releases (?)
            - _data (?)
```

The `(?)` marks a path optional. PHPStan refuses to start when an excluded path does not exist,
and an add-on that extends nothing has no `_output/`, one that has never been built has no
`_releases/`. If you use `phpstan/extension-installer`, the `includes` block is not needed.

Everything in this file is the add-on's own: its paths, its level, the directories of its that
are not source. Everything that is XenForo's is in the fragment.

### Run

```bash
vendor/bin/phpstan analyse
```

From the add-on's directory, with the add-on's own `vendor/bin/phpstan`. That is not fussiness:
PHPStan resolves classes through the autoloader of the project that installed it, so a phar
borrowed from another project, or a global install, consults that project's dependencies and
quietly describes a different dependency tree. The one exception is an add-on whose checkout
lives outside the install, a CI job with a forum unpacked beside it for instance, which names the
forum in the configuration:

```yaml
parameters:
    xenforo:
        root: /path/to/forum
```

The first run is cold and takes a few seconds while PHPStan reads the forum; later runs use its
cache and are quicker.

## Reading the first run

On a set of 44 add-ons with this configuration, the median add-on had 8 errors at level 2, and 28
of the 44 had ten or fewer. What is left is nearly always real, or at least yours, and it tends to
fall into a few kinds.

**Magic properties.** `$user->custom_column`, `\XF::options()->myOption` and `$params->user_id`
are read through `__get()`, and PHPStan reports each as an undefined property from level 2. They
are not all the same case, and the package does not paper over them with one setting:

- **Columns of XenForo's own entities are already typed.** Every core entity carries a `@property`
  docblock for each column and relation, so `$visitor->user_id` is an int without anything from
  you.
- **Columns your extension adds to a core entity are typed from your extension's docblock.** Declare
  them as XenForo declares its own, `@property string $vendor_addon_column` on the extension
  class, which is what `php cmd.php xf-dev:entity-class-properties` writes, and they are typed
  through the core class too, from `\XF::visitor()` or a finder result.
- **Columns of your own entities want the same docblock.** Run `xf-dev:entity-class-properties`
  and commit the result. It is an edit to source, so a decision, but it is the precise answer.
- **Your own options are typed by listing XenForo's option hint as a stub.** Development mode
  writes `_output/option_hint.php` for every add-on; `stubFiles: [_output/option_hint.php]` in
  the parameters types every option of yours and still reports a misspelt one. Core options can
  be typed the same way with `xf-dev:generate-options-stub`; the README has the recipe, and one
  trap in it.
- **Route parameters have no static type anywhere.** `$params->user_id` is whatever the route
  format said, and the honest answer is the crate: listing `XF\Mvc\ParameterBag` under
  `universalObjectCratesClasses` tells PHPStan the class answers any property name. The package
  ships a `crates.neon` that does this for entities, options and parameters together, and says in
  the README why it is a separate file: a crate silences a mistyped name as readily as a correct
  one, so take it per class, and only where nothing more precise exists.

**A value typed too widely.** `$entity->canUseThing()` on a value typed `XF\Mvc\Entity\Entity`
is undefined, and nothing can make it otherwise, because your extension is on `XF\Entity\User`.
That is the variable's type being too wide, and the fix is at the variable, usually a docblock on
a parameter. The same goes for a `@return Entity` on your own method that returns a user.

**A check that is now always true.** Inside the add-on that extends `XF\Entity\User`, the
shortcut returns your extension, so `create('XF:User') instanceof Extension\User` reads as always
true. The check is still worth making at runtime, where a disabled add-on would fail it; put the
`instanceof` behind a method that takes `object`, so nothing is decided before it runs. A guard
for a 2.3 method, `method_exists('XF', 'accessToken')`, is always true against a 2.3 forum for the
same reason, and the section on 2.2 below says what to do with it.

**Something wrong.** The rest. Read each one as if a careful colleague had written it in a review,
because that is roughly what it is.

When a report is puzzling, ask PHPStan what it thinks the value is:

```php
\PHPStan\dumpType($value);
```

It reports the type as an error on that line, and the type tells you whose declaration is
missing: yours, XenForo's, or a dependency's.

## Climbing the levels

**Level 2 is the place to start, and a clean level 2 is worth committing before going further.**
Raise the level one step at a time, and at each step decide for each new report whether to fix it
or to ignore it with a reason.

Ignore by identifier and path, never by message alone and never globally:

```yaml
parameters:
    ignoreErrors:
        -
            identifier: function.alreadyNarrowedType
            path: Cli/Command/Validate.php
```

PHPStan reports an ignore that no longer matches anything, so a guard that stops being needed is
still noticed. The identifier is printed under each message in the default output.

For an add-on with hundreds of reports at the level you want, PHPStan's baseline records all of
them in a file so that only new ones are reported:

```bash
vendor/bin/phpstan analyse --generate-baseline
```

That is a debt register rather than a fix. It lets a large add-on start at a useful level today
and pay the list down over time, and it is the right move for an add-on nobody is going to spend
a week on first.

What each level asks for, in the order you will meet them:

- **Level 5** checks arguments against parameters. The most common report here on an add-on is a
  nullable value passed where the parameter is not nullable, and most of those are real.
- **Level 6** asks for a type on every parameter, property and return. This is the wall, and on
  an add-on it is mostly your own methods. On a class other add-ons extend, which is any class you
  register through `extendClass()` and any you expect to be subclassed, do not add a native return
  type: an override written without it, as every existing one was, becomes an incompatible
  declaration PHP refuses to load. Type those by docblock, which PHPStan reads the same way. A bare
  `Finder`, `Tree` or `SubTree` in a docblock is reported here as missing its generic type; write
  `Finder<Thing>`.
- **Level 7** is where an undeclared return type starts to cost, and where the stubs do their
  work. Without them, every `view()`, `redirect()` and `fetch()` is `mixed` from here.
- **Level 8** reports a call on a value that might be null, at the place it is used. This is the
  level that found the thumbnail URL, and it finds something in most add-ons.
- **Level 9 and 10** treat `mixed` as the unknown it is. Above level 8, most of what is left is
  the add-on describing itself rather than the forum: PHPStan never infers a return type across a
  method boundary, so an untyped method of your own hands `mixed` to every caller. On one add-on,
  half of the level-max total came from a single untyped test helper. What remains once your own
  methods are typed is a core method the stubs do not yet cover, which is exactly the kind of
  report the package wants to hear about, and the route parameters, which no static type can know.

Several of our add-ons are clean at level max with no baseline and no ignores. It is reachable,
and the climb from 2 to max on a small add-on is an afternoon.

## Supporting XenForo 2.2

An add-on that supports 2.2 is analysed against a 2.3 forum, and three things follow.

**Keep the legacy class names.** The suffixed classes do not exist in 2.2, and 2.2 has no alias
machinery, so `XF\Repository\User` is the only name valid across the range. The generated stub
makes it analysable. For the same reason, `::class` on a renamed core class is not safe while 2.2
is supported: `\XF\Repository\UserRepository::class` is a string whatever is installed, and on
2.2 the class it names does not exist.

**Hold PHPStan to your PHP floor.** `phpVersion: {min: 70400, max: 80500}` reports syntax newer
than the floor, a `match` or a nullsafe call, and nothing else: a call to `str_contains()` on a
7.4 floor is not caught, because PHPStan reads functions from the PHP it runs on. It also makes
PHPUnit 12 unreadable, so the shape that works is a second configuration for the shipped code
alone, included from the first, with `tests` excluded. The README has it. PHPStan's own floor is
7.1, so an add-on supporting 2.2's PHP 7.0 holds at 7.1.

**Expect the version guards to read as always true.** `method_exists()` and `class_exists()`
checks for things 2.3 added are PHPStan being told the truth about one forum. Ignore each by
identifier and path, as above, so a guard that stops being needed is still noticed.

## Running it in CI

The one thing a CI job cannot download is XenForo, so a job needs a forum unpacked beside the
checkout, from your own copy, and `xenforo.root` pointing at it. The generated set can be produced
in the job with `generate --root=`, since the generator needs nothing but the files. Everything
else is the same `vendor/bin/phpstan analyse`.

Short of that, a pre-commit hook that runs the analysis from the add-on's directory catches most
of what CI would, and costs a few seconds.

## What it will not do

- **A legacy name is a subclass, not an alias.** PHPStan has no notion of two names for one class,
  so a value typed to the renamed class does not satisfy a parameter typed to the legacy name. The
  shortcuts return the legacy name when the add-on used it, which keeps your own types consistent;
  a value from XenForo's own typed API may still need a docblock.
- **An overridden signature is seen only through your own shortcut.** A value that arrives typed
  as the core class from anywhere else carries the core signature.
- **Code built on another add-on's vendored library does not resolve.** Only the analysed add-on's
  `vendor/` is scanned. Exclude that code; it runs in the sibling's context.
- **A value passed by reference inside an array literal is not tracked.** After
  `$app->fire($event, [&$collected])`, PHPStan still types `$collected` as the empty array it
  started as. That is PHPStan's own flow analysis; declare the type with a docblock after the
  call.
- **It does not know what a route parameter, a keyed container entry or an option holds**, beyond
  what a stub or a docblock of yours says. Those are facts about an install.

## Where to get it, and where to report what you find

- Source and issues: [github.com/hampel/xenforo-phpstan](https://github.com/hampel/xenforo-phpstan)
- Packagist: [packagist.org/packages/hampel/xenforo-phpstan](https://packagist.org/packages/hampel/xenforo-phpstan)
- Licence: MIT.

The README is the reference, and it goes into more detail than this on every section above: the
options recipes, the floor configuration, the full list of typed methods and corrected docblocks,
and the limitations.

The list of return types the stubs carry has grown almost entirely from reports: an add-on
reaches level 9, finds a core method that comes back `mixed`, and says so. If you reach a core
method the package does not yet type, or a report you think is wrong, open an issue with the
method, the level and what PHPStan printed. A `\PHPStan\dumpType()` on the value is the most
useful single line to include.
