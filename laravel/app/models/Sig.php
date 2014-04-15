<?php

class Sig {
        
    static function parse($sig) {
        $output = array();
        if ( isset($sig)) {
            exec("java -cp /var/www/html/laravel/public/sig.jar SIG -string " . escapeshellcmd($sig) . " 2>&1",$output);
        }
        return $output;
    }
    
}