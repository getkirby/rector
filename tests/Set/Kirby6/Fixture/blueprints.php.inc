<?php

namespace Acme\Plugin;

use Kirby\Cms\Blueprint;
use Kirby\Cms\File;
use Kirby\Cms\FileBlueprint;
use Kirby\Cms\Page;
use Kirby\Cms\PageBlueprint;

function test(
	File $file,
	FileBlueprint $fileBlueprint,
	Page $page
) {
	Blueprint::factory('pages/article', null, $page);
	PageBlueprint::factory('pages/article', 'pages/default', $page);
	Blueprint::factory(name: 'pages/article', fallback: null, model: $page);
	$file->blueprints(inSection: 'gallery');
	$file->blueprints('gallery');
	$fileBlueprint->acceptMime();
}
-----
<?php

namespace Acme\Plugin;

use Kirby\Cms\Blueprint;
use Kirby\Cms\File;
use Kirby\Cms\FileBlueprint;
use Kirby\Cms\Page;
use Kirby\Cms\PageBlueprint;

function test(
	File $file,
	\Kirby\Blueprint\FileBlueprint $fileBlueprint,
	Page $page
) {
	\Kirby\Blueprint\Blueprint::factory($page, 'pages/article', null);
	\Kirby\Blueprint\PageBlueprint::factory($page, 'pages/article', 'pages/default');
	\Kirby\Blueprint\Blueprint::factory(name: 'pages/article', fallback: null, model: $page);
	$file->blueprints(inField: 'gallery');
	$file->blueprints('gallery');
	$fileBlueprint->acceptAttribute();
}
