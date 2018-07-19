<?php

namespace Db;

interface InterfaceDb {
    
    public function __construct($params = null);
    
    public function fetchAll($where = null);
    
    public function fetchRow($where = null);
    
    public function fetchAssoc($where = null);
    
    public function insert($data);
    
    public function update($data, $where);
    
    public function delete($where);
    
}