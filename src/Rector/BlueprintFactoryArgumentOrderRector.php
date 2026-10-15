<?php

namespace Kirby\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * `Blueprint::factory()` requires the model as first argument in Kirby 6
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final class BlueprintFactoryArgumentOrderRector extends AbstractKirbyRector
{
	public function getNodeTypes(): array
	{
		return [StaticCall::class];
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Move the model to the first argument of `Blueprint::factory()`',
			[
				new CodeSample(
					<<<'CODE_SAMPLE'
					$blueprint = Blueprint::factory('pages/article', 'pages/default', $page);
					CODE_SAMPLE,
					<<<'CODE_SAMPLE'
					$blueprint = Blueprint::factory($page, 'pages/article', 'pages/default');
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
		if ($this->isName($node->name, 'factory') === false) {
			return null;
		}

		$classes = ['Kirby\Cms\Blueprint', 'Kirby\Blueprint\Blueprint'];

		if ($this->isClassName($node->class, $classes) === false) {
			return null;
		}

		if (count($node->args) !== 3) {
			return null;
		}

		foreach ($node->args as $arg) {
			// named arguments keep working as the names did not change
			if (
				$arg instanceof Arg === false ||
				$arg->name !== null ||
				$arg->unpack === true
			) {
				return null;
			}
		}

		[$name, $fallback, $model] = $node->args;

		// already in the new order
		if ($this->getType($name->value)->isObject()->yes() === true) {
			return null;
		}

		$node->args = [$model, $name, $fallback];

		return $node;
	}
}
