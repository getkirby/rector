<?php

namespace Acme\Plugin;

use Kirby\Exception\Exception;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;

class CustomException extends \Exception {}

class OwnConstructorException extends NotFoundException
{
	public function __construct(string $name)
	{
		parent::__construct(['data' => ['name' => $name]]);
	}
}

function exceptions(string $slug, array $args)
{
	throw new NotFoundException([
		'key'  => 'page.notFound',
		'data' => ['slug' => $slug]
	]);

	throw new PermissionException(['fallback' => 'Nope', 'httpCode' => 403]);
	throw new Exception($args);
	throw new Exception('Some message');
	throw new Exception(['foo' => 'bar']);
	throw new OwnConstructorException('test');
	throw new CustomException('test');
	throw new \Kirby\Exception\InvalidArgumentException([]);
}
-----
<?php

namespace Acme\Plugin;

use Kirby\Exception\Exception;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;

class CustomException extends \Exception {}

class OwnConstructorException extends NotFoundException
{
	public function __construct(string $name)
	{
		parent::__construct(data: ['name' => $name]);
	}
}

function exceptions(string $slug, array $args)
{
	throw new NotFoundException(key: 'page.notFound', data: ['slug' => $slug]);

	throw new PermissionException(fallback: 'Nope', httpCode: 403);
	throw new Exception(...$args);
	throw new Exception('Some message');
	throw new Exception(['foo' => 'bar']);
	throw new OwnConstructorException('test');
	throw new CustomException('test');
	throw new \Kirby\Exception\InvalidArgumentException();
}
