#!/usr/bin/env zsh
set -xe

# initial creation
mw docker mediawiki create
mw docker mysql create
# mw docker mysql-replica create # replica is creating all the problems
mw docker phpmyadmin create
mw docker memcached create
# graphite has been disabled for now, see T307366
# mw docker graphite create # available at http://graphite.mwdd.localhost:8080/

# install repo
mw docker mediawiki install --dbtype=mysql --dbname=wikidatawikidev
# tell the repo about itself
mw docker mediawiki exec -- php maintenance/run.php AddSite --wiki wikidatawikidev wikidatawikidev mylocalwikis --interwiki-id wikidatawikidev --navigation-id wikidatawikidev --pagepath 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/$1' --language en --server 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080'

# install dewikidev
mw docker mediawiki install --dbtype=mysql --dbname=dewikidev
# tell the dewikidev about itself
# note here and in the future: the interwiki id _must_ be the language code due to T137537 and \Wikibase\Client\Hooks\LangLinkHandler::getInterwikiCodeFromSite
mw docker mediawiki exec -- php maintenance/run.php AddSite --wiki dewikidev dewikidev mylocalwikis --interwiki-id de --navigation-id dewikidev --pagepath 'http://dewikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://dewikidev.mediawiki.mwdd.localhost:8080/w/$1' --language de --server 'http://dewikidev.mediawiki.mwdd.localhost:8080'
# tell the repo about the dewikidev wiki
mw docker mediawiki exec -- php maintenance/run.php AddSite --wiki wikidatawikidev dewikidev mylocalwikis --interwiki-id de --navigation-id dewikidev --pagepath 'http://dewikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://dewikidev.mediawiki.mwdd.localhost:8080/w/$1' --language de --server 'http://dewikidev.mediawiki.mwdd.localhost:8080'
# tell the dewikidev about the repo wiki
mw docker mediawiki exec -- php maintenance/run.php AddSite --wiki dewikidev wikidatawikidev mylocalwikis --interwiki-id wikidatawikidev --navigation-id wikidatawikidev --pagepath 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/$1' --language en --server 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080'

# install eowikidev
mw docker mediawiki install --dbtype=mysql --dbname=eowikidev
# tell the eowikidev about itself
mw docker mediawiki exec -- php maintenance/run.php AddSite --wiki eowikidev eowikidev mylocalwikis --interwiki-id eo --navigation-id eowikidev --pagepath 'http://eowikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://eowikidev.mediawiki.mwdd.localhost:8080/w/$1' --language eo --server 'http://eowikidev.mediawiki.mwdd.localhost:8080'
# tell the repo about the eowikidev wiki
mw docker mediawiki exec -- php maintenance/run.php AddSite --wiki wikidatawikidev eowikidev mylocalwikis --interwiki-id eo --navigation-id eowikidev --pagepath 'http://eowikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://eowikidev.mediawiki.mwdd.localhost:8080/w/$1' --language eo --server 'http://eowikidev.mediawiki.mwdd.localhost:8080'
# tell the eowikidev about the repo wiki
mw docker mediawiki exec -- php maintenance/run.php AddSite --wiki eowikidev wikidatawikidev mylocalwikis --interwiki-id wikidatawikidev --navigation-id wikidatawikidev --pagepath 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/$1' --language en --server 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080'
# tell the eowikidev about the de wiki
mw docker mediawiki exec -- php maintenance/run.php AddSite --wiki eowikidev dewikidev mylocalwikis --interwiki-id de --navigation-id dewikidev --pagepath 'http://dewikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://dewikidev.mediawiki.mwdd.localhost:8080/w/$1' --language de --server 'http://dewikidev.mediawiki.mwdd.localhost:8080'
# tell the dewikidev about the eo wiki
mw docker mediawiki exec -- php maintenance/run.php AddSite --wiki dewikidev eowikidev mylocalwikis --interwiki-id eo --navigation-id eowikidev --pagepath 'http://eowikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://eowikidev.mediawiki.mwdd.localhost:8080/w/$1' --language eo --server 'http://eowikidev.mediawiki.mwdd.localhost:8080'

# add interwiki prefixes so that the sitelinks in the sidebar work
mw docker mediawiki exec -- php maintenance/run.php MwSql --wiki wikidatawikidev --status --query "INSERT INTO interwiki (iw_prefix, iw_url, iw_local, iw_trans, iw_api, iw_wikiid) VALUES ('de', 'http://dewikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=\$1', 0, 0, 'http://dewikidev.mediawiki.mwdd.localhost:8080/w/api.php', 'dewikidev');"
mw docker mediawiki exec -- php maintenance/run.php MwSql --wiki wikidatawikidev --status --query "INSERT INTO interwiki (iw_prefix, iw_url, iw_local, iw_trans, iw_api, iw_wikiid) VALUES ('eo', 'http://eowikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=\$1', 0, 0, 'http://eowikidev.mediawiki.mwdd.localhost:8080/w/api.php', 'eowikidev');"
mw docker mediawiki exec -- php maintenance/run.php MwSql --wiki dewikidev --status --query "INSERT INTO interwiki (iw_prefix, iw_url, iw_local, iw_trans, iw_api, iw_wikiid) VALUES ('wikidatawikidev', 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=\$1', 0, 0, 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/api.php', 'wikidatawikidev');"
mw docker mediawiki exec -- php maintenance/run.php MwSql --wiki eowikidev --status --query "INSERT INTO interwiki (iw_prefix, iw_url, iw_local, iw_trans, iw_api, iw_wikiid) VALUES ('wikidatawikidev', 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=\$1', 0, 0, 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/api.php', 'wikidatawikidev');"
mw docker mediawiki exec -- php maintenance/run.php MwSql --wiki dewikidev --status --query "INSERT INTO interwiki (iw_prefix, iw_url, iw_local, iw_trans, iw_api, iw_wikiid) VALUES ('eo', 'http://eowikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=\$1', 0, 0, 'http://eowikidev.mediawiki.mwdd.localhost:8080/w/api.php', 'eowikidev');"
mw docker mediawiki exec -- php maintenance/run.php MwSql --wiki eowikidev --status --query "INSERT INTO interwiki (iw_prefix, iw_url, iw_local, iw_trans, iw_api, iw_wikiid) VALUES ('de', 'http://dewikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=\$1', 0, 0, 'http://dewikidev.mediawiki.mwdd.localhost:8080/w/api.php', 'dewikidev');"

# create "Data Bridge" tag
mw docker mediawiki exec -- php maintenance/run.php AddChangeTag --wiki wikidatawikidev --tag 'Data Bridge' --reason 'added by createWikis.sh'

# create bot passwords to use with API below
mw docker mediawiki exec -- php maintenance/run.php CreateBotPassword --wiki wikidatawikidev --appid 'createWikis.sh' --grants 'basic,createeditmovepage,editinterface,editpage' 'Admin' '12345678901234567890123456789012'

####
# Create starting entities and pages
###
token=""
apiBase='http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/api.php'
curlCookieOptions="-b /tmp/cookie.txt -c /tmp/cookie.txt"

getCSRFToken () {
  local user='Admin@createWikis.sh'
  local pass='12345678901234567890123456789012'

  local loginTokenResponse=`curl -j $(echo $curlCookieOptions) "${apiBase}?action=query&meta=tokens&type=login&format=json&formatversion=2"`

  local loginToken=`grep --only-matching -E '[a-f0-9]{40}' <<< $loginTokenResponse`

  curl $(echo $curlCookieOptions) \
    --data-urlencode "lgtoken=${loginToken}+\\" \
    --data-urlencode "lgname=${user}" \
    --data-urlencode "lgpassword=${pass}" \
    "${apiBase}?action=login&format=json&formatversion=2"

  local tokenResponse=`curl $(echo $curlCookieOptions) "${apiBase}?action=query&meta=tokens&format=json&formatversion=2"`
  token=`grep --only-matching -E '[a-f0-9]{40}' <<< $tokenResponse`
}
getCSRFToken

createPage () {
  local title=$1
  local text=$2
  local summary='Created by createWikis.sh'
  curl $(echo $curlCookieOptions) --data-urlencode  "token=${token}+\\" \
  --data-urlencode "title=${title}" \
  --data-urlencode "text=${text}" \
  --data-urlencode "summary=${summary}" \
  "${apiBase}?action=edit&bot=1&format=json&formatversion=2"
}

createEntity () {
  local entityType=$1
  local data=$2
  local summary='Created by createWikis.sh'
  curl $(echo $curlCookieOptions) --data-urlencode  "token=${token}+\\" \
  --data-urlencode "data=${data}" \
  --data-urlencode "summary=${summary}" \
  "${apiBase}?action=wbeditentity&bot=1&new=${entityType}&format=json&formatversion=2"
}

# P1
createEntity 'property' '{"labels":{"en":{"language":"en","value":"Sandbox String"}}, "datatype":"string"}'
# P2
createEntity 'property' '{"labels":{"en":{"language":"en","value":"Sandbox Url"}}, "datatype":"url"}'
# P3
createEntity 'property' '{"labels":{"en":{"language":"en","value":"Sandbox External Id"}}, "datatype":"external-id"}'
# P4
createEntity 'property' '{"labels":{"en":{"language":"en","value":"Sandbox Item"}}, "datatype":"wikibase-item"}'
# P5
createEntity 'property' '{"labels":{"en":{"language":"en","value":"Property Constraint"}}, "datatype":"wikibase-item"}'
# P6
createEntity 'property' '{"labels":{"en":{"language":"en","value":"Lexeme Language Code"}}, "datatype":"string"}'
# P7
createEntity 'property' '{"labels":{"en":{"language":"en","value":"Sandbox Entity Schema"}}, "datatype":"entity-schema"}'

# Q1 generic sandbox item
createEntity 'item' '{"labels":{"en":{"language":"en","value":"Sandbox Item"}}, "claims":[{"mainsnak":{"snaktype":"value","property":"P1","datavalue":{"value":"ExampleString","type":"string"}},"type":"statement","rank":"normal"}]}'

# Q2 - Q4: Items for badges
createEntity 'item' '{"labels":{"en":{"language":"en","value":"Good Article"}}}'
createEntity 'item' '{"labels":{"en":{"language":"en","value":"sitelink to redirect"}}}'
createEntity 'item' '{"labels":{"en":{"language":"en","value":"intentional sitelink to redirect"}}}'

# localize sitelinks group
createPage 'MediaWiki:Wikibase-sitelinks-mylocalwikis' 'Local Wikis'
createPage 'MediaWiki:Wikibase-statementsection-constraints' 'Constraints'
createPage 'MediaWiki:Mainpage' 'Wikidata:Main Page'

createPage 'Wikidata:Main Page' '== Test Pages for specific functionalities ==
* [[Wikidata:Bridge|Wikidata Bridge]]
* [[Wikidata:Lua|Lua]]'
createPage 'Module:Sandbox' "p = {}
local entityId = 'Q1'
p.label = function(frame)
  local entity = mw.wikibase.getEntity( entityId )
  return entity.labels.en.value
--  return mw.wikibase.getLabel( entityId )
end

p.isValidEntityId = function(frame)
  return mw.wikibase.isValidEntityId( entityId )
end

p.getEntity = function(frame)
  return mw.wikibase.getEntity( entityId )
end

return p"
createPage 'Wikidata:Lua' 'Parser function actually: {{#statements:P1|from=Q1}}

<nowiki>{{#invoke:Sandbox|label}}</nowiki>{{#invoke:Sandbox|label}}
<nowiki>{{#invoke:Sandbox|isValidEntityId}}</nowiki>{{#invoke:Sandbox|isValidEntityId}}
<nowiki>{{#invoke:Sandbox|getEntity}}</nowiki>{{#invoke:Sandbox|getEntity}}'
createPage 'Wikidata:Bridge' '{{#statements:P1|from=Q1}}&nbsp;<span data-bridge-edit-flow="single-best-value">[http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=Item:Q1#P1 Edit with Wikidata Bridge]</span>'
createPage 'MediaWiki:Common.js' "function logTrackerToConsole(topic, data) {\n  console.log( 'mw.track: %O %c(see MediaWiki:Common.js)', { 'topic': topic, 'data': data }, 'color: grey;' );\n}\nmw.trackSubscribe('', logTrackerToConsole);"

echo "<?php\n" > WDQCPropertySettings.php
echo "\n\n Importing Constraint Entities. This might take a while... \n\n"
# mw docker mediawiki exec -- php maintenance/run.php WikibaseQualityConstraints:ImportConstraintEntities.php --wiki wikidatawikidev | tee -a WDQCPropertySettings.php

mw docker mediawiki exec -- php maintenance/run.php RunJobs --wiki wikidatawikidev
mw docker mediawiki exec -- php maintenance/run.php RunJobs --wiki dewikidev
mw docker mediawiki exec -- php maintenance/run.php RunJobs --wiki eowikidev
