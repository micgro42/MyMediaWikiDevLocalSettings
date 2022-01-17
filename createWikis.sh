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
