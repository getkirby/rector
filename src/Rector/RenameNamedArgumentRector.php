<?php

namespace Kirby\Rector\Rector;

use InvalidArgumentException;
use Kirby\Rector\ValueObject\RenameNamedArgument;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Renames named arguments of parameters
 * that have been renamed in Kirby 6
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final class RenameNamedArgumentRector extends AbstractKirbyRector implements ConfigurableRectorInterface
{
	/**
	 * @var array<RenameNamedArgument>
	 */
	protected array $renames = [];

	public function configure(array $configuration): void
	{
		foreach ($configuration as $rename) {
			if ($rename instanceof RenameNamedArgument === false) {
				throw new InvalidArgumentException(
					'Expected instances of ' . RenameNamedArgument::class
				);
			}
		}

		$this->renames = $configuration;
	}

	public function getNodeTypes(): array
	{
		return [
			MethodCall::class,
			New_::class,
			NullsafeMethodCall::class,
			StaticCall::class
		];
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Rename named arguments',
			[
				new ConfiguredCodeSample(
					<<<'CODE_SAMPLE'
					$file->blueprints(inSection: 'gallery');
					CODE_SAMPLE,
					<<<'CODE_SAMPLE'
					$file->blueprints(inField: 'gallery');
					CODE_SAMPLE,
					[
						new RenameNamedArgument(
							classes: ['Kirby\Cms\File'],
							method: 'blueprints',
							oldName: 'inSection',
							newName: 'inField'
						)
					]
				)
			]
		);
	}

	protected function matches(
		MethodCall|New_|NullsafeMethodCall|StaticCall $node,
		RenameNamedArgument $rename
	): bool {
		if ($node instanceof New_) {
			return
				$rename->method === '__construct' &&
				$this->isClassName($node->class, $rename->classes) === true;
		}

		if ($this->isName($node->name, $rename->method) === false) {
			return false;
		}

		if ($node instanceof StaticCall) {
			return $this->isClassName($node->class, $rename->classes);
		}

		return $this->isObjectOfTypes($node->var, $rename->classes);
	}

	/**
	 * @param MethodCall|New_|NullsafeMethodCall|StaticCall $node
	 */
	public function refactor(Node $node): Node|null
	{
		if ($node->isFirstClassCallable() === true) {
			return null;
		}

		$changed = false;

		foreach ($this->renames as $rename) {
			if ($this->matches($node, $rename) === false) {
				continue;
			}

			foreach ($node->args as $arg) {
				if (
					$arg instanceof Arg &&
					$arg->name !== null &&
					$this->isName($arg->name, $rename->oldName) === true
				) {
					$arg->name = new Identifier($rename->newName);
					$changed   = true;
				}
			}
		}

		return $changed === true ? $node : null;
	}
}
