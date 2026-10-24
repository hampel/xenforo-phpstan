# Changelog

## 0.8.2 (2026-10-24)

- every add-on's `_build/` is left out of the scan, beside its `vendor/`: a release build copies
  the add-on under `_build/upload/`, which declares each of its classes twice while it exists,
  and a file deleted while PHPStan read it aborted an adopter's run. `generate` skips it too
- `AbstractSetup::install()`, `upgrade()` and `uninstall()` may return nothing. XenForo documents
  `null|StepResult`, against which a setup with a single step, falling out of the method as most
  do, was a missing return in every add-on that has one
- `JobResult::$completed`, the one key its `__get()` answers, is `bool`
- README: PHPStan's `phpVersion` floor is 7.1, below XenForo 2.2's 7.0; a class declared twice
  inside a `\XF::$versionId` branch gives a `method.childReturnType` PHPStan refuses to ignore,
  and the answer is two files; the Installation paragraph names no current minor

## 0.8.1 (2026-10-23)

- `getRelation()` and `getRelationOrDefault()` are typed from the receiver's `@property-read` for
  the relation name, so `$user->getRelationOrDefault('Auth')->scheme_class` resolves. 0.7.1 had
  declared `getRelationOrDefault()` as returning `Entity`, which made every column read on the
  result `property.notFound` from level 2, where `mixed` had been quiet; that return is withdrawn

## 0.8.0 (2026-10-23)

- every undeclared controller `action*` method returns `AbstractReply`, through `$this->` and
  `parent::` alike, so a controller extension's `return parent::actionIndex()` is typed. A new
  service, hence the minor; an action an add-on declares as returning something narrower keeps
  its own type
- **Upgrading:** `composer require --dev hampel/xenforo-phpstan:^0.8`; a `^0.7` constraint does not
  reach this release

## 0.7.1 (2026-10-23)

- more returns XenForo leaves undeclared, each one an extension returns through `parent::`:
  `User::canFollowUser()`, `canViewFullProfile()`, `canViewBasicProfile()` and
  `hasAdminPermission()` are bool; `Entity::getRelationOrDefault()` is an entity and
  `getErrors()` the phrases by field; `ValidateAndSavableTrait::validate()` is bool and types
  what it writes to `$errors`; `MergeService::setSource()` and `setTarget()` are fluent and
  `merge()` a `ContinuationResult`, whose `completed()` is itself; the adapter's `fetchRow()`,
  `fetchAll()` and `fetchPairs()` are the shapes they build; `Structure::$columns` and
  `$relations` are arrays of arrays

## 0.7.0 (2026-10-23)

- `filter()` on a controller and on the request is typed from the filter string, as
  `XF\InputFilterer` casts the value: named filters, `?` for nullable, `array-<type>`,
  `datetime,obj`, and an array of filters as an array shape. A new service, hence the minor; a
  check written for a `mixed` result may now read as always true
- **Upgrading:** `composer require --dev hampel/xenforo-phpstan:^0.7`; a `^0.6` constraint does not
  reach this release

## 0.6.0 (2026-10-23)

- the properties `XF\AddOn\AddOn` answers by magic are typed: its nine own keys from their
  getters, and the installed `XF\Entity\AddOn`'s columns from the entity's docblock, so
  `$addOn->title` and `$addOn->version_string` resolve and a mistyped key is still reported. A
  new service, hence the minor
- **Upgrading:** `composer require --dev hampel/xenforo-phpstan:^0.6`; a `^0.5` constraint does not
  reach this release

## 0.5.1 (2026-10-23)

- `AbstractAdapter::query()` accepts a scalar for `$params`, as core passes one and the statement
  wraps it; XenForo's own `@param array` reported `$db->query($sql, $id)` at level 5

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
