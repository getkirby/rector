<?php

namespace Kirby\Rector\Rector;

use InvalidArgumentException;
use Kirby\Rector\ValueObject\MethodCallToChainedMethodCall;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Methods that moved to a sub-object, e.g.
 * `$auth->isBlocked()` → `$auth->limits()->isBlocked()`
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final class MethodCallToChainedMethodCallRector extends AbstractKirbyRector implements ConfigurableRectorInterface
{
	/**
	 * @var array<MethodCallToChainedMethodCall>
	 */
	protected array $calls = [];

	public function configure(array $configuration): void
	{
		foreach ($configuration as $call) {
			if ($call instanceof MethodCallToChainedMethodCall === false) {
				throw new InvalidArgumentException(
					'Expected instances of ' . MethodCallToChainedMethodCall::class
				);
			}
		}

		$this->calls = $configuration;
	}

	public function getNodeTypes(): array
	{
		return [MethodCall::class, NullsafeMethodCall::class];
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Move method calls to a sub-object',
			[
				new ConfiguredCodeSample(
					<<<'CODE_SAMPLE'
					$kirby->auth()->isBlocked($email);
					CODE_SAMPLE,
					<<<'CODE_SAMPLE'
					$kirby->auth()->limits()->isBlocked($email);
					CODE_SAMPLE,
					[
						new MethodCallToChainedMethodCall(
							classes: ['Kirby\Auth\Auth'],
							oldMethod: 'isBlocked',
							via: 'limits',
							newMethod: 'isBlocked'
						)
					]
				)
			]
		);
	}

	/**
	 * @param MethodCall|NullsafeMethodCall $node
	 */
	public function refactor(Node $node): Node|null
	{
		foreach ($this->calls as $call) {
			if ($this->isName($node->name, $call->oldMethod) === false) {
				continue;
			}

			if ($this->isObjectOfTypes($node->var, $call->classes) === false) {
				continue;
			}

			$via = $node instanceof NullsafeMethodCall
				? new NullsafeMethodCall(var: $node->var, name: $call->via)
				: new MethodCall(var: $node->var, name: $call->via);

			return new MethodCall(
				var: $via,
				name: $call->newMethod,
				args: $node->args
			);
		}

		return null;
	}
}
