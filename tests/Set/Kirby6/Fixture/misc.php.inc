<?php

namespace Acme\Plugin;

use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Form\Form;
use Kirby\Http\Response;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;

function test(
	Form $form,
	Language $language,
	Page $page,
	Uuid $uuidObject,
	string $uuid
) {
	$form->content();
	$form->values();

	$language->isLast();

	Response::json([], 200, null);
	Response::json([], pretty: null);
	Response::json([], 200, true);

	Str::float('1.5');
	Str::float('1.5') ?? null;

	Uuid::for('page://abc');
	Uuid::for('/@/page/abc');
	Uuid::for($uuid);
	Uuid::for($page);
	$uuidObject->url();
}
-----
<?php

namespace Acme\Plugin;

use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Form\Form;
use Kirby\Http\Response;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;

function test(
	Form $form,
	Language $language,
	Page $page,
	Uuid $uuidObject,
	string $uuid
) {
	$form->toStoredValues();
	$form->toFormValues();

	$language->isLastLanguage();

	Response::json([], 200, false);
	Response::json([], pretty: false);
	Response::json([], 200, true);

	Str::float('1.5') ?? '0';
	Str::float('1.5') ?? null;

	Uuid::from('page://abc');
	\Kirby\Uuid\Permalink::from('/@/page/abc');
	Uuid::from($uuid);
	Uuid::for($page);
	$uuidObject->toPermalink();
}
