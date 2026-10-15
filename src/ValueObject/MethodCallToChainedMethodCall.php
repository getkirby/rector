<?php

namespace Kirby\Rector\ValueObject;

/**
 * `$object->oldMethod($args)` → `$object->via()->newMethod($args)`
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final readonly class MethodCallToChainedMethodCall
{
	/**
	 * @param array<class-string> $classes
	 */
	public function __construct(
		public array $classes,
		public string $oldMethod,
		public string $via,
		public string $newMethod
	) {
	}
}
