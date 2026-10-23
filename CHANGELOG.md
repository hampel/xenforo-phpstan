# Changelog

## 0.5.0 (2026-10-23)

- `generate --check` reports whether the generated set is one PHPStan will accept for the forum,
  without writing: exit 0 and the generated date when current, exit 1 and the reason when
  missing, stale or from an older release. The set is shared by every add-on on a forum, and
  until now the only way to ask was to regenerate it or to run PHPStan and see it refused
- **Upgrading:** below 1.0 a caret constraint pins the minor, so `^0.4` does not reach this
  release and `composer update` reports nothing to do. Move with
  `composer require --dev hampel/xenforo-phpstan:^0.5`

## 0.4.2 (2026-10-23)

- `AddOn\Manager::getById()` returns `AddOn|null`
- README: above level 8 the residual is mostly the add-on's own untyped methods, and an add-on
  with no entity of its own usually needs no crates

## 0.4.1 (2026-10-23)

- `NodeRepository::mergeNodeListExtras()` returns the string-keyed extras array its body
  builds

## 0.4.0 (2026-10-22)

- `XF\Tree` and `XF\SubTree` are generic over their record, and
  `NodeRepository::createNodeTree()` returns `Tree<Node>`: a subtree's `record`, `children()`,
  `parent()`, iteration, offsets, `getData()`, `getFlattened()`, `getDescendants()` and
  `filter()` carry the record type. A bare `Tree` or `SubTree` in an add-on's own docblock is
  now a missing-generics report at level 6 and above, which is why this is a minor version

## 0.3.2 (2026-10-22)

- `NodeRepository::filterViewable()` returns the collection it was given, keeping its entity
  type, and `Node::getNodeListExtras()` returns a string-keyed array

## 0.3.1 (2026-10-22)

- iterating an `AbstractCollection`, or a `Finder`, yields the entity again. 0.3.0 declared both
  classes in `stubs/core.stub`, and for a class declared in a stub PHPStan resolves
  `getIterator()` from its own `IteratorAggregate` stub rather than from XenForo's docblock, so
  every `foreach` over a `fetch()` saw `mixed`; both `getIterator()` methods are now restated
- `NodeRepository::getFullNodeList()` returns `AbstractCollection<Node>` and
  `Node::getBreadcrumbs()` the list of crumb arrays the method body builds

## 0.3.0 (2026-10-22)

- `stubs/core.stub`: return types for the methods XenForo 2.3 leaves undeclared and every add-on
  calls - the controller's `view()`, `redirect()`, `error()`, `message()` and `exception()`,
  the reply classes' parameter getters, the finder's fluent methods as `static` so a chain keeps
  its entity type, `Router::buildLink()`, `\XF::phrase()`, the database adapter's `quote()`,
  `fetchAllColumn()` and `getQueryCount()`, `Entity::save()`, `AbstractCollection::keys()`,
  `Request::filter()` and `App::findByContentType()`; and `AbstractDdl::addColumn()` in the
  schema stub. An add-on
  analysed at level 7 or above no longer meets `mixed` at each of them

## 0.2.2 (2026-10-22)

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
