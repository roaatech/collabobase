<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_Loader extends CI_Loader {

    function __construct() {
        parent::__construct();
    }

    /**
     * 
     * @return MY_Controller
     */
    function controller() {
        return MY_Controller::get_instance();
    }

    function extend($view, array $data = []) {
        $controller = MY_Controller::get_instance();
        foreach ($this->_ci_cached_vars as $k => $v)
            $$k = $v;
        if ($data) {
            foreach ($data as $k => $v) {
                $$k = $v;
            }
        }
        include __DIR__ . "/../views/$view";
    }

    /**
     * 
     * @return boolean|CurrentUser
     */
    function currentUser() {
        $controller = MY_Controller::get_instance();
        if (!$controller->session->userdata('current_user'))
            return false;
        else
            return CurrentUser::getInstance();
    }

}
