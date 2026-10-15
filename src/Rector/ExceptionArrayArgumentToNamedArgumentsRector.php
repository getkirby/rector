<?php

namespace Kirby\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ClassReflection;
use Rector\PHPStan\ScopeFetcher;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Kirby 6 no longer accepts a single array argument
 * for exceptions. Named arguments are used instead.
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final class ExceptionArrayArgumentToNamedArgumentsRector extends AbstractKirbyRector
{
	protected const EXCEPTION = 'Kirby\Exception\Exception';

	protected const KEYS = [
		'data',
		'details',
		'fallback',
		'httpCode',
		'key',
		'message',
		'previous',
		'translate'
	];

	/**
	 * Converts the array items to named arguments;
	 * returns `null` if the array cannot be converted safely
	 *
	 * @return array<Arg>|null
	 */
	protected function argsFromArray(Array_ $array): array|null
	{
		$args = [];

		foreach ($array->items as $item) {
			if (
				$item === null ||
				$item->unpack === true ||
				$item->byRef === true ||
				$item->key instanceof String_ === false ||
				in_array($item->key->value, static::KEYS, true) === false
			) {
				return null;
			}

			$args[] = new Arg(
				value: $item->value,
				name: new Identifier($item->key->value)
			);
		}

		return $args;
	}

	public function getNodeTypes(): array
	{
		return [New_::class, StaticCall::class];
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Replace the array argument of Kirby exceptions with named arguments',
			[
				new CodeSample(
					<<<'CODE_SAMPLE'
					throw new NotFoundException([
						'key'  => 'page.notFound',
						'data' => ['slug' => $slug]
					]);
					CODE_SAMPLE,
					<<<'CODE_SAMPLE'
					throw new NotFoundException(key: 'page.notFound', data: ['slug' => $slug]);
					CODE_SAMPLE
				)
			]
		);
	}

	/**
	 * Checks if the `new` expression creates a Kirby exception
	 * that still uses the constructor of the base class
	 */
	protected function isKirbyException(New_ $node): bool
	{
		if ($this->isClassName($node->class, [static::EXCEPTION]) === false) {
			return false;
		}

		$name = $this->resolveClassName($node->class);

		// unknown class that matched by name
		if ($this->reflectionProvider->hasClass($name) === false) {
			return true;
		}

		return $this->usesBaseConstructor(
			$this->reflectionProvider->getClass($name)
		);
	}

	/**
	 * Checks if the static call is a `parent::__construct()`
	 * call inside of a custom Kirby exception class
	 */
	protected function isParentConstructorCall(StaticCall $node): bool
	{
		if (
			$node->class instanceof Name === false ||
			$node->class->toLowerString() !== 'parent' ||
			$this->isName($node->name, '__construct') === false
		) {
			return false;
		}

		$parent = ScopeFetcher::fetch($node)->getClassReflection()?->getParentClass();

		if ($parent === null) {
			return false;
		}

		if (
			$parent->getName() !== static::EXCEPTION &&
			$parent->isSubclassOf(static::EXCEPTION) === false
		) {
			return false;
		}

		return $this->usesBaseConstructor($parent);
	}

	/**
	 * @param New_|StaticCall $node
	 */
	public function refactor(Node $node): Node|null
	{
		$matches = match (true) {
			$node instanceof New_ => $this->isKirbyException($node),
			default               => $this->isParentConstructorCall($node)
		};

		if ($matches === false || count($node->args) !== 1) {
			return null;
		}

		$arg = $node->args[0];

		if (
			$arg instanceof Arg === false ||
			$arg->name !== null ||
			$arg->unpack === true
		) {
			return null;
		}

		// array literal: turn each key into a named argument
		if ($arg->value instanceof Array_) {
			$args = $this->argsFromArray($arg->value);

			if ($args === null) {
				return null;
			}

			$node->args = $args;
			return $node;
		}

		// array variable: spread it as named arguments
		if ($this->getType($arg->value)->isArray()->yes() === true) {
			$arg->unpack = true;
			return $node;
		}

		return null;
	}

	/**
	 * Checks if the exception class does not
	 * define its own constructor signature
	 */
	protected function usesBaseConstructor(ClassReflection $class): bool
	{
		if ($class->hasConstructor() === false) {
			return true;
		}

		$declaring = $class->getConstructor()->getDeclaringClass()->getName();

		return strtolower($declaring) === strtolower(static::EXCEPTION);
	}
}
