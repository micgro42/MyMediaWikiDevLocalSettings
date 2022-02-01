#!/usr/bin/env zsh
set -xe

# initial creation
mw docker mediawiki create
mw docker mysql create
mw docker phpmyadmin create

# install repo
mw docker mediawiki install --dbtype=mysql --dbname=default
# tell the repo about itself
mw docker mediawiki exec -- php maintenance/addSite.php default default --interwiki-id default --navigation-id default --pagepath 'http://default.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://default.mediawiki.mwdd.localhost:8080/w/$1' --language en --server 'http://default.mediawiki.mwdd.localhost:8080'

# install client
mw docker mediawiki install --dbtype=mysql --dbname=client
# tell the client about itself
mw docker mediawiki exec -- php maintenance/addSite.php --wiki client client default --interwiki-id client --navigation-id client --pagepath 'http://client.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://client.mediawiki.mwdd.localhost:8080/w/$1' --language de --server 'http://client.mediawiki.mwdd.localhost:8080'
# tell the repo about the client wiki
mw docker mediawiki exec -- php maintenance/addSite.php client default --interwiki-id client --navigation-id client --pagepath 'http://client.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://client.mediawiki.mwdd.localhost:8080/w/$1' --language de --server 'http://client.mediawiki.mwdd.localhost:8080'
# tell the client about the repo wiki
mw docker mediawiki exec -- php maintenance/addSite.php --wiki client default default --interwiki-id default --navigation-id default --pagepath 'http://default.mediawiki.mwdd.localhost:8080/w/index.php?title=$1' --filepath 'http://default.mediawiki.mwdd.localhost:8080/w/$1' --language en --server 'http://default.mediawiki.mwdd.localhost:8080'

# add interwiki prefixes so that the sitelinks in the sidebar work
mw docker mediawiki exec -- php maintenance/sql.php --status --query "INSERT INTO interwiki (iw_prefix, iw_url, iw_local, iw_trans, iw_api, iw_wikiid) VALUES ('client', 'http://client.mediawiki.mwdd.localhost:8080/w/index.php?title=\$1', 0, 0, 'http://client.mediawiki.mwdd.localhost:8080/w/api.php', 'client');"
mw docker mediawiki exec -- php maintenance/sql.php --wiki client --status --query "INSERT INTO interwiki (iw_prefix, iw_url, iw_local, iw_trans, iw_api, iw_wikiid) VALUES ('default', 'http://default.mediawiki.mwdd.localhost:8080/w/index.php?title=\$1', 0, 0, 'http://default.mediawiki.mwdd.localhost:8080/w/api.php', 'default');"

# create "Data Bridge" tag
mw docker mediawiki exec -- php maintenance/addChangeTag.php --tag 'Data Bridge' --reason 'added by createWikis.sh'
