<?php

/************
 * Wikibase
 ************/

global $wgDBname, $wgScriptPath;

$wgArticlePath = $wgScriptPath . '/index.php?title=$1';
/*
 * Disable short-url rewrites.
 * https://www.mediawiki.org/wiki/Manual:Short_URL
 * https://www.mediawiki.org/wiki/Manual:$wgUsePathInfo
 *
 * TODO: invest the time to set this up correctly, like on production
 */
$wgUsePathInfo = false;

// add a ci-wikidata wiki here where we load Wikibase.ci.php?
$repoWikis = [
	'wikidatawikidev',
];

if ( in_array( $wgDBname, $repoWikis, true ) ) {
	// repo-only config
	wfLoadExtension( 'WikibaseRepository', "$IP/extensions/Wikibase/extension-repo.json" );

	// These are the values actually defined on production Wikidata
	$wgExtraNamespaces = [
		120 => 'Property',
		121 => 'Property_talk',
		122 => 'Query',
		123 => 'Query_talk',
	];
	$wgNamespaceAliases = [
		'Item' => NS_MAIN,
		'Item_talk' => NS_TALK,
		'WD' => NS_PROJECT,
		'WT' => NS_PROJECT_TALK,
		'P' => 120,
		'L' => 146,
		'E' => 640,
	];

	// FIXME: this is devwikidatawiki specific config!
	$wgMetaNamespace = 'Wikidata';

	$wgFavicon = 'favicon-repo.ico';
} elseif ( $wgDBname === 'dewikidev' ) {
	// client-only config
	$wgLanguageCode = 'de';
	$wgFavicon = 'favicon-client.ico';

	// see also: https://doc.wikimedia.org/Wikibase/master/php/md_docs_topics_options.html#client_siteGlobalID
	$wgWBClientSettings['siteGlobalID'] = 'dewikidev';
	$wgWBClientSettings['repoSiteId'] = 'wikidatawikidev';
	$wgWBClientSettings['repoSiteName'] = 'Wikidata DEV';
	$wgWBClientSettings['repoUrl'] = '//wikidatawikidev.mediawiki.mwdd.localhost:8080';
	$wgWBClientSettings['repoScriptPath'] = $wgScriptPath;
	$wgWBClientSettings['repoArticlePath'] = $wgArticlePath;
}

// https://www.mediawiki.org/wiki/Manual:CORS
// https://www.mediawiki.org/wiki/Manual:$wgCrossSiteAJAXdomains
$wgCrossSiteAJAXdomains = [
	'*.mediawiki.mwdd.localhost:8080'
];

// both wikidatawikidev and dewikidev are clients to the repo on wikidatawikidev
wfLoadExtension( 'WikibaseClient', "$IP/extensions/Wikibase/extension-client.json" );

// https://doc.wikimedia.org/Wikibase/master/php/md_docs_topics_options.html#common_siteLinkGroups
$wgWBClientSettings['siteLinkGroups'] = [ 'wikipedia', 'mylocalwikis' ];
$wgWBRepoSettings['siteLinkGroups'] = [ 'wikipedia', 'mylocalwikis' ];
$wgWBClientSettings['maxSerializedEntitySize'] = 0;
$wgWBRepoSettings['maxSerializedEntitySize'] = 0;
// https://doc.wikimedia.org/Wikibase/master/php/md_docs_topics_options.html#autotoc_md330
$wgWBClientSettings['repoSiteId'] = 'wikidatawikidev';

if ( defined( 'MW_PHPUNIT_TEST' ) ) {
	require_once './extensions/Wikibase/repo/config/Wikibase.ci.php';
	require_once './extensions/Wikibase/client/config/WikibaseClient.ci.php';
}
// https://doc.wikimedia.org/Wikibase/master/php/md_docs_topics_options.html#common_entitySources
// https://doc.wikimedia.org/Wikibase/master/php/md_docs_topics_entitysources.html
$entitySources = [
	'local' => [
		'entityNamespaces' => [
			'item' => 0,
			'property' => 120,
			'lexeme' => 146,
//			'mediainfo' => '6/mediainfo',
		],
		'repoDatabase' => 'wikidatawikidev',
		'baseUri' => 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=Special:EntityData',
		'interwikiPrefix' => 'wikidatawikidev',
		'rdfNodeNamespacePrefix' => 'wddev',
		'rdfPredicateNamespacePrefix' => 'wddev',
	],
];
$wgWBRepoSettings['entitySources'] = $entitySources;
if ( $wgDBname === 'dewikidev' ) {
	// If this is configured for repo, then it breaks tests that overwrite this setting
	$wgWBClientSettings['entitySources'] = $entitySources;
}

// https://www.mediawiki.org/wiki/Manual:$wgLocalDatabases
// FIXME: docs!
$wgLocalDatabases = [ 'dewikidev', 'wikidatawikidev' ];
// locally accessibly databases, used for dispatching
// it seems rather optional unless one looks into dispatching specifically
// https://doc.wikimedia.org/Wikibase/master/php/md_docs_topics_options.html#client_localClientDatabases
$wgWBRepoSettings['localClientDatabases'] = [
	'wikidatawikidev' => 'wikidatawikidev',
	'dewikidev' => 'dewikidev'
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

$wgWBRepoSettings['sendEchoNotification'] = true;
$wgWBClientSettings['sendEchoNotification'] = true;

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
 * Go to http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=Special:AboutTopic/Q1
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

/*
 * Make parser functions like `#ifeq:` work
 * https://www.mediawiki.org/wiki/Help:Extension:ParserFunctions
 */
wfLoadExtension( 'ParserFunctions' );

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
	'memcached' => '/var/log/mediawiki/memcached.log',
	'rdbms' => '/var/log/mediawiki/rdbms.log',
	'localisation' => '/var/log/mediawiki/localisation.log',

	// Extra log groups from your extension
	'Wikibase' => [
		'destination' => '/var/log/mediawiki/wikibase.log',
		'level' => \Psr\Log\LogLevel::DEBUG,
	],
	#'somegroup' => '/var/log/mediawiki/somegroup.log',
];
require_once "$IP/includes/DevelopmentSettings.php";

/*
if ( $wgDBname === 'wikidatawikidev' ) {
// repo config
} elseif ( $wgDBname === 'dewikidev' ) {
// dewikidev config
}
*/

/*
 * Allows use of ?uselang=x-xss to check for XSS vulnerabilities in i18n messages
 * https://www.mediawiki.org/wiki/Manual:$wgUseXssLanguage
 */
$wgUseXssLanguage = true;

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
$wgTmpDirectory = '/tmp';
if ( $wgDBname === 'wikidatawikidev' ) {
	$wgLexemeLanguageCodePropertyId = 'P6';
	wfLoadExtension( 'WikibaseLexeme' );
	$wgEntitySchemaShExSimpleUrl = 'https://tools.wmflabs.org/shex-simple/wikidata/packages/shex-webapp/doc/shex-simple.html?data=Endpoint:%20https://query.wikidata.org/sparql&hideData&manifest=[]&textMapIsSparqlQuery';
	wfLoadExtension( 'EntitySchema' );

	$wgWBQualityConstraintsSuggestionsBetaFeature = true;
	wfLoadExtension( 'WikibaseQualityConstraints' );
	if ( file_exists( __DIR__ . '/WDQCPropertySettings.php' ) ) {
		require_once __DIR__ . '/WDQCPropertySettings.php';
	}

	// FIXME: describe!
	wfLoadExtension( 'Wikidata.org' );

	/*
	 * Wikibase REST Api
	 *
	 * run in Wikibase root dir `API_URL='http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/rest.php' npm run doc:rest-api`
	 * then open http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/extensions/Wikibase/docs/rest-api/index.html
	 *
	 * Edit \Wikibase\Repo\RestApi\RouteHandlers\Middleware\UnexpectedErrorHandlerMiddleware to see internal errors
	 */
	$wgEnableRestAPI = true;
	$wgRestAPIAdditionalRouteFiles[] = 'extensions/Wikibase/repo/rest-api/routes.json';
	$wgRestAPIAdditionalRouteFiles[] = 'extensions/Wikibase/repo/rest-api/routes.dev.json';
}

if ( $wgDBname === 'wikidatawikidev' ) {
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
