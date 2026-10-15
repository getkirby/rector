<?php

namespace Kirby\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;

/**
 * Shared helpers for all Kirby rules
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
abstract class AbstractKirbyRector extends AbstractRector
{
	/**
	 * Global class aliases registered by Kirby (see `kirby/config/aliases.php`)
	 * that are relevant for the rules. Templates and snippets often use them
	 * without an import, e.g. `Str::float()`.
	 */
	protected const ALIASES = [
		'errorpageexception' => 'Kirby\Exception\ErrorPageException',
		'kirby'              => 'Kirby\Cms\App',
		'panel'              => 'Kirby\Panel\Panel',
		'response'           => 'Kirby\Cms\Response',
		'str'                => 'Kirby\Toolkit\Str',
	];

	public function __construct(
		protected ReflectionProvider $reflectionProvider
	) {
	}

	/**
	 * Returns the expression for the app instance: reuses the
	 * receiver of the given accessor (e.g. `$kirby` in `$kirby->auth()`)
	 * or falls back to `\Kirby\Cms\App::instance()`
	 */
	protected function app(Expr $receiver, string $accessor): Expr
	{
		if (
			$receiver instanceof MethodCall &&
			$receiver->args === [] &&
			$this->isName($receiver->name, $accessor) === true
		) {
			return $receiver->var;
		}

		return new StaticCall(
			class: new FullyQualified('Kirby\Cms\App'),
			name: 'instance'
		);
	}

	/**
	 * Checks if the class name node refers to one of the given classes
	 * (or one of their subclasses), resolving Kirby's global aliases
	 */
	protected function isClassName(
		Node $class,
		array $classes,
		bool $subclasses = true
	): bool {
		if ($class instanceof Name === false) {
			return false;
		}

		if ($class->isSpecialClassName() === true) {
			return false;
		}

		$name = $this->resolveClassName($class);

		foreach ($classes as $target) {
			if (strtolower($name) === strtolower($target)) {
				return true;
			}

			if (
				$subclasses === true &&
				$this->reflectionProvider->hasClass($name) === true &&
				$this->reflectionProvider->getClass($name)->isSubclassOf($target) === true
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the expression is an object of one of the given classes
	 */
	protected function isObjectOfTypes(Expr $expr, array $classes): bool
	{
		foreach ($classes as $class) {
			if ($this->isObjectType($expr, new ObjectType($class)) === true) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Resolves the full class name incl. Kirby's global aliases
	 */
	protected function resolveClassName(Name $class): string
	{
		$name = $this->getName($class) ?? $class->toString();
		return static::ALIASES[strtolower(ltrim($name, '\\'))] ?? $name;
	}
}
