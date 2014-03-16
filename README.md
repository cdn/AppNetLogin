AppNetLogin
===========

An [App.net](https://app.net) authentication extension for [MediaWiki](https://www.mediawiki.org/wiki/MediaWiki)

Requires:
- [AppDotNetPHP](https://github.com/jdolitsky/AppDotNetPHP) library to be placed in the eponymous subdirectory
- [App.net](https://app.net) Developer-tier account

### Install MediaWiki

### Create an App.net app

> go to [https://alpha.app.net/developer/apps/](https://alpha.app.net/developer/apps/)

> click "Create an App"

> enter the application name: "Name of your WIKI"

> enter the website: http://wiki-domain.tld* (* this is wherever your WIKI appears online)

> enter the callback url: http://&lt;wiki-domain.tld>/wiki/index.php?title=Special:AppNetLogin/callback

> click "Create", you will need the client id and client secret below

### Configure MediaWiki

> add the LocalSettings.php.fragment lines to LocalSettings.php

> amend the key and secret lines with client id/secret from App.net app
