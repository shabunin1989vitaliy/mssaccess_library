<?php

namespace Db\MSAccess;
use Db\InterfaceDb;

abstract class AbstractMSAccess implements InterfaceDb {
    
    protected $_name;
    
    const DEFAULT_DRIVER = 'Microsoft Access Driver (*.mdb, *.accdb)';
    const NO_TABLE = 'Table not set';
    
    public function setTable($name) {
        $this->_name = $name;
        return $this;
    }
    
    public function select() {
        return new \Db\Query();
    }
    
    protected function _prepareFetchQuery($where = null) {
        if ($where) {
            if (!is_array($where)) {
                $where = ' WHERE ' . $where;
            } else {
                $where_vars = [];
                foreach($where as $key => $value) {
                    if (!is_numeric($value)) {
                        $where_vars[] = "$key = '$value'";
                    } else {
                        $where_vars[] = "$key = $value";
                    }
                }
                $where = ' WHERE ' . implode(' AND ', $where_vars);
            }
        } else {
            $where = '';
        }
        return "SELECT * FROM {$this->_name}$where";
    }
    
    protected function _prepareDataQuery($where) {
        if ($where) {
            if (!is_array($where)) {
                $where = ' WHERE ' . $where;
            } else {
                $where_vars = [];
                foreach($where as $key => $value) {
                    if (!is_numeric($value)) {
                        $where_vars[] = "$key = '$value'";
                    } else {
                        $where_vars[] = "$key = $value";
                    }
                }
                $where = ' WHERE ' . implode(' AND ', $where_vars);
            }
        } else {
            $where = '';
        }
        return $where;
    }
    
    protected function _convertArray($array, $to = 'utf8', $from = 'windows-1251') {
        foreach($array as $key => &$value) {
            if (is_array($value)) {
                $value = $this->_convertArray($value, $to, $from);
            } else {
                $value = $this->_convertString($value, $to, $from);
            }
        }
        unset($value);
        return $array;
    }
    
    protected function _convertString($string, $to = 'utf8', $from = 'windows-1251') {
        return mb_convert_encoding($string, $to, $from);
    }
    
}