<?php
class Autoloader {

    public static function register(){
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    public static function autoload($class){
        $names = explode('\\', $class);

        $class_name = array_pop($names);
        $class_path = AUTO_LOADER_PATH;

        foreach ($names as $name){
            $class_path .= $name.DIRECTORY_SEPARATOR;
        }
        $class_path .= $class_name.'.php';

        if (!file_exists($class_path)){
            throw new \Exception("File '$class_path' do not exist" );
        }
        require_once "$class_path";
    }
}
