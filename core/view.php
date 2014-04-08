<?php

class view {

    public static function extractHeadAndBody($content) {
        $title = $head = $body = null;
        if (strstr($content, '<title>') && strstr($content, '</title>')) {
            $startPos = stripos($content, '<title>') + strlen('<title>');
            $title = substr($content, $startPos, stripos($content, '</title>') - $startPos);
        }
        if (strstr($content, '<head>') && strstr($content, '</head>')) {
            $startPos = stripos($content, '<head>') + strlen('<head>');
            $head = substr($content, $startPos, stripos($content, '</head>') - $startPos);
            //remove title
            $tm = null;
            preg_match('/<title>(.*)<\/title>/', $head, $tm);
            if (count($tm)) {
                $head = str_ireplace($tm[0], '', $head);
            }
        }
        if (strstr($content, '<body>') && strstr($content, '</body>')) {
            $startPos = stripos($content, '<body>') + strlen('<body>');
            $body = substr($content, $startPos, stripos($content, '</body>') - $startPos);
        }
        return array(
            'layoutTitle' => $title,
            'layoutHead' => $head,
            'layoutBody' => $body,
        );
    }

    public static function getProperties($controllerName,$action,$properties) {
        //specific setup from annotations given by controller/action
        $rc = new ReflectionClass($controllerName);
        $controllerDoc = $rc->getDocComment();
        $rm = new ReflectionMethod($controllerName, $action);
        $actionDoc = $rm->getDocComment();

        //get property
        $res = array();
        foreach($properties as $property) {
            $matches = null;
            $returnValue = preg_match("/@$property (.*)/", $actionDoc, $matches);
            $$property = !empty($matches[1]) ? trim($matches[1]) : null;
            if (!$$property) {
                $matches = null;
                $returnValue = preg_match("/@$property (.*)/", $controllerDoc, $matches);
                $$property = !empty($matches[1]) ? trim($matches[1]) : null;
            }
            array_push($res, $$property);
        }

        return $res;
    }

}
