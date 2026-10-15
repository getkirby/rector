<?php

namespace Kirby\Rector\ValueObject;

/**
 * `Class::method($args)` → `App::instance()->panel()->a()->b($args)`
 *
 * The `$chain` lists the methods that are called on the panel instance.
 * The last method receives the original arguments. Prefix a method
 * with `?` to call it with the nullsafe operator.
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final readonly class StaticCallToPanelInstance
{
	/**
	 * @param class-string $class
	 * @param array<string> $chain
	 */
	public function __construct(
		public string $class,
		public string $method,
		public array $chain
	) {
	}
}
