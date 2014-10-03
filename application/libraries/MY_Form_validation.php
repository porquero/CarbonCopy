<?php

/**
 * Extend Form_validation to add rules
 *
 * @author porquero
 */
class MY_Form_validation extends \CI_Form_validation {

        /**
     * Validate dates format YYYY-MM-DD
     * 
     * @param type $string
     * @return type
     */
    public function valid_date_YYYYMMDD($string) {
        return (bool) preg_match('/\d{4}-(0[1-9]|1[0-2])-(0[1-9]|1[0-9]|2[0-9]|3(0|1))/', $string);
    }

}
