# Kirby Rector

[Rector](https://getrector.com) rules to upgrade Kirby sites and plugins from Kirby 5 to Kirby 6.

## Usage

Install Rector and the Kirby rules in your project:

```bash
composer require --dev rector/rector getkirby/rector
```

Create a `rector.php` file in the root of your project:

```php
<?php

use Kirby\Rector\Set\KirbySetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/site',
	])
	->withSkip([
		__DIR__ . '/site/plugins/*/vendor',
	])
	->withImportNames(importShortClasses: false, removeUnusedImports: true)
	->withSets([KirbySetList::KIRBY_6]);
```

Preview the changes and apply them:

```bash
vendor/bin/rector --dry-run
vendor/bin/rector
```

### Recommendations

- Run Rector while Kirby 5 is still installed. Many rules need to know the type of a variable (e.g. that `$sessions` is a `Kirby\Session\Sessions` object), and Rector can only resolve those types when the old classes exist. Upgrade to Kirby 6 afterwards.
- `withImportNames()` replaces the `use` statements of renamed classes. Without it, Rector writes fully-qualified class names and leaves the old imports behind. Note that it also imports fully-qualified names in the rest of your code.
- Rector writes new arguments on a single line. Run your code style fixer afterwards (e.g. PHP CS Fixer).
- Rules that depend on types can't detect calls on untyped variables. Add type hints or `@var` annotations to templates and snippets to get the most out of the rules:

  ```php
  /** @var \Kirby\Cms\Page $page */
  ```

## What gets migrated

### Moved and renamed classes

| Before                                             | After                                               |
| -------------------------------------------------- | --------------------------------------------------- |
| `Kirby\Cms\Api`                                    | `Kirby\Api\Api`                                     |
| `Kirby\Cms\Auth`                                   | `Kirby\Auth\Auth`                                   |
| `Kirby\Cms\Auth\Status`                            | `Kirby\Auth\Status`                                 |
| `Kirby\Cms\Blueprint` (and `File`/`Page`/`Site`/`User`) | `Kirby\Blueprint\Blueprint` (…)                |
| `Kirby\Field\FieldOptions`                         | `Kirby\Form\FieldOptions`                           |
| `Kirby\Panel\ChangesDialog`                        | `Kirby\Panel\Controller\Dialog\ChangesDialogController` |
| `Kirby\Panel\PageCreateDialog`                     | `Kirby\Panel\Controller\Dialog\PageCreateDialogController` |
| `Kirby\Panel\Ui\Buttons\*`                         | `Kirby\Panel\Ui\Button\*`                           |
| `Kirby\Panel\Ui\Buttons\LanguagesDropdown`         | `Kirby\Panel\Ui\Button\LanguagesButton`             |
| `Kirby\Panel\Ui\FilePreviews\*`                    | `Kirby\Panel\Ui\FilePreview\*`                      |
| `Kirby\Session\AutoSession`                        | `Kirby\Session\Sessions`                            |
| `Kirby\Session\FileSessionStore`                   | `Kirby\Session\FileStore`                           |
| `Kirby\Session\SessionData`                        | `Kirby\Session\Data`                                |
| `Kirby\Session\SessionStore`                       | `Kirby\Session\Store`                               |

Older deprecated aliases (`Kirby\Cms\Field`, `Kirby\Toolkit\F`, `Kirby\Form\Options`, …) are replaced as well.

### Changed methods

| Before                                                   | After                                                     |
| -------------------------------------------------------- | --------------------------------------------------------- |
| `new NotFoundException(['key' => …, 'data' => …])`       | `new NotFoundException(key: …, data: …)`                  |
| `parent::__construct(['key' => …])` in custom exceptions | `parent::__construct(key: …)`                             |
| `Uuid::for('page://…')`                                  | `Uuid::from('page://…')`                                  |
| `Uuid::for('/@/page/…')`                                 | `Permalink::from('/@/page/…')`                            |
| `$uuid->url()`                                           | `$uuid->toPermalink()`                                    |
| `$language->isLast()`                                    | `$language->isLastLanguage()`                             |
| `$fileBlueprint->acceptMime()`                           | `$fileBlueprint->acceptAttribute()`                       |
| `Blueprint::factory($name, $fallback, $model)`           | `Blueprint::factory($model, $name, $fallback)`            |
| `$file->blueprints(inSection: …)`                        | `$file->blueprints(inField: …)` (also `acceptedFileTemplates()`, `AcceptRules::fileTemplates()`) |
| `$form->content()`                                       | `$form->toStoredValues()`                                 |
| `$form->values()`                                        | `$form->toFormValues()`                                   |
| `Response::json($data, $code, null)`                     | `Response::json($data, $code, false)`                     |
| `Str::float($value)`                                     | `Str::float($value) ?? '0'` (keeps the old behavior)      |

### Auth

| Before                                     | After                                                |
| ------------------------------------------ | ---------------------------------------------------- |
| `$auth->ipHash()`                          | `$kirby->visitor()->ip(hash: true)`                  |
| `$auth->enabledChallenges()`               | `$auth->challenges()->enabled()`                     |
| `$auth->isBlocked()`                       | `$auth->limits()->isBlocked()`                       |
| `$auth->log()`                             | `$auth->limits()->log()`                             |
| `$auth->logfile()`                         | `$auth->limits()->file()`                            |
| `$auth->track()`                           | `$auth->limits()->track()`                           |
| `$status->challenge(automaticFallback: …)` | `$status->challenge(fallback: …)`                    |
| `$system->loginMethods()`                  | `$kirby->auth()->methods()->enabled()`               |
| `$system->is2FA()`                         | `$kirby->auth()->methods()->hasAnyRequiring2FA()`    |
| `$user->changeTotp($secret)`               | `$user->changeSecret('totp', $secret)`               |

### Sessions

| Before                                    | After                                        |
| ----------------------------------------- | -------------------------------------------- |
| `new Sessions($store, $options)`          | `Sessions::factory($store, $options)`        |
| `new AutoSession($store, $options)`       | `Sessions::factory($store, $options)`        |
| `$kirby->sessionHandler()`                | `$kirby->sessions()`                         |
| `$sessions->get($token)`                  | `$sessions->find($token)`                    |
| `$sessions->getManually($token)`          | `$sessions->find($token)`                    |
| `$sessions->createManually($options)`     | `$sessions->create([...$options, 'mode' => 'manual'])` |
| `$sessions->currentDetected()`            | `$sessions->detect()`                        |
| `$sessions->updateCache($session)`        | `$sessions->track($session)`                 |
| `$sessions->cookieDomain()`               | `$sessions->cookie()->domain()`              |
| `$sessions->cookieName()`                 | `$sessions->cookie()->name()`                |

### Panel

| Before                         | After                                                  |
| ------------------------------ | ------------------------------------------------------ |
| `Panel::url()`                 | `App::instance()->panel()->url()`                      |
| `Panel::router($path)`         | `App::instance()->panel()->router()?->call($path)`     |
| `Panel::isFiberRequest()`      | `App::instance()->panel()->isStateRequest()`           |
| `Panel::multilang()`           | `App::instance()->panel()->multilang()`                |
| `Panel::referrer()`            | `App::instance()->panel()->referrer()`                 |
| `Home::isPanelUrl($url)`       | `App::instance()->panel()->isPanelUrl($url)`           |
| `Home::panelPath($url)`        | `App::instance()->panel()->path($url)`                 |
| `Home::url()`                  | `App::instance()->panel()->home()->url()`              |
| `Home::remembered()`           | `App::instance()->panel()->home()->remembered()`       |
| `Home::hasValidDomain($uri)`   | `App::instance()->panel()->home()->hasValidDomain($uri)` |
| `Dialog::response($data)`      | `DialogResponse::from($data)` (also `Drawer`, `Dropdown`, `Json`, `Request`, `Search`) |
| `new VersionsButton(versionId: …)` | `new VersionsButton(mode: …)`                      |

## What needs to be migrated by hand

Some breaking changes can't be migrated automatically. The most important ones:

- Custom fields defined as arrays must be rewritten as classes based on `Kirby\Form\Field`. The same goes for fields extending `Kirby\Form\FieldClass` or using the `Kirby\Form\Mixin\Value` mixin.
- Custom sections are no longer supported. Rewrite them as fields.
- Custom auth challenges must be rewritten based on the new `Kirby\Auth\Challenge` class.
- `Kirby\Form\Field::factory()`, the `$props` array of `Form::for()`/`new Form()`, and the removed `Form::data()`/`Form::strings()` methods.
- The picker classes (`Kirby\Cms\PagePicker`, …) have been replaced by Panel dialog controllers with a different API.
- `Panel::area()`, `Panel::buttons()`, `Panel::firewall()`, `Panel::hasAccess()` and the `Panel::routesFor*()` methods.
- Calls to methods that newly throw or return different types (see the changelog).
- Config options, blueprints and Panel plugins (Vue 3).

See the "Breaking changes" section of the Kirby 6 changelog for the full list.

## Development

```bash
composer install
composer test
```

The tests run the full set against the fixtures in `tests/Set/Kirby6/Fixture`. Each fixture contains the input code, a `-----` separator and the expected output. Fixtures without a separator must stay unchanged. The stubs in `tests/stubs` provide the Kirby 5 API for type resolution.
