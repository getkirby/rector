<?php

namespace Kirby\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * `Sessions::get()` no longer accepts a token in Kirby 6
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final class SessionsGetWithTokenToFindRector extends AbstractKirbyRector
{
	public function getNodeTypes(): array
	{
		return [MethodCall::class];
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Replace `$sessions->get($token)` with `$sessions->find($token)`',
			[
				new CodeSample(
					<<<'CODE_SAMPLE'
					$session = $kirby->sessionHandler()->get($token);
					CODE_SAMPLE,
					<<<'CODE_SAMPLE'
					$session = $kirby->sessionHandler()->find($token);
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
		if ($this->isName($node->name, 'get') === false) {
			return null;
		}

		if ($this->isObjectOfTypes($node->var, ['Kirby\Session\Sessions']) === false) {
			return null;
		}

		$token = $node->args[0] ?? null;

		if (
			$token instanceof Arg === false ||
			$token->unpack === true ||
			($token->name !== null && $this->isName($token->name, 'token') === false) ||
			$this->getType($token->value)->isArray()->no() === false
		) {
			return null;
		}

		$node->name = new Identifier('find');
		return $node;
	}
}
