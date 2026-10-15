<?php

use Kirby\Rector\Set\KirbySetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
	->withAutoloadPaths([__DIR__ . '/../../../stubs'])
	->withSets([KirbySetList::KIRBY_6]);
