<?php

class domain {

    public function __construct($data = null, $ignoreNotExistanceVar = false) {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $property = lowerUnderscoreToUpper($key);
                if (property_exists($this, $property)) {
                    $this->$property = $value;
                } else if(!$ignoreNotExistanceVar){
                    $this->$key = $value;
                }
            }
        }
    }

    public function isNew() {
        return empty($this->id);
    }

    public function save($forceInsert = false) {
        //TODO: walk fields, generate data array
        $data = array();
        $whereClause = '';
        foreach (get_object_vars($this) as $name => $value) {
            if (is_object($value)) {
                $value->save();
                $this->{$name.'_id'} = $data[upperToLowerUnderscore($name.'_id')] = $value->id;
            } else if(is_array($value) && !empty($value) && !in_array($name,array('belongsTo','hasMany','manyToMany'))) {
                foreach($value as $el) {
                    $el->save();
                }
            } else if (is_bool($value)) {
                $data[upperToLowerUnderscore($name)] = $value?1:0;
            } else if ($name == 'id' && !empty($value) && !$forceInsert) {
                $whereClause = "id='$value'";
            } else if ($name == 'dateCreated' && ((property_exists($this, 'id') && $this->id == '') || $forceInsert)) {
                $this->$name = $data[upperToLowerUnderscore($name)] = date(DATE_ISO8601);
            } else if ($name == 'dateUpdated') {
                $this->$name = $data[upperToLowerUnderscore($name)] = date(DATE_ISO8601);
            } else if (!in_array($name,array('belongsTo','hasMany','manyToMany'))) {
                $data[upperToLowerUnderscore($name)] = $value;
            }
        }

        $db = $this->_getDataSource();
        $numRows = $db->save($this->_getName(true), $data, $whereClause, $forceInsert);
        if((empty($whereClause) || $forceInsert)) {
            $this->id = $db->lastInsertId;
            return $this->id;
        } else {
            return $numRows;
        }
    }

    public function delete() {
        $db = $this->_getDataSource();
        $query = 'delete from ' . $this->_getName(true) . ' where id=\'' . $this->id . '\'';
        return $db->query($query);
    }

    public final function __set($name, $value) {
        $this->$name = $value;
    }

    public final function __get($name) {
        if(property_exists($this, 'hasMany')) {
            foreach($this->hasMany as $property) {
                $col = $domain = null;
                if(is_array($property)) {
                    foreach($property as $field => $table) {
                        if($field == $name) {
                            if($table == $this->_getName()) {
                                $col = $this->_foreignKey($table);
                                $domain = $table;
                            } else {
                                $col = $this->_getName();
                                $domain = $table;
                            }
                        } else {
                            $col = $field;
                            $domain = $table;
                        }
                    }
                } else {
                    if($property == $name) {
                        $col = $domain = $name;
                    }
                }

                if($col) {
                    $fx = 'findAllBy'.ucfirst($col).'Id';
                    $this->$name = $domain::$fx($this->id);
                }
            }
        }

        return property_exists($this, $name) ? $this->$name : '';
    }

    public function __call($method, $arguments) {
        return $this->_dynamicQueryResolver($method, $arguments);
    }

    public static function __callStatic($method, $arguments) {
        $class = get_called_class();
        $obj = new $class();
        return $obj->_dynamicQueryResolver($method, $arguments);
    }

    private function _foreignKey($tableNameToMatch) {
        if(property_exists($this, 'belongsTo')) {
            foreach($this->belongsTo as $property) {
                if(is_array($property)) {
                    foreach($property as $field => $table) {
                        if($table == $tableNameToMatch) {
                            return $field;
                        }
                    }
                } else {
                    if($property == $tableNameToMatch) {
                        return $property;
                    }
                }
            }
        }
        return false;
    }

    private function _dynamicQueryResolver($method, $arguments) {
        $criteria = $option = null;
        $fxMatches = array();
        preg_match_all('/(get|findBy|findAllBy|findAll|countBy|where|findMany|associate)/', $method, $fxMatches);
        $args = array();

        //workout method name
        switch ($fxMatches[0][0]) {
            case "findBy":
            case "findAllBy":
            case "countBy":
                $parts = explode('And', str_replace($fxMatches[0][0], '', $method));
                $partsCount = count($parts);
                $argumentIdx = 0;
                $criterias = array();
                for ($i = 0; $i < count($parts); $i++) {
                    $innerParts = explode('Or', $parts[$i]);
                    $innerCriterias = array();
                    for($j=0;$j<count($innerParts);$j++) {
                        list($argumentIdx,$retCriteria) = self::_dynamicQueryBuilder($innerParts[$j], $arguments, $argumentIdx);
                        $innerCriterias[] = $retCriteria;
                    }
                    $criterias[] = count($innerCriterias)>1 ? '('.implode(' or ', $innerCriterias).')' : $innerCriterias[0];
                }
                $criteria = implode(' and ', $criterias);
                if (count($arguments) == $partsCount + 1) {
                    $option = call_user_func_array(array($this,'_options'),array($arguments[$partsCount]));
                }

                $args[0] = $criteria;
                $args[1] = $option;
                break;

            case "findAll":
                $args[0] = call_user_func_array(array($this,'_options'),$arguments);
                break;

            case "findMany":
            case "get":
            case "where":
                $args = $arguments;
                break;
        }
        $method = "_" . $fxMatches[0][0];
        if (!method_exists($this, $method))
            throw new BadFunctionCallException($method . " method not found in " . $this->_getName());
        return call_user_func_array(array($this, $method), $args);
    }

    private static function _dynamicQueryBuilder($part,$arguments,$argumentIdx){
        $matches = array();
        $equal = "='$1'";
        $field = $part;
        preg_match_all('/(LessThanEquals|LessThanEqual|LessThan|GreaterThanEquals|GreaterThanEqual|GreaterThan|Between|Like|Not|InList|IsNull|IsNotNull)/', $part, $matches);
        if (count($matches[0])) {
            switch ($matches[0][0]) {
                case "Not":
                    $equal = "!='$1'";
                    break;
                case "LessThanEquals":
                case "LessThanEqual":
                    $equal = "<='$1'";
                    break;
                case "LessThan":
                    $equal = "<'$1'";
                    break;
                case "GreaterThanEquals":
                case "GreaterThanEqual":
                    $equal = ">='$1'";
                    break;
                case "GreaterThan":
                    $equal = ">'$1'";
                    break;
                case "Between":
                    $equal = " between '$1' and '$2'";
                    break;
                case "Like":
                    $equal = " like '%$1%'";
                    break;
                case "InList":
                    $equal = " in ($1)";
                    break;
                case "IsNull":
                    $equal = " is null";
                    break;
                case "IsNotNull":
                    $equal = " is not null";
                    break;
            }
            $field = str_replace($matches[0][0], '', $part);
        }
        if (strstr($equal, '$1')) {
            $equal = (is_object($arguments[$argumentIdx]) ? '_id ' : '') . $equal;
            if(!empty($matches[0][0]) && $matches[0][0] == 'InList') {
                if(is_string($arguments[$argumentIdx])) {
                    $value = $arguments[$argumentIdx];
                } else if(is_array($arguments[$argumentIdx])) {
                    $value = "'".implode("','",$arguments[$argumentIdx])."'";
                }
            } else {
                $value = is_object($arguments[$argumentIdx]) ? $arguments[$argumentIdx]->id : $arguments[$argumentIdx];
            }
            $equal = str_replace('$1', $value, $equal);
            $argumentIdx++;
        }
        if (strstr($equal, '$2')) {
            $value = is_object($arguments[$argumentIdx]) ? $arguments[$argumentIdx]->id : $arguments[$argumentIdx];
            $equal = str_replace('$2', $value, $equal);
            $argumentIdx++;
        }
        return array($argumentIdx,upperToLowerUnderscore($field) . $equal);
    }

    private function _loadChildNodes() {
        if(property_exists($this, 'belongsTo')) {
            foreach($this->belongsTo as $property) {
                if(is_array($property)) {
                    foreach($property as $field => $table) {
                        if($this->{upperToLowerUnderscore($field).'_id'} != '') {
                            $t = new $table();
                            $this->$field = $t->_get($this->{upperToLowerUnderscore($field).'_id'});
                        }
                        unset($this->{upperToLowerUnderscore($field).'_id'});
                    }
                } else {
                    $propertyFormatted = upperToLowerUnderscore($property);
                    if($this->{$propertyFormatted.'_id'} != '') {
                        $this->$property = $property::get($this->{$propertyFormatted.'_id'});
                    }
                    unset($this->{$propertyFormatted.'_id'});
                }
            }
        }
    }

    private function _options($options=null) {
        if(empty($options)) return '';
        $option = ' ';
        if (!empty($options['sort'])) {
            $option .= "order by " . upperToLowerUnderscore($options['sort']);
        }
        if (!empty($options['order'])) {
            $option .= ' ' . $options['order'];
        }
        if (!empty($options['offset']) || !empty($options['max'])) {
            $option .= ' limit ' . (empty($options['offset']) ? '0' : $options['offset']) . ',';
            $option .= empty($options['max']) ? '1' : $options['max'];
        }
        return $option;
    }

    private function _optionsOnResults($results,$options=null) {
        if(empty($options)) return $results;

        if(!empty($options['sort'])) {
            $fx = function ($key,$reverse=false) {
                return function ($a, $b) use ($key,$reverse) {
                    return strnatcmp($a->$key, $b->$key) * ($reverse?-1:1);
                };
            };
            if(!empty($options['order']) && $options['order']=='desc') {
                usort($results, $fx($options['sort'],true));
            } else {
                usort($results, $fx($options['sort']));
            }
        }
        if(!empty($options['offset'])) {
            $offset = $options['offset'];
            $max = !empty($options['max']) ? $options['max'] : null;
            $results = array_slice($results, $offset, $max);
        }

        return $results;
    }

    private function _get($id) {
        return $this->_findBy("id='$id'");
    }

    private function _countBy($criteria='') {
        $tableName = $this->_getName(true);
        $db = $this->_getDataSource();
        $query = "select count(*) as total from $tableName";
        if (!empty($criteria))
            $query .= " where " . $criteria;
        $row = $db->first($query);
        return $row ? $row->total : 0;
    }

    private function _findBy($criteria='',$option='') {
        $tableName = $this->_getName(true);
        $db = $this->_getDataSource();
        $query = "select * from $tableName";
        if (!empty($criteria))
            $query .= " where " . $criteria;
        if (!empty($option))
            $query .= ' ' . $option;
        $query .= " limit 0,1";
        $row = $db->first($query);
        if(!$row) {
            return false;
        }
        $controllerName = $this->_getName();
        $obj = new $controllerName($row);
        $obj->_loadChildNodes();
        return $obj;
    }

    private function _findAllBy($criteria='',$option='') {
        $tableName = $this->_getName(true);
        $db = $this->_getDataSource();
        $query = "select * from $tableName";
        if (!empty($criteria)) {
            $query .= " where " . $criteria;
        }
        $query .= $option;
        $rows = $db->results($query);
        $results = array();
        if (count($rows)) {
            foreach ($rows as $row) {
                $controllerName = $this->_getName();
                $obj = new $controllerName($row);
                $obj->_loadChildNodes();
                $results[] = $obj;
            }
        }
        return $results;
    }

    private function _findAll($option='') {
        $db = $this->_getDataSource();
        $query = 'select * from ' . $this->_getName(true);
        $query .= $option;
        $rows = $db->results($query);
        $results = array();
        if (count($rows)) {
            foreach ($rows as $row) {
                $controllerName = $this->_getName();
                $obj = new $controllerName($row);
                $obj->_loadChildNodes();
                $results[] = $obj;
            }
        }
        return $results;
    }

    private function _findMany($targetObj,$options=array()) {
        $assoTableName = $this->_getAssociateTable($targetObj);
        $db = $this->_getDataSource();
        $colName = $this->_getName().'_id';
        $query = 'select '.$this->_getName().'_id from ' . $assoTableName . ' where '.get_class($targetObj).'_id = ' . $targetObj->id;
        $rows = $db->results($query);
        $results = array();
        if (count($rows)) {
            foreach ($rows as $row) {
                $controllerName = $this->_getName();
                $obj = $controllerName::get($row->$colName);
                $obj->_loadChildNodes();
                $results[] = $obj;
            }
        }
        return $this->_optionsOnResults($results, $options);
    }

    private function _where($criteria, $option='') {
        $db = $this->_getDataSource();
        $query = 'select * from ' . $this->_getName(true) . ' where ' . $criteria;
        $query .= $option;
        $rows = $db->results($query);
        $results = array();
        if (count($rows)) {
            foreach ($rows as $row) {
                $controllerName = $this->_getName();
                $obj = new $controllerName($row);
                $obj->_loadChildNodes();
                $results[] = $obj;
            }
        }
        return $results;
    }

    private function _getName($tableName=false) {
        $name = get_class($this);
        if($tableName) {
            $ref = new ReflectionClass($name);
            $const = $ref->getConstant('TABLE_NAME');
            if(!empty($const)) {
                $name = $const;
            }
            return upperToLowerUnderscore($name);
        } else {
            return $name;
        }
    }

    private function _getDataSource() {
        $name = get_class($this);
        $ref = new ReflectionClass($name);
        $label = $ref->getConstant('DATA_SOURCE');
        if(empty($label)) {
            $label = 'dataSource';
        }
        return config::getHandler('dataSource',$label);
    }

    private function _getAssociateTable($targetObj) {
        $targetObjName = get_class($targetObj);
        $assoTableName = null;
        if(property_exists($this, 'manyToMany')) {
            foreach($this->manyToMany as $mtm) {
                if(in_array($this->_getName(),$mtm) && in_array($targetObjName,$mtm)) {
                    $assoTableName = implode('_',$mtm) . '_link';
//                    $targetObjName = $mtm[0] == $this->_getName() ? $mtm[1] : $mtm[0];
                    break;
                }
            }
        }
        return $assoTableName;
    }

    public function linkAssociate($targetObj) {
        $db = $this->_getDataSource();
        $assoTableName = $this->_getAssociateTable($targetObj);
        $query = 'replace into ' . $assoTableName . ' ('.get_class($targetObj).'_id,'.$this->_getName().'_id) values (' . $targetObj->id . ',' . $this->id.')';
        return $db->first($query);
    }

    public function unlinkAssociate($targetObj) {
        $db = $this->_getDataSource();
        $assoTableName = $this->_getAssociateTable($targetObj);
        $query = 'delete from ' . $assoTableName . ' where '.get_class($targetObj).'_id = ' . $targetObj->id . ' and '.$this->_getName().'_id = ' . $this->id;
        return $db->first($query);
    }
}
