<?php

class Autoloader {
    
    public static function init() {
        spl_autoload_register(function($class) {
            require_once __DIR__ . '/' . str_replace(substr('\/', 0, 1), '/', $class) . '.php';
        });
    }
    
}