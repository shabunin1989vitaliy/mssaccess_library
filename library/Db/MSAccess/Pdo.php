<?php

namespace Db\MSAccess;
use Db\MSAccess\AbstractMSAccess;

class Pdo extends AbstractMSAccess {
    
    protected $_file_path;
    protected $_user = '';
    protected $_password = '';
    protected $_connection;
    protected $_source_name;    
    
    public function __construct($params = null) {
        $this->_file_path = isset($params['file_path']) ? $params['file_path'] : '';
        $this->_user = isset($params['user']) ? $params['user'] : '';
        $this->_password = isset($params['password']) ? $params['password'] : '';
        $this->_source_name = isset($params['source_name']) ? $params['source_name'] : '';        
        try {
            $this->_connection = new PDO("odbc:DRIVER={" . self::DEFAULT_DRIVER . "}; DBQ={$this->_file_path}; Uid={$this->_user}; Pwd={$this->_password};");
        } catch (PDOException $e) {
            echo $this->_convertString($e->getMessage()); exit;
        }
    }
    
    public function fetchAll($where = null, $limit = null) {
        if ($where instanceof \Db\Query) {
            $sql = $where->assemble();
        } else {
            if (!$this->_table_name) $this->_showMessage(self::NO_TABLE);
            $sql = $this->_prepareFetchQuery($where);
            if ($limit) {
                $sql .= " LIMIT $limit";
            }
        }
        return $this->_convertArray($this->_connection->query($sql)->fetchAll(PDO::FETCH_ASSOC));
    }
    
    public function fetchAssoc($where = null, $limit = null) {
        if ($where instanceof \Db\Query) {
            $sql = $where->assemble();
        } else {
            if (!$this->_table_name) $this->_showMessage(self::NO_TABLE);
            $sql = $this->_prepareFetchQuery($where);
            if ($limit) {
                $sql .= " LIMIT $limit";
            }
        }
        $res = $this->_connection->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        if ($res) {
            $result = [];
            foreach($res as $value) {
                $result[$value[key($value)]] = $value;
            }
            $res = $result;
            $res = $this->_convertArray($res);
        }
        return $res;
    }
    
    public function fetchRow($where = null) {
        if ($where instanceof \Db\Query) {
            $sql = $where->assemble();
        } else {
            if (!$this->_table_name) $this->_showMessage(self::NO_TABLE);
            $sql = $this->_prepareFetchQuery($where);
        }
        return $this->_convertArray($this->_connection->query($sql)->fetch(PDO::FETCH_ASSOC));
    }
    
    public function insert($data) {
        if (!$this->_table_name) $this->_showMessage(self::NO_TABLE);
        $data = $this->_convertArray($data, 'windows-1251', 'utf8');
        $placeholders = array_keys($data);
        $placeholders = array_map(function($value) {return ':' . $value;}, $placeholders);
        $this->_connection->prepare("INSERT INTO {$this->_table_name} (" . implode(',', array_keys($data)) . ") values (" . implode(',', $placeholders) . ")")->execute($data);
    }
    
    public function update($data, $where) {
        if (!$this->_table_name) $this->_showMessage(self::NO_TABLE);
        $data = $this->_convertArray($data, 'windows-1251', 'utf8');
        $placeholders = array_keys($data);
        $placeholders = array_map(function($value) {return $value . ' = :' . $value;}, $placeholders);
        $where = $this->_prepareDataQuery($where);
        $this->_connection->prepare("UPDATE {$this->_table_name} SET " . implode(',', $placeholders) . "$where")->execute($data);
    }
    
    public function delete($where) {
        if (!$this->_table_name) $this->_showMessage(self::NO_TABLE);
        $where = $this->_prepareDataQuery($where);
        $this->_connection->prepare("DELETE * FROM {$this->_table_name}$where")->execute();
    }
    
    protected function _showMessage($msg) {
        throw new PDOException($msg);
    }
    
}