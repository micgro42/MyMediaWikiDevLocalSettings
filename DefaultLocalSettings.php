<?php

/************
 * Wikibase
 ************/

global $wgDBname;

if ( $wgDBname === 'default' ) {
	// repo-only config
	wfLoadExtension( 'WikibaseRepository', "$IP/extensions/Wikibase/extension-repo.json" );
	require_once "$IP/extensions/Wikibase/repo/ExampleSettings.php";
	$wgFavicon = 'favicon-repo.ico';
} elseif ( $wgDBname === 'client' ) {
	// client-only config
	$wgLanguageCode = 'de';
	$wgFavicon = 'favicon-client.ico';

	// see also: https://doc.wikimedia.org/Wikibase/master/php/md_docs_topics_options.html#client_siteGlobalID
	$wgWBClientSettings['siteGlobalID'] = 'client';
}

$wgSkinMetaTags[] = 'og:description';
$wgSkinMetaTags[] = 'description';
$wgSkinMetaTags[] = 'title';
$wgSkinMetaTags[] = 'og:title';
$wgSkinMetaTags[] = 'twitter:card';

// both default and client are clients to the repo on default
wfLoadExtension( 'WikibaseClient', "$IP/extensions/Wikibase/extension-client.json" );

// https://doc.wikimedia.org/Wikibase/master/php/md_docs_topics_options.html#common_siteLinkGroups
$wgWBClientSettings['siteLinkGroups'] = [ 'wikipedia', 'mylocalwikis' ];
$wgWBRepoSettings['siteLinkGroups'] = [ 'wikipedia', 'mylocalwikis' ];
// https://doc.wikimedia.org/Wikibase/master/php/md_docs_topics_options.html#autotoc_md330
$wgWBClientSettings['repoSiteId'] = 'default';

// https://doc.wikimedia.org/Wikibase/master/php/md_docs_topics_options.html#common_entitySources
// https://doc.wikimedia.org/Wikibase/master/php/md_docs_topics_entitysources.html
$entitySources = [
	'local' => [
		'entityNamespaces' => [
			'item' => 120,
			'property' => 122,
			'lexeme' => 146,
//			'mediainfo' => '6/mediainfo',
		],
		'repoDatabase' => 'default',
		'baseUri' => 'https://example.com/entity/',
		'interwikiPrefix' => 'default',
		'rdfNodeNamespacePrefix' => '',
		'rdfPredicateNamespacePrefix' => '',
	],
];
$wgWBRepoSettings['entitySources'] = $entitySources;
$wgWBClientSettings['entitySources'] = $entitySources;

// https://www.mediawiki.org/wiki/Manual:$wgLocalDatabases
// FIXME: docs!
$wgLocalDatabases = [ 'client', 'default' ];
// locally accessibly databases, used for dispatching
// it seems rather optional unless one looks into dispatching specifically
// https://doc.wikimedia.org/Wikibase/master/php/md_docs_topics_options.html#client_localClientDatabases
$wgWBRepoSettings['localClientDatabases'] = [
	'default' => 'default',
	'client' => 'client'
];

/************
 * Skins
 ************/
wfLoadSkin( 'Vector' );
wfLoadSkin( 'MinervaNeue' );
//$wgDefaultSkin = 'minerva';

// Enable these when using mobile Frontend is important
wfLoadExtension( 'MobileFrontend' );
$wgMFAutodetectMobileView = true;

// TODO: check for cirrus search and elastic?
//wfLoadExtension( 'Elastica' );
//wfLoadExtension( 'CirrusSearch' );
//wfLoadExtension( 'WikibaseCirrusSearch' );
//wfLoadExtension( 'WikibaseMediaInfo' );

//wfLoadExtension( 'cldr' );

// how to use: https://www.mediawiki.org/wiki/Help:Extension:ParserFunctions
wfLoadExtension( 'ParserFunctions' );
$wgPFEnableStringFunctions = true;

wfLoadExtension( 'Scribunto' );
$wgScribuntoDefaultEngine = 'luastandalone';
//$wgScribuntoDefaultEngine = 'luasandbox';
$wgNamespaceContentModels[828] = 'Scribunto';

wfLoadExtension( 'UniversalLanguageSelector' );
//wfLoadExtension( 'WikimediaMessages' );

$wgAllowExternalImages = true;

// debugging stuff
putenv( 'MW_LOG_DIR=/var/log/mediawiki' );
error_reporting( -1 );
ini_set( 'display_errors', 1 );
$wgShowSQLErrors = true;
$wgDebugDumpSql  = true;
$wgShowExceptionDetails = true;
$wgShowDBErrorBacktrace = true;
$wgDebugToolbar = true;
$wgDevelopmentWarnings = true;
$wgDockerLogDirectory = '/var/log/mediawiki';
$wgDebugLogGroups = [
	'resourceloader' => '/var/log/mediawiki/resourceloader.log',
	'exception-json' => '/var/log/mediawiki/exception.log',
	'authentication' => '/var/log/mediawiki/authentication.log',
	'authevents' => '/var/log/mediawiki/authevents.log',
	'cors' => '/var/log/mediawiki/cors.log',
	'exec' => '/var/log/mediawiki/exec.log',
	'DBConnection' => '/var/log/mediawiki/dbConnection.log',
	'DBQuery' => '/var/log/mediawiki/dbQuery.log',
	'DBReplication' => '/var/log/mediawiki/dbReplication.log',
	'DBPerformance' => '/var/log/mediawiki/dbPerformance.log',
	'SQLBagOStuff' => '/var/log/mediawiki/dbSQLBagOStuff.log',
	'cookie' => '/var/log/mediawiki/cookie.log',
	'caches' => '/var/log/mediawiki/caches.log',
	'squid' => '/var/log/mediawiki/squid.log',
	'objectcache' => '/var/log/mediawiki/objectcache.log',
	'ParserCache' => '/var/log/mediawiki/parserCache.log',
	'MessageCache' => '/var/log/mediawiki/messageCache.log',
	'TitleBlacklist-cache' => '/var/log/mediawiki/titleBlacklistCache.log',
	'gitinfo' => '/var/log/mediawiki/gitinfo.log',
	'session' => '/var/log/mediawiki/session.log',
	'runJobs' => '/var/log/mediawiki/jobRunner.log',
	'Preprocessor' => '/var/log/mediawiki/preprocessor.log',
	'ContentHandler' => '/var/log/mediawiki/contentHandler.log',
	'GlobalTitleFail' => '/var/log/mediawiki/globalTitleFail.log',
	'Mime' => '/var/log/mediawiki/mime.log',
	'StashEdit' => '/var/log/mediawiki/stashEdit.log',

	// Extra log groups from your extension
	'Wikibase' => [
		'destination' => '/var/log/mediawiki/wikibase.log',
		'level' => \Psr\Log\LogLevel::DEBUG,
	],
	#'somegroup' => '/var/log/mediawiki/somegroup.log',
];
require_once "$IP/includes/DevelopmentSettings.php";

/*
if ( $wgDBname === 'default' ) {
// repo config
} elseif ( $wgDBname === 'client' ) {
// client config
}
*/

//$wgRateLimits['edit']['ip'] = [ 2, 60 ];
//$wgRateLimits['edit']['user'] = [ 2, 60 ];
//$wgRateLimits['edit']['&can-bypass'] = false;

$wgEnableParserCache = true;
//$wgCachePages = false;

// https://www.mediawiki.org/wiki/Manual:Memcached
// https://www.mediawiki.org/wiki/Manual:$wgMemCachedServers
$wgMainCacheType = CACHE_MEMCACHED;

$wgWBRepoSettings['dataRightsText'] = 'Creative Commons CC0 License';
$wgWBRepoSettings['dataRightsUrl'] = 'https://creativecommons.org/publicdomain/zero/1.0/';

//$wgWBClientSettings['useKartographerMaplinkInWikitext'] = true; // T220122
//$wgWBClientSettings['useKartographerGlobeCoordinateFormatter'] = true; // T220122
//$wgKartographerEnableMapFrame = true;

$wgWBClientSettings['dataBridgeEnabled'] = true;
$wgWBRepoSettings['dataBridgeEnabled'] = true;
$wgWBClientSettings['dataBridgeHrefRegExp'] = '[/=]((?:Item:)?(Q[1-9][0-9]*)).*#(P[1-9][0-9]*)$';
$wgWBClientSettings['dataBridgeEditTags'] = [ 'Data Bridge' ];
$wgEditSubmitButtonLabelPublish = false;
//$wgWBClientSettings['dataBridgeHrefRegExp'] = 'http://default\.web\.mw\.localhost:8080/mediawiki/index\.php\?title=(?:Item:)?(Q[1-9][0-9]*).*#(P[1-9][0-9]*)';
//$wgWBClientSettings['dataBridgeHrefRegExp'] = 'https://wikidata\.beta\.wmflabs\.org/wiki/(?:Item:)?(Q[1-9][0-9]*).*#(P[1-9][0-9]*)';

if ( $wgDBname === 'default' ) {
	$wgLexemeLanguageCodePropertyId = 'P21';
	wfLoadExtension( 'WikibaseLexeme' );
	$wgEntitySchemaShExSimpleUrl = 'https://tools.wmflabs.org/shex-simple/wikidata/packages/shex-webapp/doc/shex-simple.html?data=Endpoint: https://query.wikidata.org/sparql&hideData&manifest=[]&textMapIsSparqlQuery';
	$wgEntitySchemaSkippedIDs = [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 42, 123, 999 ];
	wfLoadExtension( 'EntitySchema' );

	$wgWBQualityConstraintsSuggestionsBetaFeature = true;
	wfLoadExtension( 'WikibaseQualityConstraints' );
}

if ( $wgDBname === 'default' ) {
	// https://www.mediawiki.org/wiki/Manual:Hooks/SkinBuildSidebar
	// https://www.mediawiki.org/wiki/Wikibase/Suite#Optional_sidebar
	$wgHooks['SkinBuildSidebar'][] = function( $skin, &$sidebar ) {
		$sidebar['Wikibase'][] = [
			'text' => 'New Item',
			'href' => '?title=Special:NewItem',
		];
		$sidebar['Wikibase'][] = [
			'text' => 'New Property',
			'href' => '?title=Special:NewProperty',
		];
		$sidebar['Wikibase'][] = [
			'text' => 'New Lexeme',
			'href' => '?title=Special:NewLexeme',
		];
		$sidebar['Wikibase'][] = [
			'text' => 'New Schema',
			'href' => '?title=Special:NewEntitySchema',
		];
	};
}
