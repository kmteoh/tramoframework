<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

class pTaglib {

    public $namespace = 'p';
    public $attributes;
    public $body;

    /* magic taglib */

    public function layoutHead($search, $replace, $subject) {
        return str_replace($search, $replace, $subject);
    }

    /* magic taglib */

    public function layoutBody($search, $replace, $subject) {
        return str_replace($search, $replace, $subject);
    }

    /* magic taglib */

    public function layoutTitle($search, $replace, $subject, $default) {
        return str_replace($search, !empty($replace) ? $replace : $default, $subject);
    }

    public function onload() {
        if (!empty($_SESSION['onload'])) {
            $script = PHP_EOL.'<!--onload-->'.PHP_EOL.'<script>$(function(){' . implode(PHP_EOL, $_SESSION['onload']) . '});</script>'.PHP_EOL.'<!--onload end-->'.PHP_EOL;
            unset($_SESSION['onload']);
            return $script;
        }
    }

    public function enqueueJs() {
        if (!empty($_SESSION['js'])) {
            $script = PHP_EOL.'<!--enqueueJs-->'.PHP_EOL;
            foreach($_SESSION['js'] as $js) {
                $script .= '   <script src='.$js.'></script>'.PHP_EOL;
            }
            unset($_SESSION['js']);
            return $script.PHP_EOL.'<!--enqueueJs end-->'.PHP_EOL;
        }
    }

    public function enqueueCss() {
        if (!empty($_SESSION['css'])) {
            $script = PHP_EOL.'<!--enqueueCss-->'.PHP_EOL;
            foreach($_SESSION['css'] as $css) {
                $script .= '   <link href="'.$css.'" rel="stylesheet">'.PHP_EOL;
            }
            unset($_SESSION['css']);
            return $script.PHP_EOL.'<!--enqueueCss end-->'.PHP_EOL;
        }
    }

    public function debug() {
        $body = '';

        $db = config::getHandler('dataSource');
        if ($error = $db->info('error')) {
            $body .= predump($error, false);
        }
        if ($profile = $db->info('profile')) {
            $body .= $this->dataTable(array('data'=>$profile));
        }
        return $body;
    }

    public function rangeDropDown($attrs = array(), $body = null) {
        $output = '<select name="' . $attrs['name'] . '"';
        $output .= ' id="' . (!empty($attrs['id']) ? $attrs['id'] : $attrs['name']) . '"';
        foreach ($attrs as $key => $value) {
            if (!in_array($key, array('name', 'min', 'max'))) {
                $output .= " $key=\"$value\"";
            }
        }
        $output .= '>';
        $prefix = empty($attrs['prefix']) ? '' : $attrs['prefix'];
        $decimal = empty($attrs['decimal']) ? '0' : $attrs['decimal'];
        $thousand = empty($attrs['thousand']) ? '' : $attrs['thousand'];
        if (!empty($attrs['default'])) {
            $output .= '<option value="">' . $attrs['default'] . '</option>';
        }
        $range = range($attrs['min'], $attrs['max'], (!empty($attrs['step']) ? $attrs['step'] : 1));
        if (!empty($attrs['reverse'])) {
            $range = array_reverse($range);
        }
        foreach ($range as $value) {
            $output .= '<option' . (isset($attrs['value']) && $value == $attrs['value'] ? ' selected' : '') . ' value="'.$value.'">' . $prefix . number_format($value, $decimal, '.', $thousand) . '</option>';
        }
        return $output .= '</select>';
    }

    public function keyValueDropDown($attrs = array(), $body = null) {

		if(empty($attrs['value']) && !empty($attrs['defaultValue'])){
			$attrs['value'] = $attrs['defaultValue'];
		}

        $output = '';

		$output .= '<select name="' . $attrs['name'] . '"';
        $output .= ' id="' . (!empty($attrs['id']) ? $attrs['id'] : $attrs['name']) . '"';
        foreach ($attrs as $key => $value) {
            if (!in_array($key, array('name', 'options', 'value', 'defaultValue'))) {
                $output .= " $key=\"$value\"";
            }
        }
        $output .= '>';
        if (!empty($attrs['default'])) {
            $output .= '<option value="">' . $attrs['default'] . '</option>';
        }
        if (!empty($attrs['options'])) {
            if(!empty($attrs['labelValue'])) { 
                list($label,$value) = explode(',',$attrs['labelValue']);
                for($i=0;$i<count($attrs['options']);$i++) {
                    $selected = (!empty($attrs['value']) && isset($attrs['value']) && ltrim($attrs['options'][$i]->$value) == ltrim($attrs['value']) ? ' selected' : '');
					if(is_object($attrs['options'][$i])) {
                        $output .= '<option value="' . ltrim($attrs['options'][$i]->$value) . '"' . $selected . '>' . $attrs['options'][$i]->$label . '</option>';
                    } else {
                        $output .= '<option value="' . ltrim($attrs['options'][$i][$value]) . '"' . $selected . '>' . $attrs['options'][$i][$label] . '</option>';
                    }
                }
            } else {
				foreach ($attrs['options'] as $value => $label) {
					$output .= '<option value="' . ltrim($value) . '"' . (!empty($attrs['value']) && isset($attrs['value']) && ltrim($value) == ltrim($attrs['value']) ? ' selected' : '') . '>' . $label . '</option>';
                }
            }
        }
        return $output .= '</select>';
    }

    public function hiddenInput($attrs = array(), $body = null) {
        return $this->_input('hidden', $attrs, $body);
    }

    public function textInput($attrs = array(), $body = null) {
        return $this->_input('text', $attrs, $body);
    }

    public function fileInput($attrs = array(), $body = null) {
        return $this->_input('file', $attrs, $body);
    }

    public function passwordInput($attrs = array(), $body = null) {
        return $this->_input('password', $attrs, $body);
    }

    public function emailInput($attrs = array(), $body = null) {
        return $this->_input('email', $attrs, $body);
    }

    public function radioInput($attrs = array(), $body = null) {
        return $this->_input('radio', $attrs, $body);
    }

    private function _input($type, $attrs = array(), $body = null) {
        $output = '<input type="' . $type . '" name="' . $attrs['name'] . '"';
        $output .= ' id="' . (!empty($attrs['id']) ? $attrs['id'] : $attrs['name']) . '"';
        foreach ($attrs as $key => $value) {
            if (!in_array($key, array('name', 'min', 'max', 'required'))) {
                $output .= " $key=\"$value\"";
            }
            if($key == 'required') {
                $output .= " $key";
            }
        }
        $output .= ' />';
        return $output;
    }

    public function listView($attrs=array(),$body=null) {
        $model = array();
        foreach($attrs as $key => $value) {
            if(!in_array($key,array('list','view','var'))) {
                $model[$key] = $value;
            }
        }
        ob_start();
        $i = 0;
		foreach($attrs['list'] as $item) {
            $model[$attrs['var']] = $item;
            view($attrs['view'],$model,$i++);
        }
        return taglib::parseTags(ob_get_clean(),$model);
    }

    public function dataTable($attrs=array(),$body=null) {
        if (empty($attrs['data']))
            return;

        $data = $attrs['data'];
        if(is_object($data[0])) {
            $arrayData = array();
            foreach($data as $d) {
                $arrayData[] = (array) $d;
            }
            $data = $arrayData;
            unset($arrayData);
        }

        $headers = array();
        if(!empty($attrs['headers'])) {
            $headers = explode(',',$attrs['headers']);
        }
        $columns = array();
        if(!empty($attrs['columns'])) {
            $columns = explode(',',$attrs['columns']);
        }
        $output = '<table class="table table-hover'.(!empty($attrs['cssClass'])?' '.$attrs['cssClass']:'').'"'.(!empty($attrs['id'])?' id="'.$attrs['id'].'"':'').'>';
        $output .= '<thead><tr class="row">';
        if(!empty($columns)) {
            foreach($columns as $i => $key) {
                $output .= '<th>' . (!empty($headers[$i]) ? $headers[$i] : ucfirst($key)) . '</th>';
            }
        }

        $output .= '</tr></thead><tbody>';
        foreach ($data as $i => $row) {
            $output .= '<tr class="row r' . $i . '">';
            if(!empty($columns)) {
                foreach ($columns as $key) {
                    $value = $row[$key];
                    $output .= '<td>' . $value . '</td>';
                }
            } else {
                foreach ($row as $key => $value) {
                    $output .= '<td>' . $value . '</td>';
                }
            }
            $output .= '</tr>';
        }
        $output .= '</tbody></table>';

        return $output;
    }
}
