<?php
if(!defined('BASE_PATH')){
    define('BASE_PATH',dirname(__DIR__));
}
spl_autoload_register(static function(string $class):void{
    foreach([
        BASE_PATH.'/src/Core/'.$class.'.php',
        BASE_PATH.'/src/Controllers/'.$class.'.php',
        BASE_PATH.'/src/Models/'.$class.'.php',
        BASE_PATH.'/src/Services/'.$class.'.php',
    ]as$file){
        if(is_file($file)){
            require_once $file;
            return;
        }
    }
});
require_once __DIR__ . '/helpers.php';
