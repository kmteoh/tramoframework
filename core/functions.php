<?php

function getDefinedPath($mode = null) {
    $paths = array();
    switch ($mode) {
        case 'service':
            $folders = array(_SERVICE);
            $paths = array(FS, CORE);
            break;
        case 'view':
            $folders = array(_VIEW);
            $paths = array(FS, CORE);
            break;
        case 'filter':
            $folders = array(_FILTER);
            break;
        default:
            $paths = array(FS, CORE);
            $folders = array(_CONTROLLER, _DOMAIN, _EXCEPTION, _FILTER, _SERVICE, _TAGLIB, _VIEW, _VENDOR);
    }

    $path = array(FS, CORE);
    $config = config::getInstance();
    if ($config->extensions) {
        foreach ($config->extensions as $extension) {
            $path[] = EXTENSION . $extension . DS;
        }
    }

    foreach ($path as $p) {
        foreach ($folders as $f) {
            $paths[] = $p . $f . DS;
        }
    }
    return $paths;
}

function autoloader($name) {
    $paths = getDefinedPath();
    foreach ($paths as $path) {
        if (file_exists($path . $name . '.php')) {
            require_once $path . $name . '.php';
            return true;
        }
    }
    return false;
}

function uniqueFilenameGenerator() {
    return randomDigits(6) . '_' . randomDigits(17) . '_' . time() . '_t';
}

function randomDigits($length) {
    $numbers = range(0, 9);
    shuffle($numbers);
    $digits = '';
    for ($i = 0; $i < $length; $i++)
        $digits .= $numbers[rand(0, 9)];
    return $digits;
}

function predump($data, $toScreen = true) {
    if(php_sapi_name() == "cli" && $toScreen) {
        print_r($data);
    } else {
        $str = '<pre>' . htmlentities(print_r($data, true)) . '</pre>';
        if ($toScreen) {
            echo $str;
        } else {
            return $str;
        }
    }
}

function import($name) {
    $res = autoloader($name);
    if (!$res) {
        throw new FileNotFoundException($name . " library not found!");
    }
}

function customHeaders($headers)
{
    if(headers_sent()) {
        return;
    }
    foreach($headers as $header) {
        header($header);
    }
}

function redirect($url, $headers = array()) {
    $headers[] = "Location: $url";
    customHeaders($headers);
    die();
}

function view($name, $model = null) {
    if (is_array($model))
        extract($model);
    $params = config::getHandler('param');
    $controllerName = $params->controller;
    $action = $params->action;

    if ($params->templateToUse == '') {
        try {
            list($template) = View::getProperties($controllerName . 'Controller', $action, array('template'));
        } catch (Exception $e) {
            //
        }
        if(empty($template)) {
            $template = 'default';
        }
        $params->templateToUse = $template . DS;
    }
    $file = '';
    foreach (getDefinedPath('view') as $path) {
        if (file_exists($path . $params->templateToUse . $name . '.php')) {
            $file = $path . $params->templateToUse . $name . '.php';
            break;
        } else if (file_exists($path . $params->templateToUse . $controllerName . DS . $name . '.php')) {
            $file = $path . $params->templateToUse . $controllerName . DS . $name . '.php';
            break;
        } else if (file_exists($path . $name . '.php')) {
            $file = $path . $name . '.php';
            break;
        } else if (file_exists($path . $controllerName . DS . $name . '.php')) {
            $file = $path . $controllerName . DS . $name . '.php';
            break;
        }
    }
    if(empty($file) && file_exists($name . '.php')) {
        $file = $name . '.php';
    }

    if (!empty($file)) {
        include $file;
        if (!empty($script))
            onload($script);
    } else {
        throw new FileNotFoundException($name . " view not found!");
    }
}

function onload($script) {
    $_SESSION['onload'][] = $script;
}

function enqueueJs($path) {
    $_SESSION['js'][] = $path;
}

function enqueueCss($path) {
    $_SESSION['css'][] = $path;
}

//http://stackoverflow.com/questions/7609095/is-there-an-xml-encode-like-json-encode-in-php
function xml_encode($mixed, $domElement = null, $DOMDocument = null, $styleSheet = null) {
    if (is_null($DOMDocument)) {
        $DOMDocument = new DOMDocument;
        $DOMDocument->formatOutput = true;
        if($styleSheet) {
            $xslt = $DOMDocument->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="'.$styleSheet.'"');
            $DOMDocument->appendChild($xslt);
        }
        xml_encode($mixed, $DOMDocument, $DOMDocument);
        return $DOMDocument->saveXML();
    } else {
        if (is_object($mixed))
            $mixed = (array) $mixed;
        if (is_array($mixed)) {
            foreach ($mixed as $index => $mixedElement) {
                if (is_int($index)) {
                    if ($index === 0) {
                        $node = $domElement;
                    } else {
                        $node = $DOMDocument->createElement($domElement->tagName);
                        $domElement->parentNode->appendChild($node);
                    }
                } else {
                    if ($index == '@attributes') {
                        foreach ($mixedElement as $attrKey => $attrValue) {
                            $domElement->setAttribute($attrKey, $attrValue);
                        }
                        continue;
                    } else {
                        $plural = $DOMDocument->createElement($index);
                        $domElement->appendChild($plural);
                        $node = $plural;
                        if (!(rtrim($index, 's') === $index)) {
                            $singular = $DOMDocument->createElement(rtrim($index, 's'));
                            $plural->appendChild($singular);
                            $node = $singular;
                        }
                    }
                }

                xml_encode($mixedElement, $node, $DOMDocument);
            }
        } else {
            $mixed = is_bool($mixed) ? ($mixed ? 'true' : 'false') : $mixed;
            $domElement->appendChild($DOMDocument->createTextNode($mixed));
        }
    }
}

function csv_encode($mixed) {
    $path = 'php://memory';
    $handle = fopen($path, "rw+");
    fputcsv($handle, array_keys((array) $mixed[0]));
    foreach ($mixed as $row) {
        fputcsv($handle, (array) $row);
    }
    fseek($handle, 0);
    $output = stream_get_contents($handle);
    fclose($handle);
    return $output;
}

function upperToLowerUnderscore($string) {
    $matches = array();

    preg_match_all('/[A-Z]/', substr($string, 1), $matches, PREG_OFFSET_CAPTURE);
    if (count($matches[0])) {
        for ($j = 0; $j < count($matches[0]); $j++) {
            $string = str_replace($matches[0][$j][0], '_' . $matches[0][$j][0], $string);
        }
    }

    return strtolower($string);
}

function lowerUnderscoreToUpper($string) {
    $matches = array();
    $newString = $string;
    preg_match_all('/(_[a-z])+/', $string, $matches);
    if (count($matches[0])) {
        for ($j = 0; $j < count($matches[0]); $j++) {
            $toBe = strtoupper(str_replace('_', '', $matches[0][$j]));
            $newString = str_replace($matches[0][$j], $toBe, $newString);
        }
    } else
        $newString = $string;

    return $newString;
}

function shutdown() {
    $e = error_get_last();
    if ($e['type'] !== E_ERROR)
        return;

    view('error/shutdown', array('type' => errorGetType($e['type']), 'message' => $e['message'], 'file' => $e['file'], 'line' => $e['line'], 'params' => config::getHandler('param')));
}

function uncaughtException($e) {
    view('error/debug', array('class' => get_class($e), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString(), 'params' => config::getHandler('param')));
}

function errorGetType($type) {
    switch ($type) {
        case E_ERROR: // 1 //
            return 'E_ERROR';
        case E_WARNING: // 2 //
            return 'E_WARNING';
        case E_PARSE: // 4 //
            return 'E_PARSE';
        case E_NOTICE: // 8 //
            return 'E_NOTICE';
        case E_CORE_ERROR: // 16 //
            return 'E_CORE_ERROR';
        case E_CORE_WARNING: // 32 //
            return 'E_CORE_WARNING';
        case E_CORE_ERROR: // 64 //
            return 'E_COMPILE_ERROR';
        case E_CORE_WARNING: // 128 //
            return 'E_COMPILE_WARNING';
        case E_USER_ERROR: // 256 //
            return 'E_USER_ERROR';
        case E_USER_WARNING: // 512 //
            return 'E_USER_WARNING';
        case E_USER_NOTICE: // 1024 //
            return 'E_USER_NOTICE';
        case E_STRICT: // 2048 //
            return 'E_STRICT';
        case E_RECOVERABLE_ERROR: // 4096 //
            return 'E_RECOVERABLE_ERROR';
        case E_DEPRECATED: // 8192 //
            return 'E_DEPRECATED';
        case E_USER_DEPRECATED: // 16384 //
            return 'E_USER_DEPRECATED';
    }
    return "";
}

function xmlentities($text, $charset = 'UTF-8') {
    // Debug and Test
    // $text = "test &amp; &trade; &amp;trade; abc &reg; &amp;reg; &#45;";
    // First we encode html characters that are also invalid in xml
    $text = htmlentities($text, ENT_COMPAT, $charset, false);

    // XML character entity array from Wiki
    // Note: &apos; is useless in UTF-8 or in UTF-16
    $arr_xml_special_char = array("&quot;", "&amp;", "&apos;", "&lt;", "&gt;");

    // Building the regex string to exclude all strings with xml special char
    $arr_xml_special_char_regex = "(?";
    foreach ($arr_xml_special_char as $key => $value) {
        $arr_xml_special_char_regex .= "(?!$value)";
    }
    $arr_xml_special_char_regex .= ")";

    // Scan the array for &something_not_xml; syntax
    $pattern = "/$arr_xml_special_char_regex&([a-zA-Z0-9]+;)/";

    // Replace the &something_not_xml; with &amp;something_not_xml;
    $replacement = '&amp;${1}';
    return preg_replace($pattern, $replacement, $text);
}

function microSeconds() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

function extractLinksFromHtml($url, $collects = array()) {
    $htmlAsString = file_get_contents($url);
    $doc = new DOMDocument();
    @$doc->loadHTML($htmlAsString);
    $xpath = new DOMXPath($doc);
    $nodeList = $xpath->query('//a/@href');
    $parsedUrl = parse_url($url);
    for ($i = 0; $i < $nodeList->length; $i++) {
        $sitemap = '';
        if (substr($nodeList->item($i)->value, 0, 1) == '/' && strlen($nodeList->item($i)->value) > 1) {
            $sitemap = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $nodeList->item($i)->value;
        } else if (strstr($nodeList->item($i)->value, $url)) {
            $sitemap = $nodeList->item($i)->value;
        }

        if (!empty($sitemap)) {
            if (!in_array($sitemap, $collects)) {
                $collects[] = $sitemap;
                $collects = extractLinksFromHtml($sitemap, $collects);
            }
        }
    }
    return $collects;
}

function nl2ws($string) {
    return str_replace(array("\r\n","\n"),' ',$string);
}

//http://www.php.net/manual/en/function.filesize.php
function human_filesize($bytes, $decimals = 2) {
  $szLabel = array('K'=>'KB','M'=>'MB','G'=>'GB','T'=>' teraBytes','P'=>' petaBytes');
  $sz = array('B','K','M','G','T','P');
  $factor = floor((strlen($bytes) - 1) / 3);
  if($factor <1) return '1KB';
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $szLabel[$sz[$factor]];
}