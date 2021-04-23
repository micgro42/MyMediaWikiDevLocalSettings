echo "Script to create two new wikis from scratch and create sitelinks between them.\n"
echo "Symlink this script to your addshore/mediawiki-docker-dev directory and run it from there.\n"

./create

./addsite client

# tell the client wiki about the default wiki
./script client maintenance/addSite.php --pagepath='http://default.web.mw.localhost:8080/mediawiki/index.php?title=$1'  --filepath='http://default.web.mw.localhost:8080/mediawiki/$1' --language en --interwiki-id default default local

# tell the client wiki that it is also a client to itself
./script client maintenance/addSite.php --pagepath='http://client.web.mw.localhost:8080/mediawiki/index.php?title=$1'  --filepath='http://client.web.mw.localhost:8080/mediawiki/$1' --language en --interwiki-id client client local

# tell the default wiki that it is also a client to itself
./script default maintenance/addSite.php --pagepath='http://default.web.mw.localhost:8080/mediawiki/index.php?title=$1'  --filepath='http://default.web.mw.localhost:8080/mediawiki/$1' --language en --interwiki-id default default local

# tell the default wiki about the client wiki
./script default maintenance/addSite.php --pagepath='http://client.web.mw.localhost:8080/mediawiki/index.php?title=$1'  --filepath='http://client.web.mw.localhost:8080/mediawiki/$1' --language en --interwiki-id client client local
