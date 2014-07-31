<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

class urlMapping {
    private $_maps = array();

    public static final function parse() {
        $mapped = array();
        $params = config::getHandler('param');
        $urls = urlMapping::_getDefinedUrls();

        $mimeTypes = config::get('mimeTypes');
        $extentions = array_keys($mimeTypes);
        $pathinfo = pathinfo($params->path);
        if(!empty($pathinfo['extension']) && in_array($pathinfo['extension'],$extentions)) {
            $params->format = $pathinfo['extension'];
            $params->path = str_replace($pathinfo['basename'],$pathinfo['filename'],$params->path);
        }

        if(substr($params->path,-1) == '/') {
            $params->path = substr($params->path,0,-1);
        }
        $urlParts = $params->path ? explode('/',$params->path) : array();

        if(empty($urlParts))
            $mapped = $urls['/'];
        else {
            foreach($urls as $pattern => $redirects) {
                //skip error pages
                if(is_numeric(substr($pattern,0,1)))
                    continue;

                $mapped = array(); $matched = null;
                preg_match_all('/\/([\$\w\-_]*)/',$pattern,$matched);

                if(count($urlParts) == count($matched[1])) {
                    $mismatched = false;
                    foreach($matched[1] as $i => $param) {
                        if(substr($param,0,1)=='$')
                            $mapped[substr($param,1)] = $urlParts[$i];
                        else if($urlParts[$i] != $param)
                            $mismatched = true;
                    }

                    if($mismatched)
                        continue;

                    if(!empty($redirects['controller']))
                        $mapped['controller'] = $redirects['controller'];
                    if(!empty($redirects['action'])) {
                        if(is_array($redirects['action'])) {
                            if(!empty($redirects['action'][$params->requestMethod])) {
                                $mapped['action'] = $redirects['action'][$params->requestMethod];
                            } else {
                                throw new BadRequestException("$pattern does not support {$params->requestMethod} request method");
                            }
                        } else {
                            $mapped['action'] = $redirects['action'];
                        }
                    }
                    if(!empty($redirects['view']))
                        $mapped['view'] = $redirects['view'];

                    if(!empty($mapped['controller']) && !empty($mapped['action'])) {
                        $mapped['pattern'] = $pattern;
                        $mapped['redirects'] = $redirects;
                        break;
                    }
                }
            }
        }

        if(empty($mapped)) {
            throw new UrlMappingErrorException("Can't match pattern for \"/{$params->path}\"");
        }

        foreach($mapped as $key => $value) {
            if($key == 'controller' && strpos($value,'\\')) {
                $path = CONTROLLER.$value.'Controller';
                import($path);
                $parts = explode('\\',$value);
                $value = $parts[count($parts)-1];
                $mapped[$key] = $value;
            } else {
                $params->$key = $value;
            }
        }

        return $mapped;
    }

    private static final function _getDefinedUrls() {
        if(!file_exists(CONFIG."urlMappings.json")) {
            throw new FileNotFoundException(CONFIG."urlMappings.json not found");
        }

        $urls = json_decode(file_get_contents(CONFIG."urlMappings.json"),true);
        
        $runtimeConfig = config::getInstance();
        if(count($runtimeConfig->extensions)) {
            foreach($runtimeConfig->extensions as $extension) {
                $urls = array_merge(json_decode(file_get_contents(EXTENSION.$extension.DS._CONFIG.DS."urlMappings.json"),true),$urls);
            }
        }

        return $urls;
    }

    public static final function errorPage($code) {
        $urls = urlMapping::_getDefinedUrls();
        if(!empty($urls[$code]['view']))
            return $urls[$code]['view'];
        else if(!empty($urls[substr($code,0,1).'xx']['view']))
            return $urls[substr($code,0,1).'xx']['view'];
        else return null;
    }

    public static final function exceptionPage($name) {
        $urls = urlMapping::_getDefinedUrls();
        if(!empty($urls[$name]['view']))
            return $urls[$name]['view'];
        else if(!empty($urls[$name]['controller']) && !empty($urls[$name]['action']))
            return array('controller'=>$urls[$name]['controller'],'action'=>$urls[$name]['action']);
        else return null;
    }
}