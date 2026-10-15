<?php

namespace Kirby\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * `Uuid::for()` only accepts model objects in Kirby 6.
 * UUID strings are resolved with `Uuid::from()`,
 * permalinks with `Permalink::from()`.
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final class UuidForStringToFromRector extends AbstractKirbyRector
{
	public function getNodeTypes(): array
	{
		return [StaticCall::class];
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Replace `Uuid::for()` with a string by `Uuid::from()` or `Permalink::from()`',
			[
				new CodeSample(
					<<<'CODE_SAMPLE'
					$uuid      = Uuid::for('page://my-page');
					$permalink = Uuid::for('/@/page/my-page');
					CODE_SAMPLE,
					<<<'CODE_SAMPLE'
					$uuid      = Uuid::from('page://my-page');
					$permalink = \Kirby\Uuid\Permalink::from('/@/page/my-page');
					CODE_SAMPLE
				)
			]
		);
	}

	/**
	 * @param StaticCall $node
	 */
	public function refactor(Node $node): Node|null
	{
		if ($this->isName($node->name, 'for') === false) {
			return null;
		}

		if ($this->isClassName($node->class, ['Kirby\Uuid\Uuid'], subclasses: false) === false) {
			return null;
		}

		$seed = $node->args[0] ?? null;

		if (
			$seed instanceof Arg === false ||
			$seed->unpack === true ||
			$this->getType($seed->value)->isString()->yes() === false
		) {
			return null;
		}

		// permalink literal
		if (
			$seed->value instanceof String_ &&
			str_contains($seed->value->value, '/@/') === true
		) {
			return new StaticCall(
				class: new FullyQualified('Kirby\Uuid\Permalink'),
				name: 'from',
				args: [new Arg($seed->value)]
			);
		}

		$node->name = new Identifier('from');

		foreach ($node->args as $index => $arg) {
			if ($arg instanceof Arg === false) {
				continue;
			}

			if ($arg->name !== null) {
				$arg->name = match ($arg->name->toString()) {
					'seed'  => new Identifier('uuid'),
					default => $arg->name
				};

				continue;
			}

			// the context is the third parameter of `Uuid::from()`
			if ($index === 1) {
				$arg->name = new Identifier('context');
			}
		}

		return $node;
	}
}
