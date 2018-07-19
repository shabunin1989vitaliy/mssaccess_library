<?php

namespace Db\MSAccess;

use Db\MSAccess\AbstractMSAccess;

class Odbc extends AbstractMSAccess {
    
    protected $_driver;
    protected $_file_path;
    protected $_source_name;
    protected $_user;
    protected $_password;
    protected $_connection;
    
    public function __construct($params = null) {
        $this->_source_name = $params['source_name'];
        $this->_driver = $params['driver'] ? $params['driver'] : self::DEFAULT_DRIVER;
        $this->_file_path = $params['file_path'];
        $this->_user = $params['user'];
        $this->_password = $params['password'];
        $this->_connection = odbc_connect($this->_source_name ? $this->_source_name : "Driver={{$this->_driver}};Dbq={$this->_file_path}", $this->_user, $this->_password);
        $this->_isError();
    }
    
    public function fetchAll($where = null, $limit = null) {
        if ($where instanceof \Db\Query) {
            $sql = $where->assemble();
        } else {
            if (!$this->_name) $this->_showMessage(self::NO_TABLE);
            $sql = $this->_prepareFetchQuery($where);
            if ($limit) {
                $sql = str_replace("SELECT", "SELECT TOP $limit", $sql);
            }
        }
        
        $res = odbc_exec($this->_connection, $sql);
        if ($res) {
            $result = [];
            while($row = odbc_fetch_array($res)) {
                $result[] = $row;
            }
        }
        $this->_isError();
        return $result ? $this->_convertArray($result) : null;
    }
    
    public function fetchAssoc($where = null, $limit = null) {
        if ($where instanceof \Db\Query) {
            $sql = $where->assemble();
        } else {
            if (!$this->_name) $this->_showMessage(self::NO_TABLE);
            $sql = $this->_prepareFetchQuery($where);
            if ($limit) {
                $sql = str_replace("SELECT", "SELECT TOP $limit", $sql);
            }
        }
        $res = odbc_exec($this->_connection, $sql);
        if ($res) {
            $result = [];
            while($row = odbc_fetch_array($res)) {
                $result[$row[key($row)]] = $row;
            }
        }
        $this->_isError();
        return $result ? $this->_convertArray($result) : null;
    }
    
    public function fetchRow($where = null) {
        if ($where instanceof \Db\Query) {
            $sql = $where->assemble();
        } else {
            if (!$this->_name) $this->_showMessage(self::NO_TABLE);
            $sql = $this->_prepareFetchQuery($where);
        }
        $res = odbc_exec($this->_connection, $sql);
        if ($res) {
            $result = odbc_fetch_array($res);
        }
        $this->_isError();
        return $result ? $this->_convertArray($result) : null;
    }
    
    public function insert($data) {
        if (!$this->_name) $this->_showMessage(self::NO_TABLE);
        $data = $this->_convertArray($data, 'windows-1251', 'utf8');
        $sql = "INSERT INTO {$this->_name} (" . implode(',', array_keys($data)) . ") VALUES ('" . implode("','", array_values($data)) . "')";
        odbc_exec($this->_connection, $sql);
        $this->_isError();
        return true;
    }
    
    public function update($data, $where) {
        if (!$this->_name) $this->_showMessage(self::NO_TABLE);
        $data = $this->_convertArray($data, 'windows-1251', 'utf8');
        if ($data) {
            $str = [];
            foreach($data as $key => $value) {
                $str[] = $key . "='" . $value . "'";
            }
            $str = implode(',', $str);
            $where = $this->_prepareDataQuery($where);
            $sql = "UPDATE {$this->_name} SET $str$where";
            odbc_exec($this->_connection, $sql);
            $this->_isError();
            return true;
        }
        return false;
    }
    
    public function delete($where) {
        if (!$this->_name) $this->_showMessage(self::NO_TABLE);
        $where = $this->_prepareDataQuery($where);
        $sql = "DELETE * FROM {$this->_name}$where";
        odbc_exec($this->_connection, $sql);
        $this->_isError();
        return true;
    }
    
    public function getTables() {
        $res = odbc_tables($this->_connection);
        $tables = [];
        while($row = odbc_fetch_array($res)) {
            if ($row['TABLE_TYPE'] == 'TABLE') $tables[] = $row['TABLE_NAME'];
        }
        return $tables;
    }
    
    protected function _isError() {
        if (odbc_error()) {
            $this->_showMessage(odbc_errormsg($this->_connection));
        }
    }
    
    protected function _showMessage($msg) {
        throw new \Exception($this->_convertString($msg));
    }
    
}