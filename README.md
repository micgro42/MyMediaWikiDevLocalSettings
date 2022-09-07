# Wikidata Local Dev Setup

This repository contains a default configuration and wiki creation script to get to a local development setup that somewhat mirrors
Wikidata and a Wikipedia wiki.
It is inherently perpetually in progress.

## Installation

### Preparations
The files/scripts here are intended to work together with the [mwcli](https://www.mediawiki.org/wiki/Cli) development environment.

1. Make sure you have installed mwcli: https://www.mediawiki.org/wiki/Cli/guide/Installation
2. Make sure you have completed its first-time setup: https://www.mediawiki.org/wiki/Cli/guide/Docker-Development-Environment/First-Setup
    - this includes making sure you have a `LocalSettings.php` file in the root directory of your `mediawiki core` clone


### Required extensions and skins

These default settings assume some extensions to be present.
The best way to get them is to filter for them in the [list of all repositories](https://gerrit.wikimedia.org/r/admin/repos),
and then clone them (with the long command including the `commit-msg` hook) directly into `mediawiki/extensions/`.

* Wikibase
* WikibaseLexeme
* WikibaseQualityConstraints
* EntitySchema
* Scribunto
* ArticlePlaceholder
* MobileFrontend
* WikimediaBadges

Further, two skins are needed:
* Vector, which should have been installed as part of the mwcli first-time setup above
* MinervaNeue, which can be cloned with the process described above, but this time into the directory `mediawiki/skins/`.

### Link the files

For the contents in this repository to work, the favicons and the `DefaultLocalSettings.php` have to be _hard-linked_ into the `mediawiki/` directory.

You can install the files correctly by running:

```shell
./linkFilesToMediawikiDirectory.sh <path to your mediawiki directory>
```

**This linking step has to be repeated after each `git pull` or similar in the current repository!**

### Load the default settings

Then, in your `LocalSettings.php` (which must already be inside the mediawiki directory) somewhere in the beginning, after requiring the docker-related LocalSettings, add the following:
```php
require_once __DIR__ . '/DefaultLocalSettings.php';
```

For example, the top of my own `mediawiki/LocalSettings.php` looks like this:

```php
<?php

require_once '/mwdd/MwddSettings.php';

require_once __DIR__ . '/DefaultLocalSettings.php';

// ... other settings
```


## Usage

After completing the setup above, there should be a `createWiki.sh` file linked inside your `mediawiki/` directory.
You can just execute that file to create two linked wikis and a few initial entities from scratch:

```shell
./createWikis.sh
```
