<?php

namespace Kirby\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * The `Sessions` constructor no longer accepts an options array
 * in Kirby 6. `AutoSession` has been merged into `Sessions`.
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final class NewSessionsToFactoryRector extends AbstractKirbyRector
{
	public function getNodeTypes(): array
	{
		return [New_::class];
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Replace `new Sessions()` and `new AutoSession()` with `Sessions::factory()`',
			[
				new CodeSample(
					<<<'CODE_SAMPLE'
					$sessions = new Sessions($root, ['mode' => 'header']);
					CODE_SAMPLE,
					<<<'CODE_SAMPLE'
					$sessions = \Kirby\Session\Sessions::factory($root, ['mode' => 'header']);
					CODE_SAMPLE
				)
			]
		);
	}

	/**
	 * @param New_ $node
	 */
	public function refactor(Node $node): Node|null
	{
		$classes = ['Kirby\Session\Sessions', 'Kirby\Session\AutoSession'];

		if ($this->isClassName($node->class, $classes, subclasses: false) === false) {
			return null;
		}

		if ($node->args === [] || count($node->args) > 2) {
			return null;
		}

		foreach ($node->args as $index => $arg) {
			if ($arg instanceof Arg === false || $arg->unpack === true) {
				return null;
			}

			// already uses the new constructor signature
			if (
				$arg->name !== null &&
				$this->isNames($arg->name, ['store', 'options']) === false
			) {
				return null;
			}

			if (
				$index === 1 &&
				$arg->name === null &&
				$this->getType($arg->value)->isArray()->no() === true
			) {
				return null;
			}
		}

		return new StaticCall(
			class: new FullyQualified('Kirby\Session\Sessions'),
			name: 'factory',
			args: $node->args
		);
	}
}
