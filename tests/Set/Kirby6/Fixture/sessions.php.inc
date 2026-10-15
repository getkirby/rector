<?php

namespace Acme\Plugin;

use Kirby\Cms\App;
use Kirby\Session\AutoSession;
use Kirby\Session\Sessions;

function test(
	App $kirby,
	AutoSession $autoSession,
	Sessions $sessions,
	array $options,
	string $token
) {
	new Sessions('/sessions', ['mode' => 'header']);
	new AutoSession('/sessions', $options);
	new AutoSession('/sessions');
	$kirby->sessionHandler()->get($token);
	$sessions->get($token, 'header');
	$autoSession->get(['long' => true]);
	$autoSession->createManually();
	$sessions->createManually(['long' => true, 'mode' => 'cookie']);
	$sessions->createManually($options);
	$sessions->getManually($token);
	$sessions->currentDetected();
	$sessions->cookieDomain();
	$sessions->cookieName();
}
-----
<?php

namespace Acme\Plugin;

use Kirby\Cms\App;
use Kirby\Session\AutoSession;
use Kirby\Session\Sessions;

function test(
	App $kirby,
	\Kirby\Session\Sessions $autoSession,
	Sessions $sessions,
	array $options,
	string $token
) {
	\Kirby\Session\Sessions::factory('/sessions', ['mode' => 'header']);
	\Kirby\Session\Sessions::factory('/sessions', $options);
	\Kirby\Session\Sessions::factory('/sessions');
	$kirby->sessions()->find($token);
	$sessions->find($token, 'header');
	$autoSession->get(['long' => true]);
	$autoSession->create(['mode' => 'manual']);
	$sessions->create(['long' => true, 'mode' => 'manual']);
	$sessions->create([...$options, 'mode' => 'manual']);
	$sessions->find($token);
	$sessions->detect();
	$sessions->cookie()->domain();
	$sessions->cookie()->name();
}
