<?php

namespace Acme\Plugin;

use Kirby\Exception\NotFoundException;
use Kirby\Http\Response;
use Kirby\Toolkit\Str;

function test(string $slug)
{
	throw new NotFoundException(key: 'page.notFound', data: ['slug' => $slug]);

	Response::json([], 200, false);
	Str::float('1.5') ?? '0';
}
