<?php

namespace Kirby\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * The login methods moved from the system
 * to the auth methods handler in Kirby 6
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final class SystemToAuthMethodsRector extends AbstractKirbyRector
{
	protected const METHODS = [
		'is2FA'        => 'hasAnyRequiring2FA',
		'loginMethods' => 'enabled',
	];

	public function getNodeTypes(): array
	{
		return [MethodCall::class];
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Replace `$system->loginMethods()` and `$system->is2FA()` with the auth methods handler',
			[
				new CodeSample(
					<<<'CODE_SAMPLE'
					$methods = $kirby->system()->loginMethods();
					$is2FA   = $kirby->system()->is2FA();
					CODE_SAMPLE,
					<<<'CODE_SAMPLE'
					$methods = $kirby->auth()->methods()->enabled();
					$is2FA   = $kirby->auth()->methods()->hasAnyRequiring2FA();
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
		$method = $this->getName($node->name);

		if (isset(static::METHODS[$method]) === false) {
			return null;
		}

		if ($this->isObjectOfTypes($node->var, ['Kirby\Cms\System']) === false) {
			return null;
		}

		$app = $this->app($node->var, 'system');

		return new MethodCall(
			var: new MethodCall(
				var: new MethodCall(var: $app, name: 'auth'),
				name: 'methods'
			),
			name: static::METHODS[$method]
		);
	}
}
