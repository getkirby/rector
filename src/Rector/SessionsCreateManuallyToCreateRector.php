<?php

namespace Kirby\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * `Sessions::createManually()` is deprecated in Kirby 6
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final class SessionsCreateManuallyToCreateRector extends AbstractKirbyRector
{
	public function getNodeTypes(): array
	{
		return [MethodCall::class];
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Replace `$sessions->createManually()` with `$sessions->create([\'mode\' => \'manual\'])`',
			[
				new CodeSample(
					<<<'CODE_SAMPLE'
					$session = $sessions->createManually(['long' => true]);
					CODE_SAMPLE,
					<<<'CODE_SAMPLE'
					$session = $sessions->create(['long' => true, 'mode' => 'manual']);
					CODE_SAMPLE
				)
			]
		);
	}

	protected function modeItem(): ArrayItem
	{
		return new ArrayItem(
			value: new String_('manual'),
			key: new String_('mode')
		);
	}

	/**
	 * @param MethodCall $node
	 */
	public function refactor(Node $node): Node|null
	{
		if ($this->isName($node->name, 'createManually') === false) {
			return null;
		}

		$classes = ['Kirby\Session\Sessions', 'Kirby\Session\AutoSession'];

		if ($this->isObjectOfTypes($node->var, $classes) === false) {
			return null;
		}

		if (count($node->args) > 1) {
			return null;
		}

		$options = $node->args[0] ?? null;

		if ($options === null) {
			$options = new Array_([$this->modeItem()]);
		} elseif ($options instanceof Arg === false || $options->unpack === true) {
			return null;
		} elseif ($options->value instanceof Array_) {
			$options = $options->value;

			// the mode is always enforced by `createManually()`
			$options->items = array_values(array_filter(
				$options->items,
				fn ($item) =>
					$item?->key instanceof String_ === false ||
					$item->key->value !== 'mode'
			));

			$options->items[] = $this->modeItem();
		} else {
			$options = new Array_([
				new ArrayItem(value: $options->value, unpack: true),
				$this->modeItem()
			]);
		}

		$node->name = new Identifier('create');
		$node->args = [new Arg($options)];

		return $node;
	}
}
