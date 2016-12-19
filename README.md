Lookyman/Nette/Proxy
====================

Integration of [Proxy Manager](https://ocramius.github.io/ProxyManager) into [Nette Framework](https://nette.org).

[![Build Status](https://travis-ci.org/lookyman/nette-proxy.svg?branch=master)](https://travis-ci.org/lookyman/nette-proxy)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lookyman/nette-proxy/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lookyman/nette-proxy/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/lookyman/nette-proxy/badge.svg?branch=master)](https://coveralls.io/github/lookyman/nette-proxy?branch=master)
[![Downloads](https://img.shields.io/packagist/dt/lookyman/nette-proxy.svg)](https://packagist.org/packages/lookyman/nette-proxy)
[![Latest stable](https://img.shields.io/packagist/v/lookyman/nette-proxy.svg)](https://packagist.org/packages/lookyman/nette-proxy)


Installation
------------

### Install

```sh
composer require lookyman/nette-proxy
```

### Config

```yaml
extensions: 
	proxy: Lookyman\Nette\Proxy\DI\ProxyExtension
	
proxy:
	proxyDir: %appDir%/../temp/proxies # this is the default value
	default: off # turn on to proxy everything
```

### Usage

Tag services with `lookyman.lazy` and they get magically proxied.

```yaml
services: 
	-
		class: MyHeavyService
		tags: [lookyman.lazy]
```

If you have `proxy.default` turned on and you don't want a particular service to be proxied, you can do it like this:

```yaml
services: 
	-
		class: DontProxyMeService
		tags: [lookyman.lazy: off]
```

Proxying certain Nette services is automaticaly disabled due to [known limitations](https://ocramius.github.io/ProxyManager/docs/lazy-loading-value-holder.html#known-limitations).

### Pre-generating proxies

Proxy generation causes I/O operations and uses a lot of reflection, so it is handy to have them pre-generated before the application starts. For this, install [Kdyby/Console](https://github.com/kdyby/console) and run:

```sh
php www/index.php lookyman:nette-proxy:generate
```
