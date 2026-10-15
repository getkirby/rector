<?php

/** @var \Kirby\Cms\App $kirby */
/** @var \Kirby\Cms\Page $page */

$value = Str::float($page->price());
$other = Str::float($page->price()) ?? '1';
$url   = Panel::url('pages/blog');
$json  = Response::json(['a' => 1], 200, null);

throw new ErrorPageException(['fallback' => 'Not found']);
-----
<?php

/** @var \Kirby\Cms\App $kirby */
/** @var \Kirby\Cms\Page $page */

$value = Str::float($page->price()) ?? '0';
$other = Str::float($page->price()) ?? '1';
$url   = \Kirby\Cms\App::instance()->panel()->url('pages/blog');
$json  = Response::json(['a' => 1], 200, false);

throw new ErrorPageException(fallback: 'Not found');
