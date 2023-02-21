<?php

/************
 * Wikibase
 ************/

global $wgDBname, $wgScriptPath;

$wgArticlePath = $wgScriptPath . '/index.php?title=$1';

$repoWikis = [
	'wikidatawiki_dev',
];

if ( in_array( $wgDBname, $repoWikis, true ) ) {
	// repo-only config
	wfLoadExtension( 'WikibaseRepository', "$IP/extensions/Wikibase/extension-repo.json" );
	require_once "$IP/extensions/Wikibase/repo/ExampleSettings.php";
	$wgFavicon = 'favicon-repo.ico';
} elseif ( $wgDBname === 'dewiki_dev' ) {
	// client-only config
	$wgLanguageCode = 'de';
	$wgFavicon = 'favicon-client.ico';

	// see also: https://doc.wikimedia.org/Wikibase/master/php/md_docs_topics_options.html#client_siteGlobalID
	$wgWBClientSettings['siteGlobalID'] = 'dewiki_dev';
	$wgWBClientSettings['repoSiteId'] = 'wikidatawiki_dev';
	$wgWBClientSettings['repoSiteName'] = 'Wikidata DEV';
	$wgWBClientSettings['repoUrl'] = '//wikidatawiki_dev.mediawiki.mwdd.localhost:8080';
	$wgWBClientSettings['repoScriptPath'] = $wgScriptPath;
	$wgWBClientSettings['repoArticlePath'] = $wgArticlePath;
}

// both wikidatawiki_dev and dewiki_dev are clients to the repo on wikidatawiki_dev
wfLoadExtension( 'WikibaseClient', "$IP/extensions/Wikibase/extension-client.json" );

// https://doc.wikimedia.org/Wikibase/master/php/md_docs_topics_options.html#common_siteLinkGroups
$wgWBClientSettings['siteLinkGroups'] = [ 'wikipedia', 'mylocalwikis' ];
$wgWBRepoSettings['siteLinkGroups'] = [ 'wikipedia', 'mylocalwikis' ];
// https://doc.wikimedia.org/Wikibase/master/php/md_docs_topics_options.html#autotoc_md330
$wgWBClientSettings['repoSiteId'] = 'wikidatawiki_dev';

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
		'repoDatabase' => 'wikidatawiki_dev',
		'baseUri' => 'https://example.com/entity/',
		'interwikiPrefix' => 'wikidatawiki_dev',
		'rdfNodeNamespacePrefix' => '',
		'rdfPredicateNamespacePrefix' => '',
	],
];
$wgWBRepoSettings['entitySources'] = $entitySources;
$wgWBClientSettings['entitySources'] = $entitySources;

// https://www.mediawiki.org/wiki/Manual:$wgLocalDatabases
// FIXME: docs!
$wgLocalDatabases = [ 'dewiki_dev', 'wikidatawiki_dev' ];
// locally accessibly databases, used for dispatching
// it seems rather optional unless one looks into dispatching specifically
// https://doc.wikimedia.org/Wikibase/master/php/md_docs_topics_options.html#client_localClientDatabases
$wgWBRepoSettings['localClientDatabases'] = [
	'wikidatawiki_dev' => 'wikidatawiki_dev',
	'dewiki_dev' => 'dewiki_dev'
];

/**
 * Configuration to group statements together based on their datatype or other criteria like "propertySet".
 * For example, putting all of external identifiers in one place.
 *
 * See https://doc.wikimedia.org/Wikibase/master/php/docs_topics_options.html#autotoc_md308
 *
 * Section configurations other than 'statements' and 'identifiers' require you
 * to define wikibase-statementsection-* messages for section headings to be rendered correctly.
 */
$wgWBRepoSettings['statementSections'] = [
	'item' => [
		'statements' => null,
		'identifiers' => [
			'type' => 'dataType',
			'dataTypes' => [ 'external-id' ],
		],
	],
	'property' => [
		'statements' => null,
		'constraints' => [
			'type' => 'propertySet',
			'propertyIds' => [ 'P5' ],
		],
	],
	'lexeme' => [
		'statements' => null,
		'identifiers' => [
			'type' => 'dataType',
			'dataTypes' => [ 'external-id' ],
		],
	],
];

/**
 * These settings are used to configure the badges that are available for sitelinks on items.
 */
$wgWBRepoSettings['badgeItems'] = [
	'Q2' => 'wb-badge-goodarticle',
	'Q3' => 'wb-badge-redirect-sitelink',
	'Q4' => 'wb-badge-redirect-intentional-sitelink',
];
$wgWBClientSettings['badgeClassNames'] = [
	'Q2' => 'badge-goodarticle',
];
/**
 * These items are badges that can be added to sitelinks to redirects
 *
 * You can create a redirect by saving a Wikipage with the content `#REDIRECT [[Other page title]]`
 */
$wgWBRepoSettings['redirectBadgeItems'] = [
	'Q3', 'Q4'
];

/**
 * Enables the "new"/mobile Termbox
 *
 * Further configuration is required to make SSR work too
 *
 * See also https://github.com/wikimedia/wikibase-termbox#readme
 */
$wgWBRepoSettings['termboxEnabled'] = true;

/**
 * Tainted references is a small Vue widget that highlights potentially outdated references
 *
 * To see it in action create a statement that also has a reference.
 * Then, in the Item UI, edit the statement, but leave the reference untouched.
 * After saving, you should see an exclamation mark in a circle that triggers a small popup.
 *
 * See also:
 * https://www.wikidata.org/wiki/Wikidata:Mismatched_reference_notification_input
 * https://phabricator.wikimedia.org/project/profile/4242/
 */
$wgWBRepoSettings['taintedReferencesEnabled'] = true;

/************
 * MediaWiki
 ************/
$wgEnableParserCache = true;
// https://www.mediawiki.org/wiki/Manual:Memcached
// https://www.mediawiki.org/wiki/Manual:$wgMemCachedServers
// TODO: how to inspect that cache locally?
$wgMainCacheType = CACHE_MEMCACHED;

/**
 * Destination of statsd metrics.
 *
 * You can access this server at @link http://graphite.mwdd.localhost:8080/
 *
 * TODO: Graphite has been disabled for now, @see T307366
 */
// $wgStatsdServer = 'graphite'; // graphite is broken in mwcli, taking up GigaBytes of space _fast_

/************
 * Skins
 ************/
wfLoadSkin( 'Vector' );
wfLoadSkin( 'MinervaNeue' );
wfLoadExtension( 'MobileFrontend' );
$wgMFAutodetectMobileView = true;

/************
 * Extensions
 ************/

/**
 * ArticlePlaceholder might need some more setup, that is, importing a template and a lua module.
 * See the setup section on https://www.mediawiki.org/wiki/Extension:ArticlePlaceholder
 *
 * It also needs the Scribunto extension.
 *
 * It is enabled only on a few small wikis and likely to be sunset soon.
 *
 * Go to http://wikidatawiki_dev.mediawiki.mwdd.localhost:8080/w/index.php?title=Special:AboutTopic/Q1
 * to see what this extension does. (assuming Q1 exists and has some statements)
 */
wfLoadExtension( 'ArticlePlaceholder' );

/**
 * Scribunto is a Lua extension for MediaWiki. It is probably enabled on all wikis.
 *
 * Needed for the ArticlePlaceholder extension
 * https://www.mediawiki.org/wiki/Extension:Scribunto
 */
wfLoadExtension( 'Scribunto' );
$wgScribuntoDefaultEngine = 'luastandalone';

/**
 * WikimediaBadges mainly provides the CSS to show tiny icons for badges next to sitelinks on client wikis.
 *
 * It goes together with the $wgWBClientSettings['badgeClassNames'] config setting.
 *
 * https://www.mediawiki.org/wiki/Extension:WikimediaBadges
 */
wfLoadExtension( 'WikimediaBadges' );

/**
 * UniversalLanguageSelector is required for the "new"/mobile Termbox to work
 *
 * TODO: add what else UniversalLanguageSelector does for Wikidata and Wikipedias in general
 *
 * https://www.mediawiki.org/wiki/Extension:UniversalLanguageSelector
 */
wfLoadExtension( 'UniversalLanguageSelector' );

//wfLoadExtension( 'AbuseFilter' );
//wfLoadExtension( 'Babel' );
//wfLoadExtension( 'BetaFeatures' );
//wfLoadExtension( 'cldr' );
//wfLoadExtension( 'Echo' );
//wfLoadExtension( 'EventLogging' );
//wfLoadExtension( 'Gadgets' );
//wfLoadExtension( 'Interwiki' );
//wfLoadExtension( 'InterwikiSorting' );
//wfLoadExtension( 'ParserFunctions' );

//wfLoadExtension( 'Cite' );
//wfLoadExtension( 'SpamBlacklist' );
//wfLoadExtension( 'TitleBlacklist' );
//wfLoadExtension( 'WikimediaMessages' );
//wfLoadExtension( 'JsonConfig' ); // dependency for Kartographer extension
//wfLoadExtension( 'Kartographer' );

//$wgGroupPermissions['sysop']['interwiki'] = true;

//wfLoadExtension( 'Gadgets' );
$wgEventLoggingBaseUri = 'http://localhost:8080/event.gif';
$wgEventLoggingFile = '/var/log/mediawiki/events.log';

$wgAllowExternalImages = true;

// debugging stuff
putenv( 'MW_LOG_DIR=/var/log/mediawiki' );
error_reporting( -1 );
ini_set( 'display_errors', 1 );
$wgShowSQLErrors = true;
$wgDebugDumpSql  = true;
$wgShowExceptionDetails = true;
$wgShowDBErrorBacktrace = true;
//$wgDebugToolbar = true; // lots of noise in API requests !?!
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
if ( $wgDBname === 'wikidatawiki_dev' ) {
// repo config
} elseif ( $wgDBname === 'dewiki_dev' ) {
// dewiki_dev config
}
*/


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

//$wgWBClientSettings['dataBridgeHrefRegExp'] = 'https://wikidata\.beta\.wmflabs\.org/wiki/(?:Item:)?(Q[1-9][0-9]*).*#(P[1-9][0-9]*)';

if ( $wgDBname === 'wikidatawiki_dev' ) {
	$wgLexemeLanguageCodePropertyId = 'P6';
	wfLoadExtension( 'WikibaseLexeme' );
	$wgEntitySchemaShExSimpleUrl = 'https://tools.wmflabs.org/shex-simple/wikidata/packages/shex-webapp/doc/shex-simple.html?data=Endpoint: https://query.wikidata.org/sparql&hideData&manifest=[]&textMapIsSparqlQuery';
	$wgEntitySchemaSkippedIDs = [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 42, 123, 999 ];
	wfLoadExtension( 'EntitySchema' );

	$wgWBQualityConstraintsSuggestionsBetaFeature = true;
	wfLoadExtension( 'WikibaseQualityConstraints' );
}

if ( $wgDBname === 'wikidatawiki_dev' ) {
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
