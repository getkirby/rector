<?php

namespace Acme\Plugin;

use Kirby\Cms\Page;
use Kirby\Panel\Dialog;
use Kirby\Panel\Home;
use Kirby\Panel\Panel;
use Kirby\Panel\Ui\Buttons\VersionsButton;

function test(
	Page $page
) {
	Panel::url('site');
	Panel::router('pages/blog');
	Panel::isFiberRequest();
	Panel::go('site');
	Home::isPanelUrl('https://example.com/panel');
	Home::panelPath('https://example.com/panel/site');
	Home::url();
	Dialog::response(['component' => 'k-text-dialog']);
	new VersionsButton($page, versionId: 'changes');
}
-----
<?php

namespace Acme\Plugin;

use Kirby\Cms\Page;
use Kirby\Panel\Dialog;
use Kirby\Panel\Home;
use Kirby\Panel\Panel;
use Kirby\Panel\Ui\Buttons\VersionsButton;

function test(
	Page $page
) {
	\Kirby\Cms\App::instance()->panel()->url('site');
	\Kirby\Cms\App::instance()->panel()->router()?->call('pages/blog');
	\Kirby\Cms\App::instance()->panel()->isStateRequest();
	Panel::go('site');
	\Kirby\Cms\App::instance()->panel()->isPanelUrl('https://example.com/panel');
	\Kirby\Cms\App::instance()->panel()->path('https://example.com/panel/site');
	\Kirby\Cms\App::instance()->panel()->home()->url();
	\Kirby\Panel\Response\DialogResponse::from(['component' => 'k-text-dialog']);
	new \Kirby\Panel\Ui\Button\VersionsButton($page, mode: 'changes');
}
