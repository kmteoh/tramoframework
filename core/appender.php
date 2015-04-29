<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

class appender {

    const SEPERATOR_DAY = 'DAY';
    const SEPERATOR_MEGABYTE = 'M';

    const APPENDER_SCREEN = 'screen';
    const APPENDER_DOMAIN = 'domain';
    const APPENDER_FILE = 'file';

    public $level = logger::WARN;

    private $_appender = 'file'; //file | screen | memory
    private $_filename = '../logger.log'; //file | screen
    private $_seperator = 'DAY';
    private $_format = "%date\t%level\t%file(%line)\t%message";
    private $_template = '<pre>%s</pre>';
    private $_domain = 'userLog';
    private $_exclude = array();

    public function __construct($config) {
        foreach ($config as $key => $value) {
            if($key == 'level') {
                $this->$key = constant("logger::$value");
            } else {
                $this->{"_$key"} = $value;
            }
        }
    }

    public function appendLog($level,$message,$file=null,$line=null) {
        $backtrace = null;
        if(!$file && !$line) {
            $backtraces = array_reverse(debug_backtrace());
            foreach($backtraces as $i => $trace) {
                if(!empty($trace['class']) && $trace['class']=="logger") {
                    $backtrace = $trace;

                    //if it is in exclude list, ignore
                    if(!empty($backtraces[$i-1]) 
                        && is_array($this->_exclude)
                        && !empty($this->_exclude)
                        && !empty($backtraces[$i-1])
                        && in_array($backtraces[$i-1]['class'],$this->_exclude)) {
                        return;
                    }
                    break;
                }
            }
            $file = $backtrace['file'];
            $line = $backtrace['line'];
        }

        $date = date('Y-m-d H:i:s');
        if($this->_appender == appender::APPENDER_DOMAIN) {
            $l = new $this->_domain(array(
                'date' => $date,
                'level' => $level,
                'message' => $message,
                'file' => $file,
                'line' => $line
            ));
            $l->save();
        } else {
            $string = str_replace('%date',$date,
                        str_replace('%level',$level,
                            str_replace('%message',$message,
                                str_replace('%file',$file,
                                    str_replace('%line',$line,$this->_format)
                      ))));
            if($this->_appender == appender::APPENDER_FILE) {
                $dir = dirname($this->_filename);
                @mkdir($dir);
                $zipFilename = $this->_filename.'.'.date('Ymd').'.zip';
                if($this->_seperator == appender::SEPERATOR_DAY && file_exists($this->_filename) && !file_exists($zipFilename)) {
                    $this->archive($zipFilename);
                } else if (strstr($this->_seperator,appender::SEPERATOR_MEGABYTE)) {
                    $zipFilename = $this->_filename.'.'.date('YmdHis').'.zip';
                    $size = str_replace(appender::SEPERATOR_MEGABYTE,'',$this->_seperator);
                    if(file_exists($this->_filename) && (filesize($this->_filename)/1000000) > $size) {
                        $this->archive($zipFilename);
                    }
                }
                file_put_contents($this->_filename, $string.PHP_EOL, FILE_APPEND);
            } else if($this->_appender == appender::APPENDER_SCREEN) {
                echo str_replace('%s',$string,$this->_template);
            }
        }
    }

    private function archive($filename) {
        $zip = new ZipArchive();
        if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
            rename($this->_filename, substr($filename,0,-4));
        } else {
            $zip->addFile(realpath($this->_filename), basename(substr($filename,0,-4)));
            $zip->close();
            file_put_contents($this->_filename,'');
        }
    }
}
