#!/usr/bin/env zsh
set -xe

# initial creation
mw docker mediawiki create
mw docker mysql create
mw docker mysql-replica create
mw docker phpmyadmin create
mw docker memcached create
# graphite has been disabled for now, see T307366
# mw docker graphite create # available at http://graphite.mwdd.localhost:8080/

# install repo
mw docker mediawiki install --dbtype=mysql --dbname=wikidatawikidev
# tell the repo about itself
mw docker mediawiki exec -- php maintenance/run.php AddSite --wiki wikidatawikidev wikidatawikidev mylocalwikis --interwiki-id wikidatawikidev --navigation-id wikidatawikidev --pagepath 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/$1' --language en --server 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080'

# install dewiki_dev
mw docker mediawiki install --dbtype=mysql --dbname=dewiki_dev
# tell the dewiki_dev about itself
mw docker mediawiki exec -- php maintenance/run.php AddSite --wiki dewiki_dev dewiki_dev mylocalwikis --interwiki-id dewiki_dev --navigation-id dewiki_dev --pagepath 'http://dewiki_dev.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://dewiki_dev.mediawiki.mwdd.localhost:8080/w/$1' --language de --server 'http://dewiki_dev.mediawiki.mwdd.localhost:8080'
# tell the repo about the dewiki_dev wiki
mw docker mediawiki exec -- php maintenance/run.php AddSite --wiki wikidatawikidev dewiki_dev mylocalwikis --interwiki-id dewiki_dev --navigation-id dewiki_dev --pagepath 'http://dewiki_dev.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://dewiki_dev.mediawiki.mwdd.localhost:8080/w/$1' --language de --server 'http://dewiki_dev.mediawiki.mwdd.localhost:8080'
# tell the dewiki_dev about the repo wiki
mw docker mediawiki exec -- php maintenance/run.php AddSite --wiki dewiki_dev wikidatawikidev mylocalwikis --interwiki-id wikidatawikidev --navigation-id wikidatawikidev --pagepath 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/$1' --language en --server 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080'

# add interwiki prefixes so that the sitelinks in the sidebar work
mw docker mediawiki exec -- php maintenance/run.php MwSql --wiki wikidatawikidev --status --query "INSERT INTO interwiki (iw_prefix, iw_url, iw_local, iw_trans, iw_api, iw_wikiid) VALUES ('dewiki_dev', 'http://dewiki_dev.mediawiki.mwdd.localhost:8080/w/index.php?title=\$1', 0, 0, 'http://dewiki_dev.mediawiki.mwdd.localhost:8080/w/api.php', 'dewiki_dev');"
mw docker mediawiki exec -- php maintenance/run.php MwSql --wiki dewiki_dev --status --query "INSERT INTO interwiki (iw_prefix, iw_url, iw_local, iw_trans, iw_api, iw_wikiid) VALUES ('wikidatawikidev', 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=\$1', 0, 0, 'http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/api.php', 'wikidatawikidev');"

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
createEntity 'property' '{"labels":{"en":{"language":"en","value":"Sandbox Url"}}, "datatype":"url"}'
createEntity 'property' '{"labels":{"en":{"language":"en","value":"Sandbox External Id"}}, "datatype":"external-id"}'
createEntity 'property' '{"labels":{"en":{"language":"en","value":"Sandbox Item"}}, "datatype":"wikibase-item"}'
createEntity 'property' '{"labels":{"en":{"language":"en","value":"Property Constraint"}}, "datatype":"wikibase-item"}'
createEntity 'property' '{"labels":{"en":{"language":"en","value":"Lexeme Language Code"}}, "datatype":"string"}'

# Q1 generic sandbox item
createEntity 'item' '{"labels":{"en":{"language":"en","value":"Sandbox Item"}}, "claims":[{"mainsnak":{"snaktype":"value","property":"P1","datavalue":{"value":"ExampleString","type":"string"}},"type":"statement","rank":"normal"}]}'

# Q2 - Q4: Items for badges
createEntity 'item' '{"labels":{"en":{"language":"en","value":"Good Article"}}}'
createEntity 'item' '{"labels":{"en":{"language":"en","value":"sitelink to redirect"}}}'
createEntity 'item' '{"labels":{"en":{"language":"en","value":"intentional sitelink to redirect"}}}'

# localize sitelinks group
createPage 'MediaWiki:Wikibase-sitelinks-mylocalwikis' 'Local Wikis'
createPage 'MediaWiki:Wikibase-statementsection-constraints' 'Constraints'

createPage 'Main_Page' '== Test Pages for specific functionalities ==
* [[Bridge|Wikidata Bridge]]'
createPage 'Bridge' '{{#statements:P1|from=Q1}}&nbsp;<span data-bridge-edit-flow="single-best-value">[http://wikidatawikidev.mediawiki.mwdd.localhost:8080/w/index.php?title=Item:Q1#P1 Edit with Wikidata Bridge]</span>'
