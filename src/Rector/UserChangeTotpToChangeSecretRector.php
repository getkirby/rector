<?php

namespace Kirby\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * `User::changeTotp()` is deprecated in Kirby 6
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final class UserChangeTotpToChangeSecretRector extends AbstractKirbyRector
{
	public function getNodeTypes(): array
	{
		return [MethodCall::class];
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Replace `$user->changeTotp($secret)` with `$user->changeSecret(\'totp\', $secret)`',
			[
				new CodeSample(
					<<<'CODE_SAMPLE'
					$user->changeTotp($secret);
					CODE_SAMPLE,
					<<<'CODE_SAMPLE'
					$user->changeSecret('totp', $secret);
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
		if ($this->isName($node->name, 'changeTotp') === false) {
			return null;
		}

		if ($this->isObjectOfTypes($node->var, ['Kirby\Cms\User']) === false) {
			return null;
		}

		$secret = $node->args[0] ?? null;

		if (
			count($node->args) !== 1 ||
			$secret instanceof Arg === false ||
			$secret->unpack === true
		) {
			return null;
		}

		$type = new String_('totp');

		if ($secret->name !== null) {
			$node->args = [
				new Arg(value: $type, name: new Identifier('secret')),
				new Arg(value: $secret->value, name: new Identifier('content'))
			];
		} else {
			array_unshift($node->args, new Arg($type));
		}

		$node->name = new Identifier('changeSecret');

		return $node;
	}
}
