<?php

class MY_Session extends CI_Session {

    function __construct($params = array()) {
        parent::__construct($params);
    }

    function flashdata($key, $default = null) {
        $result = parent::flashdata($key);
        if (!$result)
            $result = $default;
        return $result;
    }

    function userdata($key, $default = null) {
        $result = parent::userdata($key);
        if (!$result)
            $result = $default;
        return $result;
    }

}
