<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_Form_validation extends CI_Form_validation {

    function __construct($rules = array()) {
        parent::__construct($rules);
    }

    /**
     * Error Count
     *
     * Returns the the number of errors
     *
     * @access    public
     * @return    int
     */
    function error_count() {
        return count($this->_error_array);
    }

}
