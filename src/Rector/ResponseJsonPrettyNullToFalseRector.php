<?php

namespace Kirby\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * The `$pretty` argument of `Response::json()`
 * must be a boolean in Kirby 6
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final class ResponseJsonPrettyNullToFalseRector extends AbstractKirbyRector
{
	protected const POSITION = 2;

	protected function findPrettyArg(StaticCall $node): Arg|null
	{
		foreach ($node->args as $index => $arg) {
			if ($arg instanceof Arg === false || $arg->unpack === true) {
				return null;
			}

			if ($arg->name !== null) {
				if ($this->isName($arg->name, 'pretty') === true) {
					return $arg;
				}

				continue;
			}

			if ($index === static::POSITION) {
				return $arg;
			}
		}

		return null;
	}

	public function getNodeTypes(): array
	{
		return [StaticCall::class];
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Pass `false` instead of `null` as `$pretty` argument of `Response::json()`',
			[
				new CodeSample(
					<<<'CODE_SAMPLE'
					return Response::json($data, 200, null);
					CODE_SAMPLE,
					<<<'CODE_SAMPLE'
					return Response::json($data, 200, false);
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
		if ($this->isName($node->name, 'json') === false) {
			return null;
		}

		$classes = ['Kirby\Http\Response', 'Kirby\Cms\Response'];

		if ($this->isClassName($node->class, $classes) === false) {
			return null;
		}

		$pretty = $this->findPrettyArg($node);

		if (
			$pretty?->value instanceof ConstFetch === false ||
			$this->isName($pretty->value, 'null') === false
		) {
			return null;
		}

		$pretty->value = new ConstFetch(new Name('false'));

		return $node;
	}
}
