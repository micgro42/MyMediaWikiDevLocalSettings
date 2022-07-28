#!/usr/bin/env zsh
set -xe

# initial creation
mw docker mediawiki create
mw docker mysql create
mw docker phpmyadmin create
mw docker memcached create

# install repo
mw docker mediawiki install --dbtype=mysql --dbname=default
# tell the repo about itself
mw docker mediawiki exec -- php maintenance/addSite.php default mylocalwikis --interwiki-id default --navigation-id default --pagepath 'http://default.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://default.mediawiki.mwdd.localhost:8080/w/$1' --language en --server 'http://default.mediawiki.mwdd.localhost:8080'

# install client
mw docker mediawiki install --dbtype=mysql --dbname=client
# tell the client about itself
mw docker mediawiki exec -- php maintenance/addSite.php --wiki client client mylocalwikis --interwiki-id client --navigation-id client --pagepath 'http://client.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://client.mediawiki.mwdd.localhost:8080/w/$1' --language de --server 'http://client.mediawiki.mwdd.localhost:8080'
# tell the repo about the client wiki
mw docker mediawiki exec -- php maintenance/addSite.php client mylocalwikis --interwiki-id client --navigation-id client --pagepath 'http://client.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://client.mediawiki.mwdd.localhost:8080/w/$1' --language de --server 'http://client.mediawiki.mwdd.localhost:8080'
# tell the client about the repo wiki
mw docker mediawiki exec -- php maintenance/addSite.php --wiki client default mylocalwikis --interwiki-id default --navigation-id default --pagepath 'http://default.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://default.mediawiki.mwdd.localhost:8080/w/$1' --language en --server 'http://default.mediawiki.mwdd.localhost:8080'

# add interwiki prefixes so that the sitelinks in the sidebar work
mw docker mediawiki exec -- php maintenance/sql.php --status --query "INSERT INTO interwiki (iw_prefix, iw_url, iw_local, iw_trans, iw_api, iw_wikiid) VALUES ('client', 'http://client.mediawiki.mwdd.localhost:8080/w/index.php?title=\$1', 0, 0, 'http://client.mediawiki.mwdd.localhost:8080/w/api.php', 'client');"
mw docker mediawiki exec -- php maintenance/sql.php --wiki client --status --query "INSERT INTO interwiki (iw_prefix, iw_url, iw_local, iw_trans, iw_api, iw_wikiid) VALUES ('default', 'http://default.mediawiki.mwdd.localhost:8080/w/index.php?title=\$1', 0, 0, 'http://default.mediawiki.mwdd.localhost:8080/w/api.php', 'default');"

# create "Data Bridge" tag
mw docker mediawiki exec -- php maintenance/addChangeTag.php --tag 'Data Bridge' --reason 'added by createWikis.sh'

# create bot passwords to use with API below
mw docker mediawiki exec -- php maintenance/createBotPassword.php --appid 'createWikis.sh' --grants 'basic,createeditmovepage,editinterface,editpage' 'Admin' '00000000000000000000000000000000'

####
# Create starting entities and pages
###
token=""
apiBase='http://default.mediawiki.mwdd.localhost:8080/w/api.php'
curlCookieOptions="-b /tmp/cookie.txt -c /tmp/cookie.txt"

getCSRFToken () {
  local user='Admin@createWikis.sh'
  local pass='00000000000000000000000000000000'

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

# Q1 - Item for "Good Article" badge
createEntity 'item' '{"labels":{"en":{"language":"en","value":"Good Article"}}}'

# P1
createEntity 'property' '{"labels":{"en":{"language":"en","value":"Sandbox String"}}, "datatype":"string"}'

# localize sitelinks group
createPage 'MediaWiki:Wikibase-sitelinks-mylocalwikis' 'Local Wikis'
