<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

$start = microSeconds();
error_reporting(0);
ini_set('display_errors',false);

spl_autoload_register('autoloader');
register_shutdown_function('shutdown');
set_exception_handler('uncaughtException');

try {
    require_once(__DIR__.DS.'coreInit.php');

    //import user bootstrap file
    if(file_exists(CONFIG.'bootstrap.php') && is_readable(CONFIG.'bootstrap.php')) {
        include_once CONFIG.'bootstrap.php';
    }

    //init param
    $params = config::getHandler('param');

    //routing
    try {
        $urlMap = urlMapping::parse();
        extract($urlMap);
    } catch (UrlMappingErrorException $e) {
        $ePage = urlMapping::exceptionPage(get_class($e));
        if (is_array($ePage)) {
            //we have controller and action to work on
            extract($ePage);
            $params->exception = $e;
        } else {
            throw $e;
        }
    }

    if (empty($controller)) {
        throw new ControllerNotFoundException();
    } else if (empty($action)) {
        throw new ActionNotFoundException();
    }

    $filters = filter::getFilters();
    try {
        //determine controller and action
        $controllerName = $controller . 'Controller';
        if (!class_exists($controllerName)) {
            throw new ControllerNotFoundException("`$controller` controller not exists!");
        }
        if (!method_exists($controllerName, $action)) {
            throw new ActionNotFoundException("`$action` action in `$controller` controller not exists!");
        }

        //load filters and run 'before' filters
        for($i=0;$i<count($filters);$i++) {
            $filters[$i]->before($controller,$action);
        }

        //action
        $controllerObj = new $controllerName();
        $model = call_user_func(array($controllerObj,$action));
    } catch(Exception $e) {
        //this can be any kind of exceptions which was potentially properly handled
        $ePage = urlMapping::exceptionPage(get_class($e));
        if (is_array($ePage)) {
            //we have controller and action to work on
            extract($ePage);
            $controllerName = $controller.'Controller';
            $params->exception = $e;
            //reaction
            $controllerObj = new $controllerName();
            $model = call_user_func(array($controllerObj,$action),$e);
        } else {
            throw $e;
        }
    }
    $model['params'] = $params;

    //run 'after' filters
    for($i=0;$i<count($filters);$i++) {
        $filters[$i]->after($controller,$action,$model);
    }

    //if format is specified, convery the output to given format, potentially json/xml/csv
    if (!empty($model['format'])) {
        if (!isset($runtimeConfig->mimeTypes[$model['format']]))
            throw new ConvertionErrorException($model['format'] . " mime type not defined");

        $fx = $model['format'] . "_encode";
        $headers = array();
        if (!function_exists($fx)) {
            throw new BadFunctionCallException("$fx() not defined");
        }
        $headers[] = "Content-type: " . $runtimeConfig->mimeTypes[$model['format']];
        if (!empty($model['filename'])) {
            $headers[] = 'Content-Disposition: attachment; filename="' . $model['filename'] . '.' . $model['format'] . '"';
        }
        
        $xsl = !empty($model['xsl']) ? $model['xsl'] : null;

        customHeaders($headers);
        
        //so that stream output does not content these elements
        $data = $model;
        unset($data['xsl']);
        unset($data['format']);
        unset($data['filename']);
        unset($data['params']);
        if ($model['format'] == 'csv') {
            $data = array_shift(array_values($data));
        }

        ob_start();
        if(!empty($xsl)) {
            $body = $fx($data,null,null,$xsl);
        } else {
            $body = $fx($data);
        }
        ob_end_clean();
    }
    //if goto is specified, it is a redirect
    else if (!empty($model['goto'])) {
        redirect($model['goto']);
    }
    //if body is given, use as is
    else if (isset($model['body'])) {
        $body = $model['body'];
    }
    //anything else
    else {
        //to support more annotations in future
        list($layout,$template) = View::getProperties($controllerName, $action, array('layout','template'));
        if(!empty($model['customTemplate'])) {
            $template = $model['customTemplate'];
        } else {
            $template = !empty($template)?$template:$runtimeConfig->template;
        }
        if(empty($layout)) {
            $layout = 'main';
        }
        $templateExist = false;
        foreach(getDefinedPath('view') as $v) {
            if(is_dir($v.$template) && count(glob($v.$template."/*"))) {
                $templateExist = true;
            }
        }
        if(!$templateExist) {
            throw new FileNotFoundException("Template `$template` does not seem to exist (1)");
        }
        $params->templateToUse = $template.DS;
        define('TEMPLATE_NAME',$template);

        //get content
        if($cache && $cache->canCache('page') && $cachedBody = $cache->get("{$params->host}{$params->query}view")) {
            $body = $cachedBody;
        } else {
            $view = !empty($view) ? $view : (!empty($model['view']) ? $model['view'] : "$controller/$action");
            if (!strstr($view, '/'))
                $view = "$controller/$view";
            ob_start();
            view($view, $model);
            $body = ob_get_clean();

            //put content into layout
            if ($layout && $layout != 'nil') {
                $model = array_merge($model, view::extractHeadAndBody($body));
                ob_start();
                view('layout/' . $layout, $model);
                $body = ob_get_clean();
            }

            //process main tags: layouthead/layouttitle/layoutbody
            $body = taglib::parseLayoutTags($body, $model);

            //process taglibs
            $body = taglib::parseTags($body, $model);

            //inject system code
            $injector = PHP_EOL."\t<meta name=\"generated-by\" content=\"Tramo Framework\"/>";
            $injector .= PHP_EOL."\t<meta name=\"generated-by-version\" content=\"".config::get('version')."\"/>";
            $body = str_replace('</head>',$injector.PHP_EOL."</head>",$body);

            if($cache && $cache->canCache('page')) {
                $cache->set("{$params->host}{$params->query}view",$body);
            }
        }
    }

    //run 'beforeView' filters
    for($i=0;$i<count($filters);$i++) {
        $body = $filters[$i]->beforeView($controller,$action,$model,$body);
    }

    echo $body;

    //run 'afterView' filters
    for($i=0;$i<count($filters);$i++) {
        $filters[$i]->afterView($controller,$action,$model,$body);
    }
} catch (HttpException $e) {
    header('HTTP/1.1 ' . $e->getStatusCode());
    $view = urlMapping::exceptionPage(get_class($e));
    if (empty($view))
        $view = urlMapping::errorPage($e->getStatusCode());
    if (empty($view))
        $view = 'error/httpError';
    view($view, array('class' => get_class($e), 'status' => $e->getStatusCode(), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'params' => config::getHandler('param')));
} catch (Exception $e) {
    header('HTTP/1.1 500');
    $view = urlMapping::exceptionPage(get_class($e));
    if (empty($view))
        $view = 'error/debug';
    view($view, array('class' => get_class($e), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'params' => config::getHandler('param')));
}
