<?php

class restService extends service {

    private $_ch;
    private $_options;
    private $_last_error_code;
    private $_last_error_message;
    private $_info;
    private $_response;
    private $_responseHeaders;

    public function __construct() {
        if (!function_exists('curl_init')) {
            throw new LibraryNotFoundException('cURL runtime library does not seem to be loaded');
        }
        $this->reset();
    }

    public function get($url, $format = 'text', $callback = array()) {
        return $this->_exec($url,$format,$callback);

    }

    public function post($url, $data, $format = 'text') {
        $params = is_array($data) ? http_build_query($data, NULL, '&') : $data;
        $this->option(CURLOPT_POST, TRUE)
            ->option(CURLOPT_POSTFIELDS, $params);
    }

    public function put() {

    }

    public function delete() {

    }

    public function reset() {
        //default
        $this->_options = array();
        $this->_option('TIMEOUT', 30)
            ->_option('RETURNTRANSFER', true)
            ->_option('FOLLOWLOCATION', true)
            ->_option('FAILONERROR', true)
			->_option('ENCODING', 'gzip')
            ->_option('HEADERFUNCTION', array(&$this,'_readHeaders'));
    }

    private function _exec($url,$format,$callback) {
        $this->_ch = curl_init($url);
        curl_setopt_array($this->_ch, $this->_options);
        $this->_response = curl_exec($this->_ch);
        $this->_info = curl_getinfo($this->_ch);

        if ($this->_response === FALSE) {
            $this->_last_error_code = curl_errno($this->_ch);
            $this->_last_error_message = curl_error($this->_ch);
        }

        $response = false;
        if ($this->_info['http_code'] >= 200 && $this->_info['http_code'] <= 299) {
            switch ($format) {
                case "json":
                    $response = json_decode($this->_response, true);
                    break;
                case "xml":
                    $response = simplexml_load_string($this->_response);
                    break;
                default:
                    $response = $this->_response;
                    break;
            }
            if (!empty($callback['success'])) {
                $response = $callback['success']($response);
            }
        } else {
            if (!empty($callback['failed'])) {
                $response = $callback['failed']($this->_last_error_code, $this->_last_error_message);
            }
        }
        return $response;
    }

    private function _option($code, $value, $prefix = 'opt') {
        if (is_string($code) && !is_numeric($code)) {
            $code = constant('CURL' . strtoupper($prefix) . '_' . strtoupper($code));
        }

        $this->_options[$code] = $value;
        return $this;
    }

    private function _readHeaders($ch, $header) {
        //extracting example data: filename from header field Content-Disposition
        return strlen($header);
    }

}