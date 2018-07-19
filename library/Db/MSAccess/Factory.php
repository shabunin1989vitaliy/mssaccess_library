<?php

namespace Db\MSAccess;

class Factory {
    
    public static function factory($connection_type, $params = []) {
        switch ($connection_type) {
            case 'pdo':
                $object = new \Db\MSAccess\Pdo($params);
                break;
            case 'odbc':
                $object = new \Db\MSAccess\Odbc($params);
                break;
        }
        return $object;
    }
    
    protected static function ucfisrt_utf($str) {
        return mb_strtoupper(substr($str, 0, 1)) . substr($str, 1);
    }
    
}