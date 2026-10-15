<?php

namespace Kirby\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * `Auth::ipHash()` has been removed in Kirby 6
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final class AuthIpHashToVisitorIpRector extends AbstractKirbyRector
{
	public function getNodeTypes(): array
	{
		return [MethodCall::class];
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Replace `$auth->ipHash()` with `$visitor->ip(hash: true)`',
			[
				new CodeSample(
					<<<'CODE_SAMPLE'
					$hash = $kirby->auth()->ipHash();
					CODE_SAMPLE,
					<<<'CODE_SAMPLE'
					$hash = $kirby->visitor()->ip(hash: true);
					CODE_SAMPLE
				)
			]
		);
	}

	/**
	 * @param MethodCall $node
	 */
	public function refactor(Node $node): Node|null
	{
		if ($this->isName($node->name, 'ipHash') === false) {
			return null;
		}

		$classes = ['Kirby\Cms\Auth', 'Kirby\Auth\Auth'];

		if ($this->isObjectOfTypes($node->var, $classes) === false) {
			return null;
		}

		return new MethodCall(
			var: new MethodCall(
				var: $this->app($node->var, 'auth'),
				name: 'visitor'
			),
			name: 'ip',
			args: [
				new Arg(
					value: new ConstFetch(new Name('true')),
					name: new Identifier('hash')
				)
			]
		);
	}
}
