<?php

namespace Db;

class Query {
    
    const FROM = 'from';
    const INNER_JOIN = 'inner join';
    const LEFT_JOIN = 'left outer join';
    const RIGHT_JOIN = 'right outer join';
    const AND_WHERE = 'and';
    const OR_WHERE = 'or';
    
    protected $_from;
    protected $_join;
    protected $_order;
    protected $_group;
    protected $_limit;
    protected $_where;
    protected $_or_where;
    
    protected $_fields;
    
    public function from($table_name, $fields = null) {
        $this->_join($table_name, null, $fields, self::FROM);
        return $this;
    }
    
    public function joinLeft($table_name, $on, $fields = null) {
        $this->_join($table_name, $on, $fields, self::LEFT_JOIN);
        return $this;
    }
    
    public function joinRight($table_name, $on, $fields = null) {
        $this->_join($table_name, $on, $fields, self::RIGHT_JOIN);
        return $this;
    }
    
    public function innerJoin($table_name, $on, $fields = null) {
        $this->_join($table_name, $on, $fields, self::INNER_JOIN);
        return $this;
    }
    
    public function order($str) {
        $this->_order[] = $str;
        return $this;
    }
    
    public function group($str) {
        $this->_group[] = $str;
        return $this;
    }
    
    public function limit($number) {
        $this->_limit = $number;
        return $this;
    }
    
    public function where($where) {
        $this->_parseWhereQuery($where);
        return $this;
    }
    
    public function orWhere($where) {
        $this->_parseWhereQuery($where, self::OR_WHERE);
        return $this;
    }
    
    public function count() {
        $this->_fields = ['count(*) as count'];
        return $this;
    }
    
    public function assemble() {
        return "SELECT " . 
                    $this->_getLimit() . 
                        $this->_getFields() . 
                            $this->_getFrom() . 
                                $this->_getJoin() . 
                                    $this->_getOrder() . 
                                        $this->_getGroup() .
                                            $this->_getWhere();
    }
    
    protected function _parseWhereQuery($where, $cond = self::AND_WHERE) {
        if (is_array($where)) {
            foreach($where as $key => $value) {
                if ($cond == self::OR_WHERE) {
                    $this->_or_where[] = $key . "='" . $value . "'";
                } else {
                    $this->_where[] = $key . "='" . $value . "'";
                }
            }
        } else {
            if ($cond == self::OR_WHERE) {
                $this->_or_where[] = $where;
            } else {
                $this->_where[] = $where;
            }
        }
    }
    
    protected function _join($table_name, $on = null, $fields = null, $cond = null) {
        $prefix = '';
        if (is_array($table_name)) {
            $prefix = key($table_name);
            $table_name = $table_name[$prefix];
        }
        $sql = ' ' . $cond . ' ' . $table_name . ($prefix ? ' as ' . $prefix : '') . ($on ? " on $on" : '');
        if ($cond == self::FROM) {
            $this->_from = $sql;
        } else {
            $this->_join[] = $sql;
        }
        if ($fields) {
            foreach($fields as $field) {
                $this->_fields[] = $field;
            }
        }
    }
    
    protected function _getLimit() {
        return $this->_limit ? "TOP {$this->_limit} " : '';
    }
    
    protected function _getFields() {
        return $this->_fields ? implode(',', $this->_fields) : '*';
    }
    
    protected function _getFrom() {
        return "{$this->_from}";
    }
    
    protected function _getJoin() {
        return $this->_join ? implode(' ', $this->_join) : '';
    }
    
    protected function _getOrder() {
        return $this->_order ? " order by " . implode(',', $this->_order) : '';
    }
    
    protected function _getGroup() {
        return $this->_group ? " group by " . implode(',', $this->_group) : '';
    }
    
    protected function _getWhere() {
        $sql = '';
        if ($this->_where || $this->_or_where) {
            $sql .= " where ";
            if ($this->_where) $sql .= implode(' and ', $this->_convertArray($this->_where, 'windows-1251', 'utf8'));
            if ($this->_where && $this->_or_where) $sql .= ' or ';
            if ($this->_or_where) $sql .= implode(' or ', $this->_convertArray($this->_or_where, 'window-1251', 'utf8'));
        }
        return $sql;
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