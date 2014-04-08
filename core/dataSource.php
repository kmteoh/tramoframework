<?php

class dataSource {

    private $_dhb;
    private $_profiling = false;
    private $_caching = false;

    private $_lastResult = array();
    private $_lastQuery = '';
    private $_lastError = '';
    private $_lastQueryWithError = '';

    private $_profilingResults = array();

    public $numRows = 0;
    public $lastInsertId = null;

    private static $instance;

    private function __construct($dsn, $user='', $password='', $options=array()) {
        $this->_dhb = new PDO($dsn, $user, $password, $options);
    }

    public static function getInstance($label='',$dsn='', $user='', $password='', $options=array()) {
        if(empty($label)) {
            $label = 'dataSource';
        }
        if (empty(dataSource::$instance[$label])) {
            dataSource::$instance[$label] = new dataSource($dsn, $user, $password, $options);
        }
        return dataSource::$instance[$label];
    }

    public function first($query) {
        $numRows = $this->query($query);
        return $numRows ? $this->_lastResult[0] : null;
    }

    public function last($query) {
        $this->query($query);
        return $this->numRows ? $this->_lastResult[$this->numRows -1] : null;
    }

    public function results($query) {
        $this->query($query);
        return $this->_lastResult;
    }

    public function save($table, $data, $whereClause='', $forceInsert=false) {
        if ($forceInsert || empty($whereClause)) {
            $query = 'insert into ' . $table . ' (';

            $fields = array_keys($data);
            $query .= implode(',',$fields) . ') values (';

            $values = array();
            foreach($data as $value) {
                if($value === 0 || $value === false) {
                    $values[] = $value;
                } else {
                    switch (strtolower($value)) {
                        case 'now()':
                            $values[] = $this->_dhb->quote(date(DATE_ISO8601));
                            break;
                        case 'null':
                        case null:
                            $values[] = 'null';
                            break;
                        default:
                            $values[] = $this->_dhb->quote($value);
                            break;
                    }
                }
            }
            $query .= implode(',',$values) . ')';

        } else {
            $query = 'update ' . $table . ' set ';
            while (list($columns, $value) = each($data)) {
                if($value === 0 || $value === false) {
                    $query .= $columns . ' = \'0\', ';
                } else {
                    switch (strtolower($value)) {
                        case 'now()':
                            $query .= $columns . $this->_dhb->quote(date(DATE_ISO8601));
                            break;
                        case 'null':
                        case null:
                            $query .= $columns . ' = null, ';
                            break;
                        default:
                            $query .= $columns . ' = '.$this->_dhb->quote($value).', ';
                            break;
                    }
                }
            }
            $query = substr($query, 0, -2) . ' where ' . $whereClause;
        }
        return $this->query($query);
    }

    public function query($query) {
        $this->_lastQuery = $query;
        $useCachedResult = false;
        
        if($this->_profiling) {
            $startTime = microSeconds();
        }

        if ( preg_match("/^(insert|delete|update|replace|drop|create)\s+/i",$query) ){
            $this->numRows = $this->_dhb->exec($query);
            if($this->_hasError()) {
                return false;
            }
            if (preg_match("/^(insert|replace)\s+/i",$query)) {
                $this->lastInsertId = $this->_dhb->lastInsertId();
            }
        } else {
            if($this->_caching) {
                $cache = config::getHandler('cache');
                $lastCache = $cache->get($query);
                if($lastCache) {
                    $this->_lastResult = json_decode($lastCache);
                    $this->numRows = count($this->_lastResult);
                    $useCachedResult = true;
                }
            }

            if(!$useCachedResult) {
                $sth = $this->_dhb->prepare($query); 
                if($this->_hasError()) {
                    return false;
                }
                $sth->execute();
                $this->_lastResult = $sth->fetchAll(PDO::FETCH_CLASS);
                $this->numRows = count($this->_lastResult);
            }

            if($this->_caching && !$useCachedResult) {
                $cache = config::getHandler('cache');
                $lastCache = $cache->set($query,json_encode($this->_lastResult));
            }
        }

        if($this->_profiling) {
            $taken = microSeconds() - $startTime;
            $this->_profilingResults[] = array(
                'query' => $query,
                'time' => $taken,
                'useCachedResult' => $useCachedResult?'yes':'no'
            );
        }

        return $this->numRows;
    }

    public function escape($s) {
        switch (gettype($s)) {
            case 'string' :
                $s = addslashes(stripslashes($s));
                break;

            case 'boolean' :
                $s = ($s === FALSE) ? 0 : 1;
                break;

            default :
                $s = ($s === NULL) ? 'NULL' : $s;
                break;
        }

        return $s;
    }

    public function info($mode,$tableName='') {
        $res = array();
        switch($mode) {
            case 'error':
                if(empty($this->_lastError)) return null;
                $res = array(
                    'error' => $this->_lastError,
                    'query' => $this->_lastQueryWithError
                );
                break;

            case 'column':
                $sth = $this->_dhb->query("select * from $tableName limit 1");
				$col_count = $sth->columnCount();
				for ( $i=0 ; $i < $col_count ; $i++ ) {
					$cols = new stdClass();
					if ( $meta = $sth->getColumnMeta($i) ) {
                        $res[$i] = (object) $meta;
					}
				}
                break;

            case 'profile':
                $res = $this->_profilingResults;
                break;
            
            default:
                $res = false;
        }

        return $res;
    }

    public function enableProfiling() {
        $this->_profiling = true;
    }

    public function disableProfiling() {
        $this->_profiling = false;
    }

    public function enableCaching() {
        $this->_caching = true;
    }

    public function disableCaching() {
        $this->_caching = false;
    }

    public function beginTransaction() {
        return $this->_dhb->beginTransaction();
    }

    public function commit() {
        return $this->_dhb->commit();
    }

    public function rollback() {
        return $this->_dhb->rollBack();
    }

    private function _hasError() {
        $e = $this->_dhb->errorInfo();
        if(isset($e[1])) {
            $this->_lastError = implode(', ',$e);
            $this->_lastQueryWithError = $this->_lastQuery;
            return true;
        }

        return false;
    }
}
