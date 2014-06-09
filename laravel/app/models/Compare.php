<?php

class Compare {
    
    //todo add other compare functions as needed
    
    public static function cmp($a,$b) {
        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }
    
    public static function cmp_priority($a, $b) {
        
        $ap = $a->priority();
        $bp = $b->priority();
        
        if ($ap == $bp) {
            return 0;
        }
        return ($ap < $bp) ? -1 : 1;
    }
}

?>
