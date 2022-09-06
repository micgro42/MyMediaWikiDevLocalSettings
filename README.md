# Wikidata Local Dev Setup

TODO: quick summary of what this is

## Installation

### Preparations
The files/scripts here are intended to work together with the [mwcli](https://www.mediawiki.org/wiki/Cli) development environment.

1. Make sure you have installed mwcli: https://www.mediawiki.org/wiki/Cli/guide/Installation
1. Make sure you have completed its first-time setup: https://www.mediawiki.org/wiki/Cli/guide/Docker-Development-Environment/First-Setup
    - this includes making sure you have a `LocalSettings.php` file in the root directory of your `mediawiki core` clone


### Required extensions and skins

* TODO

### Link the files

For the contents in this repository to work, the favicons and the `DefaultLocalSettings.php` have to be _hard-linked_ into the `mediawiki/` directory.

You can install the files correctly by running:

```shell
./install.sh <path to your mediawiki directory>
```

**This linking step has to be repeated after each `git pull` or similar in the current repository!**

### Load the default settings

Then, in your `LocalSettings.php` (which must already be inside the mediawiki directorey) somewhere in the beginning, after requiring the docker-related LocalSettings, add the following:
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
