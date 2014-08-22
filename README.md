TF for PHP
=======================

A lightweight PHP framework which aims to make coding simpler. This framework is distributed under MIT license.

TF will be presented as TF in this document.

## Table of Contents
1. [Getting Started](#1)<br>
  1.1. &nbsp;&nbsp;[Installation Requirement](#1-1)<br>
  1.2. &nbsp;&nbsp;[Downloading and Installing](#1-2)<br>
  1.3. &nbsp;&nbsp;[Understanding Framework Structure](#1-3)<br>
  1.4. &nbsp;&nbsp;[A Hello World Example](#1-4)
2. [Configuration](#2)
3. [Domain Modeling](#3)
4. [Controllers and Actions](#4)
5. [Services](#5)
5. [Web Layer](#6)
7. [Filters](#7)
8. [Taglibs](#8)
9. [Extensions](#9)
10. [Plugins](#10)
12. [Cron Jobs](#10)
13. [Deployment Checklist](#11)
14. [Report Issues](#12)

<a name="1"></a>
# 1. Getting Started
<a name="1-1"></a>
## 1.1 &nbsp;&nbsp;Installation Requirement
This framework is built and intended to run with PHP 5.3 or later, though it is developed and tested with PHP 5.3 only. It currently does not take advantage of namespace but may be introduced in later versions.
Primarily TF requires WAMP, LAMP or MAMP with following being enabled:
&nbsp;&nbsp;* mod_rewite<br>&nbsp;&nbsp;* short_tag<br>&nbsp;&nbsp;* xmlrpc

Other extentions will be required only when needed.

<a name="1-2"></a>
## 1.2 &nbsp;&nbsp;Downloading and Installing
TF can be downloaded via [GitHub](https://github.com/kmteoh/tramoframework) only.
For setting up web applications built with TF and get it running on Apache, 'DocumentRoot' must be set to 'webapp' folder from the framework. Please refer [here](http://httpd.apache.org/docs/2.2/vhosts/examples.html) for sample.

<a name="1-3"></a>
## 1.3 &nbsp;&nbsp;Understanding Framework Structure
TF is structured to be like another MVC frameworks out there in the market, to minimise the learning curves. There are some components that are only introduced to this framework, which will be explained.
Below explains the main folders in TF and what do they use for:

**\config** - main configuration files in json format, typically config.json, dataSource.json and urlMappings.json. This folder can be accessible using predefined keyword `CONFIG`<br>
**\controllers** - all controllers sit in here. A controller may have more than one actions which handle requests and creates or prepares the response. Also read [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) for more information<br>
**\core** - core TF files. All magics happen here<br>
**\domains** - a domain represents an entity in the application, e.g. books and authors. All properties of entity is mapped using [ORM](http://en.wikipedia.org/wiki/Object-relational_mapping) technique<br>
**\exceptions** - all custom exception classes will be stored here<br>
**\extensions** - TF support extensions which has near identical structure explained right here. Extension provides extra functionalities to existing application, which can be developed and tested independently<br>
**\filters** - filter works as intercepter to controllers and actions. All filters will be stored here<br>
**\jobs** - common web applications will require some sort of automation scripts to perform specific tasks at specific time or within specific interval. TF provides a way to develop these automation scripts in more structured way<br>
**\services**<br>
**\taglibs**<br>
**\tests**<br>
**\vendors**<br>
**\views**<br>
**\webapp**<br>

<a name="1-4"></a>
## 1.4 &nbsp;&nbsp;A Hello World Example

<a name="2"></a>
# 2. Configuration

<a name="3"></a>
# 3. Domain Modeling

<a name="4"></a>
# 4. Controllers and Actions

<a name="5"></a>
# 5. Services

<a name="6"></a>
# 6. Web Layer

<a name="7"></a>
# 7. Filters

<a name="8"></a>
# 8. Taglibs

<a name="9"></a>
# 9. Extensions

<a name="10"></a>
# 10. Plugins

<a name="11"></a>
# 11. Deployment Checklist

<a name="12"></a>
# 12. Report Issues