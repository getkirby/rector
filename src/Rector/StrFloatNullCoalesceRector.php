<?php

namespace Kirby\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * `Str::float()` returns `null` instead of `'0'` for
 * non-numeric values in Kirby 6. This rule keeps the old
 * behavior. Remove the fallback where `null` is fine.
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final class StrFloatNullCoalesceRector extends AbstractKirbyRector
{
	protected const SKIP = 'kirby_rector_skip';

	public function getNodeTypes(): array
	{
		return [Coalesce::class, StaticCall::class];
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Keep the `\'0\'` fallback of `Str::float()` for non-numeric values',
			[
				new CodeSample(
					<<<'CODE_SAMPLE'
					$value = Str::float($input);
					CODE_SAMPLE,
					<<<'CODE_SAMPLE'
					$value = Str::float($input) ?? '0';
					CODE_SAMPLE
				)
			]
		);
	}

	protected function isStrFloat(Node $node): bool
	{
		return
			$node instanceof StaticCall &&
			$node->isFirstClassCallable() === false &&
			$this->isName($node->name, 'float') === true &&
			$this->isClassName($node->class, ['Kirby\Toolkit\Str']) === true;
	}

	/**
	 * @param Coalesce|StaticCall $node
	 */
	public function refactor(Node $node): Node|null
	{
		// already has a fallback
		if ($node instanceof Coalesce) {
			if ($this->isStrFloat($node->left) === true) {
				$node->left->setAttribute(static::SKIP, true);
			}

			return null;
		}

		if (
			$node->getAttribute(static::SKIP) === true ||
			$this->isStrFloat($node) === false
		) {
			return null;
		}

		$node->setAttribute(static::SKIP, true);

		return new Coalesce(
			left: $node,
			right: new String_('0')
		);
	}
}
