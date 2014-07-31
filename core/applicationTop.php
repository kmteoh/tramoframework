<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

session_start();
ob_start("ob_gzhandler");

define('DS',DIRECTORY_SEPARATOR);
define('FS',dirname(dirname(__FILE__)).DS);

define('_CONTROLLER','controllers');
define('_CONFIG','config');
define('_CORE','core');
define('_DOMAIN','domains');
define('_EXCEPTION','exceptions');
define('_FILTER','filters');
define('_SERVICE','services');
define('_TAGLIB','taglibs');
define('_VENDOR','vendors');
define('_VIEW','views');

define('CORE',FS._CORE.DS);
define('CONFIG',FS._CONFIG.DS);
define('CORECONFIG',CORE._CONFIG.DS);
define('EXTENSION',FS.'extensions'.DS);

//special header and footer file
define('CORE_ERROR_HEADER',CORE._VIEW.DS.'error'.DS.'_header');
define('CORE_ERROR_FOOTER',CORE._VIEW.DS.'error'.DS.'_footer');

require_once CORE.'config.php';
require_once CORE.'functions.php';

//core
import('dataSource');
import('appender');
import('logger');
import('cache');
import('domain');
import('controller');
import('service');
import('filter');
import('taglib');
import('view');
import('urlMapping');
import('param');

