<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

class taglib {
    public function __construct() {
        
    }

    public static function getTaglibs() {
        $taglibs = array();
        foreach(array(TAGLIB,CORETAGLIB) as $path)
        $files = scandir($path);
        if(count($files)) {
            foreach($files as $file) {
                if(is_file($path.$file)) {
                    $tName = substr($file,0,-4);
                    if(property_exists($tName,'namespace')) {
                        $t = new $tName();
                        $taglibs[$tName] = $t->namespace;
                    }
                }
            }
        }
        return $taglibs;
    }

    public static function parseLayoutTags($html=null,$model=null) {
        $m = null;
        $count = preg_match_all('/<p:layout(.*)\/>/',$html,$m);
        if($count) {
            $taglib = new pTaglib();
            for($i=0;$i<count($m[0]);$i++) {
                $a = explode(' ',trim($m[1][$i]));
                $attrs = array();
                if(count($a)>1) {
                    for($j=1;$j<count($a);$j++) {
                        $b = explode('=',$a[$j]);
                        $attrs[$b[0]] = str_replace('"','',$b[1]);
                    }
                    $html = $taglib->{'layout'.$a[0]}($m[0][$i],$model['layout'.$a[0]],$html,$attrs['default']);
                } else
                    $html = $taglib->{'layout'.$m[1][$i]}($m[0][$i],$model['layout'.$m[1][$i]],$html);
            }
        }
        return $html;
    }

    public static function parseTags($html=null,$model=null) {
        if(empty($html)) return '';

        //find taglib in use within content
        $m = null;
        preg_match_all('/<(\w+):(.*)\/>/',substr($html,1),$m);

        //found some taglibs being used. proceed
        if(!empty($m[0])) {
            for($i=0;$i<count($m[0]);$i++) {
                //find attributes and body
                $attrs = array();
                $string = str_replace('&','&amp;',$m[0][$i]);
                $xml = @simplexml_load_string($string);
                if($xml && count($xml->attributes())){
                    foreach($xml->attributes() as $key => $value) {
                        if(substr($value,0,1)=='$') {
                            $var = substr($value,1);
                            $attrs[$key] = isset($model[$var]) ? $model[$var] : $value;
                        } else {
                            $attrs[$key] = (string) $value;
                        }
                    }
                }
                $body = (string) $m[0][$i];

                //find the taglib name being used
                $findMethod = array();
                preg_match('/(\w*)\s(.*)/',$m[2][$i],$findMethod);
                $method = $findMethod[1];

                //do it
                $class = $m[1][$i] . 'Taglib';
                if(!class_exists($class))
                    throw new TaglibNotFoundException("Taglib $class not exists!");
                if(!method_exists($class,$method))
                    throw new TaglibPropertyNotFoundException("Taglib property $method not exists!");
                $taglib = new $class();
                $newBody = in_array($method,array('debug'))?$taglib->$method(config::getInstance()):$taglib->$method($attrs,$body);
                $html = str_replace($m[0][$i],$newBody,$html);
            }
        }
        return $html;
    }
}
/*
            <p:mytag attr="1111" rel="aaaa">
                thththt
            </p:mytag>
            <span>asdfas</span>
            <p:mytag attr="2222" rel="bbbb">
                sdfgsdfg
                <p:mytag attr="5555" rel="eeee">
                    DFDFDFDFFD
                </p:mytag>
                sdfg sdfg
            </p:mytag>
            <span>asdf</span>
            <p:mytag attr="3333" rel="cccc" /> <span>asdf</span>
            <p:mytag attr="4444" rel="dddd"/>
            <p:mytag />
            <p:mytag/><span>asdf</span>
            <p:mytag attr="6666" rel="ffff">
                HHHHHH
                <p:mytag attr="7777" rel="gggg">
                    XZXZX
                </p:mytag>
            </p:mytag>
 * 
 */