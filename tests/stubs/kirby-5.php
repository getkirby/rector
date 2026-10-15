<?php

/**
 * Minimal stubs of the Kirby 5 API, so that PHPStan
 * can resolve the types in the test fixtures
 */

namespace Kirby\Cms {
	class App
	{
		public static function instance(): static
		{
		}
		public function auth(): Auth
		{
		}
		public function sessionHandler(): \Kirby\Session\Sessions
		{
		}
		public function system(): System
		{
		}
		public function user(): User|null
		{
		}
		public function visitor(): \Kirby\Http\Visitor
		{
		}
	}

	class Auth
	{
		public function enabledChallenges(): array
		{
		}
		public function ipHash(): string
		{
		}
		public function isBlocked(string $email): bool
		{
		}
		public function log(): array
		{
		}
		public function logfile(): string
		{
		}
		public function status(): Auth\Status
		{
		}
		public function track(string|null $email, bool $triggerHook = true): bool
		{
		}
	}

	class Blueprint
	{
		public function acceptedFileTemplates(string|null $inSection = null): array
		{
		}
		public static function factory(string $name, string|null $fallback, ModelWithContent $model): static|null
		{
		}
	}

	class File extends ModelWithContent
	{
		public function blueprints(string|null $inSection = null): array
		{
		}
	}

	class FileBlueprint extends Blueprint
	{
		public function acceptMime(): string
		{
		}
	}

	class Language
	{
		public function isLast(): bool
		{
		}
	}

	abstract class ModelWithContent
	{
	}

	class Page extends ModelWithContent
	{
	}

	class PageBlueprint extends Blueprint
	{
	}

	class Response extends \Kirby\Http\Response
	{
	}

	class System
	{
		public function is2FA(): bool
		{
		}
		public function loginMethods(): array
		{
		}
	}

	class User extends ModelWithContent
	{
		public function changeTotp(string|null $secret): static
		{
		}
	}
}

namespace Kirby\Cms\Auth {
	class Status
	{
		public function challenge(bool $automaticFallback = true): string|null
		{
		}
	}
}

namespace Kirby\Exception {
	class Exception extends \Exception
	{
		public function __construct(
			array|string $args = [],
			string|null $key = null,
			array|null $data = null,
			array|null $details = null,
			string|null $fallback = null,
			int|null $httpCode = null,
			string|null $message = null,
			\Throwable|null $previous = null,
			bool $translate = true
		) {
		}
	}

	class ErrorPageException extends Exception
	{
	}
	class InvalidArgumentException extends Exception
	{
	}
	class NotFoundException extends Exception
	{
	}
	class PermissionException extends Exception
	{
	}
}

namespace Kirby\Form {
	class Form
	{
		public function content(): array
		{
		}
		public function values(): array
		{
		}
	}
}

namespace Kirby\Http {
	class Response
	{
		public static function json(string|array $body = '', int|null $code = null, bool|null $pretty = null, array $headers = []): static
		{
		}
	}

	class Visitor
	{
		public function ip(): string|null
		{
		}
	}
}

namespace Kirby\Panel {
	class Dialog
	{
		public static function response($data, array $options = []): \Kirby\Http\Response
		{
		}
	}

	class Home
	{
		public static function isPanelUrl(string $url): bool
		{
		}
		public static function panelPath(string $url): string|null
		{
		}
		public static function url(): string
		{
		}
	}

	class Panel
	{
		public static function isFiberRequest(): bool
		{
		}
		public static function router(string|null $path = null): \Kirby\Http\Response|null
		{
		}
		public static function url(string|null $url = null, array $options = []): string
		{
		}
	}
}

namespace Kirby\Panel\Ui\Buttons {
	class VersionsButton
	{
		public function __construct(\Kirby\Cms\ModelWithContent $model, string $versionId = 'latest')
		{
		}
	}
}

namespace Kirby\Session {
	class AutoSession
	{
		public function __construct(string $store, array $options = [])
		{
		}
		public function createManually(array $options = []): Session
		{
		}
		public function get(array $options = []): Session
		{
		}
		public function getManually(string $token): Session
		{
		}
	}

	class Session
	{
	}

	class Sessions
	{
		public function __construct(string $store, array $options = [])
		{
		}
		public function cookieDomain(): string|null
		{
		}
		public function cookieName(): string
		{
		}
		public function createManually(array $options = []): Session
		{
		}
		public function currentDetected(): Session|null
		{
		}
		public function get(string $token, string|null $mode = null): Session
		{
		}
		public function getManually(string $token): Session
		{
		}
		public function updateCache(Session $session): void
		{
		}
	}
}

namespace Kirby\Toolkit {
	class Str
	{
		public static function float(mixed $value): string
		{
		}
	}
}

namespace Kirby\Uuid {
	interface Identifiable
	{
	}

	class Uuid
	{
		public static function for(string|Identifiable $seed, \Kirby\Cms\Collection|null $context = null): static|null
		{
		}
		public function url(): string
		{
		}
	}
}
