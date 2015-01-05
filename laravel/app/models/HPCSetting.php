<?php

class HPCSetting {

	private $name = "";
	private $key = "SystemSettings";

    function __construct($name) {
        $this->name = $name;
    }
    
    static function all() {
        $settings = array();        
        foreach (R::connection()->hkeys('SystemSettings') as $name) {
            $settings[] = new HPCSetting($name);
        }

        if (count($settings) == 0) {

            // no settings defined, create defaults
            R::connection()->hset('SystemSettings', 'LOGO', '/laravel/public/img/logo.png');
            R::connection()->hset('SystemSettings', 'COMPANY', 'Geisinger');
            R::connection()->hset('SystemSettings', 'DIVISION', 'Weis Research');

            R::connection()->hset('SystemSettings', 'BASE_DIR', '/opt/NOW');
            R::connection()->hset('SystemSettings', 'HAS_NODES', 'False');
            R::connection()->hset('SystemSettings', 'EMAIL', 'eblipsky@geisinger.edu');
            
            R::connection()->hset('SystemSettings', 'REDIS_HOST', 'localhost');
            R::connection()->hset('SystemSettings', 'REDIS_PORT', '6379');
            R::connection()->hset('SystemSettings', 'REDIS_DB', '0');

            R::connection()->hset('SystemSettings', 'COUCHDB_HOST', 'http://localhost:5984/');
            R::connection()->hset('SystemSettings', 'COUCHDB_DB', 'pipeline');

            foreach (R::connection()->hkeys('SystemSettings') as $name) {
                $settings[] = new HPCSetting($name);
            }

        }

        return $settings;
    }

    function name() {
        return $this->name;
    }
    
    static function save($logo, $company, $division, $basedir, $hasnodes, $email, $redishost, $redisport, $redisdb, $couchdbhost, $couchdb) {
        R::connection()->hset('SystemSettings','LOGO',$logo);
        R::connection()->hset('SystemSettings','COMPANY',$company);
        R::connection()->hset('SystemSettings','DIVISION',$division);
        R::connection()->hset('SystemSettings','BASE_DIR',$basedir);
        R::connection()->hset('SystemSettings','HAS_NODES',$hasnodes);
        R::connection()->hset('SystemSettings','EMAIL',$email);
        R::connection()->hset('SystemSettings','REDIS_HOST',$redishost);
        R::connection()->hset('SystemSettings','REDIS_PORT',$redisport);
        R::connection()->hset('SystemSettings','REDIS_DB',$redisdb);
        R::connection()->hset('SystemSettings','COUCHDB_HOST',$couchdbhost);
        R::connection()->hset('SystemSettings','COUCHDB_DB',$couchdb);
    }
    
    function value() {
        return R::connection()->hget($this->key,$this->name);
    }                              

}
