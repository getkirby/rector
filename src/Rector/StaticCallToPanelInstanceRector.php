<?php

namespace Kirby\Rector\Rector;

use InvalidArgumentException;
use Kirby\Rector\ValueObject\StaticCallToPanelInstance;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * `Kirby\Panel\Panel` and `Kirby\Panel\Home` are no longer static
 * in Kirby 6. The instance is available via `$kirby->panel()`.
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final class StaticCallToPanelInstanceRector extends AbstractKirbyRector implements ConfigurableRectorInterface
{
	/**
	 * @var array<StaticCallToPanelInstance>
	 */
	protected array $calls = [];

	protected function chain(StaticCall $node, StaticCallToPanelInstance $call): Expr
	{
		$expr = new MethodCall(
			var: new StaticCall(
				class: new FullyQualified('Kirby\Cms\App'),
				name: 'instance'
			),
			name: 'panel'
		);

		$last = array_key_last($call->chain);

		foreach ($call->chain as $index => $method) {
			$args = $index === $last ? $node->args : [];

			$expr = match (str_starts_with($method, '?')) {
				true  => new NullsafeMethodCall(var: $expr, name: substr($method, 1), args: $args),
				false => new MethodCall(var: $expr, name: $method, args: $args)
			};
		}

		return $expr;
	}

	public function configure(array $configuration): void
	{
		foreach ($configuration as $call) {
			if ($call instanceof StaticCallToPanelInstance === false) {
				throw new InvalidArgumentException(
					'Expected instances of ' . StaticCallToPanelInstance::class
				);
			}
		}

		$this->calls = $configuration;
	}

	public function getNodeTypes(): array
	{
		return [StaticCall::class];
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Replace static Panel calls with calls on the Panel instance',
			[
				new ConfiguredCodeSample(
					<<<'CODE_SAMPLE'
					$url = Panel::url('pages/blog');
					CODE_SAMPLE,
					<<<'CODE_SAMPLE'
					$url = \Kirby\Cms\App::instance()->panel()->url('pages/blog');
					CODE_SAMPLE,
					[
						new StaticCallToPanelInstance(
							class: 'Kirby\Panel\Panel',
							method: 'url',
							chain: ['url']
						)
					]
				)
			]
		);
	}

	/**
	 * @param StaticCall $node
	 */
	public function refactor(Node $node): Node|null
	{
		foreach ($this->calls as $call) {
			if ($this->isName($node->name, $call->method) === false) {
				continue;
			}

			if ($this->isClassName($node->class, [$call->class], subclasses: false) === false) {
				continue;
			}

			return $this->chain($node, $call);
		}

		return null;
	}
}
