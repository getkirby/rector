<?php

use Kirby\Rector\Rector\AuthIpHashToVisitorIpRector;
use Kirby\Rector\Rector\BlueprintFactoryArgumentOrderRector;
use Kirby\Rector\Rector\ExceptionArrayArgumentToNamedArgumentsRector;
use Kirby\Rector\Rector\MethodCallToChainedMethodCallRector;
use Kirby\Rector\Rector\NewSessionsToFactoryRector;
use Kirby\Rector\Rector\RenameNamedArgumentRector;
use Kirby\Rector\Rector\ResponseJsonPrettyNullToFalseRector;
use Kirby\Rector\Rector\SessionsCreateManuallyToCreateRector;
use Kirby\Rector\Rector\SessionsGetWithTokenToFindRector;
use Kirby\Rector\Rector\StaticCallToPanelInstanceRector;
use Kirby\Rector\Rector\StrFloatNullCoalesceRector;
use Kirby\Rector\Rector\SystemToAuthMethodsRector;
use Kirby\Rector\Rector\UserChangeTotpToChangeSecretRector;
use Kirby\Rector\Rector\UuidForStringToFromRector;
use Kirby\Rector\ValueObject\MethodCallToChainedMethodCall;
use Kirby\Rector\ValueObject\RenameNamedArgument;
use Kirby\Rector\ValueObject\StaticCallToPanelInstance;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameStaticMethod;

/**
 * Upgrades Kirby 5 code to Kirby 6
 *
 * Covers the breaking changes and deprecations of Kirby 6
 * that can be migrated automatically. See the Kirby 6
 * changelog for everything else.
 */
return static function (RectorConfig $rectorConfig): void {
	/**
	 * Custom rules
	 */
	$rectorConfig->rules([
		AuthIpHashToVisitorIpRector::class,
		BlueprintFactoryArgumentOrderRector::class,
		ExceptionArrayArgumentToNamedArgumentsRector::class,
		NewSessionsToFactoryRector::class,
		ResponseJsonPrettyNullToFalseRector::class,
		SessionsCreateManuallyToCreateRector::class,
		SessionsGetWithTokenToFindRector::class,
		StrFloatNullCoalesceRector::class,
		SystemToAuthMethodsRector::class,
		UserChangeTotpToChangeSecretRector::class,
		UuidForStringToFromRector::class,
	]);

	/**
	 * Methods that moved to a sub-object
	 */
	$auth = ['Kirby\Auth\Auth', 'Kirby\Cms\Auth'];

	$rectorConfig->ruleWithConfiguration(MethodCallToChainedMethodCallRector::class, [
		new MethodCallToChainedMethodCall($auth, 'enabledChallenges', 'challenges', 'enabled'),
		new MethodCallToChainedMethodCall($auth, 'isBlocked', 'limits', 'isBlocked'),
		new MethodCallToChainedMethodCall($auth, 'log', 'limits', 'log'),
		new MethodCallToChainedMethodCall($auth, 'logfile', 'limits', 'file'),
		new MethodCallToChainedMethodCall($auth, 'track', 'limits', 'track'),
		new MethodCallToChainedMethodCall(['Kirby\Session\Sessions'], 'cookieDomain', 'cookie', 'domain'),
		new MethodCallToChainedMethodCall(['Kirby\Session\Sessions'], 'cookieName', 'cookie', 'name'),
	]);

	/**
	 * Static Panel methods that are now
	 * called on the Panel instance
	 */
	$panel = 'Kirby\Panel\Panel';
	$home  = 'Kirby\Panel\Home';

	$rectorConfig->ruleWithConfiguration(StaticCallToPanelInstanceRector::class, [
		new StaticCallToPanelInstance($home, 'hasValidDomain', ['home', 'hasValidDomain']),
		new StaticCallToPanelInstance($home, 'isPanelUrl', ['isPanelUrl']),
		new StaticCallToPanelInstance($home, 'panelPath', ['path']),
		new StaticCallToPanelInstance($home, 'remembered', ['home', 'remembered']),
		new StaticCallToPanelInstance($home, 'url', ['home', 'url']),
		new StaticCallToPanelInstance($panel, 'isFiberRequest', ['isStateRequest']),
		new StaticCallToPanelInstance($panel, 'multilang', ['multilang']),
		new StaticCallToPanelInstance($panel, 'referrer', ['referrer']),
		new StaticCallToPanelInstance($panel, 'router', ['router', '?call']),
		new StaticCallToPanelInstance($panel, 'url', ['url']),
	]);

	/**
	 * Renamed parameters
	 */
	$rectorConfig->ruleWithConfiguration(RenameNamedArgumentRector::class, [
		new RenameNamedArgument(['Kirby\Auth\Status', 'Kirby\Cms\Auth\Status'], 'challenge', 'automaticFallback', 'fallback'),
		new RenameNamedArgument(['Kirby\Blueprint\AcceptRules'], 'fileTemplates', 'inSection', 'inField'),
		new RenameNamedArgument(['Kirby\Blueprint\Blueprint', 'Kirby\Cms\Blueprint'], 'acceptedFileTemplates', 'inSection', 'inField'),
		new RenameNamedArgument(['Kirby\Cms\File'], 'blueprints', 'inSection', 'inField'),
		new RenameNamedArgument(['Kirby\Panel\Ui\Button\VersionsButton', 'Kirby\Panel\Ui\Buttons\VersionsButton'], '__construct', 'versionId', 'mode'),
	]);

	/**
	 * Renamed methods
	 */
	$rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
		new MethodCallRename('Kirby\Blueprint\FileBlueprint', 'acceptMime', 'acceptAttribute'),
		new MethodCallRename('Kirby\Cms\App', 'sessionHandler', 'sessions'),
		new MethodCallRename('Kirby\Cms\FileBlueprint', 'acceptMime', 'acceptAttribute'),
		new MethodCallRename('Kirby\Cms\Language', 'isLast', 'isLastLanguage'),
		new MethodCallRename('Kirby\Form\Form', 'content', 'toStoredValues'),
		new MethodCallRename('Kirby\Form\Form', 'values', 'toFormValues'),
		new MethodCallRename('Kirby\Session\AutoSession', 'getManually', 'find'),
		new MethodCallRename('Kirby\Session\Sessions', 'currentDetected', 'detect'),
		new MethodCallRename('Kirby\Session\Sessions', 'getManually', 'find'),
		new MethodCallRename('Kirby\Session\Sessions', 'updateCache', 'track'),
		new MethodCallRename('Kirby\Uuid\Uuid', 'url', 'toPermalink'),
	]);

	/**
	 * Panel response classes
	 */
	$rectorConfig->ruleWithConfiguration(RenameStaticMethodRector::class, [
		new RenameStaticMethod('Kirby\Panel\Dialog', 'response', 'Kirby\Panel\Response\DialogResponse', 'from'),
		new RenameStaticMethod('Kirby\Panel\Drawer', 'response', 'Kirby\Panel\Response\DrawerResponse', 'from'),
		new RenameStaticMethod('Kirby\Panel\Dropdown', 'response', 'Kirby\Panel\Response\DropdownResponse', 'from'),
		new RenameStaticMethod('Kirby\Panel\Json', 'response', 'Kirby\Panel\Response\JsonResponse', 'from'),
		new RenameStaticMethod('Kirby\Panel\Request', 'response', 'Kirby\Panel\Response\RequestResponse', 'from'),
		new RenameStaticMethod('Kirby\Panel\Search', 'response', 'Kirby\Panel\Response\SearchResponse', 'from'),
	]);

	/**
	 * Moved and renamed classes
	 */
	$rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
		// api
		'Kirby\Cms\Api' => 'Kirby\Api\Api',

		// auth
		'Kirby\Cms\Auth'        => 'Kirby\Auth\Auth',
		'Kirby\Cms\Auth\Status' => 'Kirby\Auth\Status',

		// blueprints
		'Kirby\Cms\Blueprint'     => 'Kirby\Blueprint\Blueprint',
		'Kirby\Cms\FileBlueprint' => 'Kirby\Blueprint\FileBlueprint',
		'Kirby\Cms\PageBlueprint' => 'Kirby\Blueprint\PageBlueprint',
		'Kirby\Cms\SiteBlueprint' => 'Kirby\Blueprint\SiteBlueprint',
		'Kirby\Cms\UserBlueprint' => 'Kirby\Blueprint\UserBlueprint',

		// forms
		'Kirby\Field\FieldOptions' => 'Kirby\Form\FieldOptions',

		// panel
		'Kirby\Panel\ChangesDialog'                      => 'Kirby\Panel\Controller\Dialog\ChangesDialogController',
		'Kirby\Panel\PageCreateDialog'                   => 'Kirby\Panel\Controller\Dialog\PageCreateDialogController',
		'Kirby\Panel\Ui\Buttons\LanguageCreateButton'    => 'Kirby\Panel\Ui\Button\LanguageCreateButton',
		'Kirby\Panel\Ui\Buttons\LanguageDeleteButton'    => 'Kirby\Panel\Ui\Button\LanguageDeleteButton',
		'Kirby\Panel\Ui\Buttons\LanguageSettingsButton'  => 'Kirby\Panel\Ui\Button\LanguageSettingsButton',
		'Kirby\Panel\Ui\Buttons\LanguagesDropdown'       => 'Kirby\Panel\Ui\Button\LanguagesButton',
		'Kirby\Panel\Ui\Buttons\OpenButton'              => 'Kirby\Panel\Ui\Button\OpenButton',
		'Kirby\Panel\Ui\Buttons\PageStatusButton'        => 'Kirby\Panel\Ui\Button\PageStatusButton',
		'Kirby\Panel\Ui\Buttons\PreviewButton'           => 'Kirby\Panel\Ui\Button\PreviewButton',
		'Kirby\Panel\Ui\Buttons\SettingsButton'          => 'Kirby\Panel\Ui\Button\SettingsButton',
		'Kirby\Panel\Ui\Buttons\VersionsButton'          => 'Kirby\Panel\Ui\Button\VersionsButton',
		'Kirby\Panel\Ui\Buttons\ViewButton'              => 'Kirby\Panel\Ui\Button\ViewButton',
		'Kirby\Panel\Ui\Buttons\ViewButtons'             => 'Kirby\Panel\Ui\Button\ViewButtons',
		'Kirby\Panel\Ui\FilePreviews\AudioFilePreview'   => 'Kirby\Panel\Ui\FilePreview\AudioFilePreview',
		'Kirby\Panel\Ui\FilePreviews\DefaultFilePreview' => 'Kirby\Panel\Ui\FilePreview\DefaultFilePreview',
		'Kirby\Panel\Ui\FilePreviews\ImageFilePreview'   => 'Kirby\Panel\Ui\FilePreview\ImageFilePreview',
		'Kirby\Panel\Ui\FilePreviews\PdfFilePreview'     => 'Kirby\Panel\Ui\FilePreview\PdfFilePreview',
		'Kirby\Panel\Ui\FilePreviews\VideoFilePreview'   => 'Kirby\Panel\Ui\FilePreview\VideoFilePreview',

		// sessions
		'Kirby\Session\AutoSession'      => 'Kirby\Session\Sessions',
		'Kirby\Session\FileSessionStore' => 'Kirby\Session\FileStore',
		'Kirby\Session\SessionData'      => 'Kirby\Session\Data',
		'Kirby\Session\SessionStore'     => 'Kirby\Session\Store',

		// deprecated aliases from earlier versions
		'Kirby\Cms\Asset'          => 'Kirby\Filesystem\Asset',
		'Kirby\Cms\Content'        => 'Kirby\Content\Content',
		'Kirby\Cms\Dir'            => 'Kirby\Filesystem\Dir',
		'Kirby\Cms\Field'          => 'Kirby\Content\Field',
		'Kirby\Cms\FileFoundation' => 'Kirby\Filesystem\IsFile',
		'Kirby\Cms\Filename'       => 'Kirby\Filesystem\Filename',
		'Kirby\Cms\Form'           => 'Kirby\Form\Form',
		'Kirby\Cms\KirbyTag'       => 'Kirby\Text\KirbyTag',
		'Kirby\Cms\KirbyTags'      => 'Kirby\Text\KirbyTags',
		'Kirby\Cms\Plugin'         => 'Kirby\Plugin\Plugin',
		'Kirby\Cms\PluginAsset'    => 'Kirby\Plugin\Asset',
		'Kirby\Cms\PluginAssets'   => 'Kirby\Plugin\Assets',
		'Kirby\Cms\Template'       => 'Kirby\Template\Template',
		'Kirby\Form\Options'       => 'Kirby\Option\Options',
		'Kirby\Form\OptionsApi'    => 'Kirby\Option\OptionsApi',
		'Kirby\Form\OptionsQuery'  => 'Kirby\Option\OptionsQuery',
		'Kirby\Toolkit\Dir'        => 'Kirby\Filesystem\Dir',
		'Kirby\Toolkit\F'          => 'Kirby\Filesystem\F',
		'Kirby\Toolkit\File'       => 'Kirby\Filesystem\File',
		'Kirby\Toolkit\Mime'       => 'Kirby\Filesystem\Mime',
		'Kirby\Toolkit\Query'      => 'Kirby\Query\Query',
	]);
};
