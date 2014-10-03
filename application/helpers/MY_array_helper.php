<?php

/**
 * Función de comparación para utilizar con uasort.
 * 
 * @param array $a
 * @param string $b user function
 * @return int
 */
function cmp($a, $b) {
    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}
