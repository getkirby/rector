<?php

namespace Kirby\Rector\Tests\Set\Kirby6;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class Kirby6SetTest extends AbstractRectorTestCase
{
	public static function provideData(): Iterator
	{
		return self::yieldFilesFromDirectory(__DIR__ . '/Fixture');
	}

	public function provideConfigFilePath(): string
	{
		return __DIR__ . '/config/kirby-6.php';
	}

	#[DataProvider('provideData')]
	public function test(string $filePath): void
	{
		$this->doTestFile($filePath);
	}
}
