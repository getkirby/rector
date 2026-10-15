<?php

namespace Kirby\Rector\ValueObject;

/**
 * `$object->method(oldName: $value)` → `$object->method(newName: $value)`
 *
 * Use `__construct` as method to rename arguments of `new` expressions.
 *
 * @copyright Bastian Allgeier
 * @license   MIT
 */
final readonly class RenameNamedArgument
{
	/**
	 * @param array<class-string> $classes
	 */
	public function __construct(
		public array $classes,
		public string $method,
		public string $oldName,
		public string $newName
	) {
	}
}
