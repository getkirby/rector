<?php

namespace Acme\Plugin;

use Kirby\Cms\App;
use Kirby\Cms\Auth;
use Kirby\Cms\User;

function test(
	App $kirby,
	Auth $auth,
	User $user,
	string $secret
) {
	$kirby->auth()->ipHash();
	$auth->ipHash();
	$auth->isBlocked('mail@example.com');
	$auth->log();
	$auth->logfile();
	$auth->track('mail@example.com', false);
	$kirby->auth()->enabledChallenges();
	$kirby->auth()->status()->challenge(automaticFallback: false);
	$kirby->system()->loginMethods();
	$kirby->system()->is2FA();
	App::instance()->system()->loginMethods();
	$user->changeTotp($secret);
	$user->changeTotp(secret: null);
}
-----
<?php

namespace Acme\Plugin;

use Kirby\Cms\App;
use Kirby\Cms\Auth;
use Kirby\Cms\User;

function test(
	App $kirby,
	\Kirby\Auth\Auth $auth,
	User $user,
	string $secret
) {
	$kirby->visitor()->ip(hash: true);
	\Kirby\Cms\App::instance()->visitor()->ip(hash: true);
	$auth->limits()->isBlocked('mail@example.com');
	$auth->limits()->log();
	$auth->limits()->file();
	$auth->limits()->track('mail@example.com', false);
	$kirby->auth()->challenges()->enabled();
	$kirby->auth()->status()->challenge(fallback: false);
	$kirby->auth()->methods()->enabled();
	$kirby->auth()->methods()->hasAnyRequiring2FA();
	App::instance()->auth()->methods()->enabled();
	$user->changeSecret('totp', $secret);
	$user->changeSecret(secret: 'totp', content: null);
}
