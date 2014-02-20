<?php

class MY_Exceptions extends CI_Exceptions {

    public function __construct() {
        parent::__construct();
    }

    public function show_error($heading, $message, $template = 'error_general', $status_code = 500) {
        try {
            $str = parent::show_error($heading, $message, $template = 'error_general', $status_code = 500);
            if (ENVIRONMENT == 'development')
                throw new Exception($str);
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $trace = "<h1>Call Trace</h1><pre>" . $e->getTraceAsString() . "<pre>";
            //append our stack trace to the error message
            $err = str_replace('</div>', $trace . '</div>', $msg);
            echo $err;
        }
    }

    public function show_php_error($severity, $message, $filepath, $line) {
        try {
            ob_start();
            parent::show_php_error($severity, $message, $filepath, $line);
            $str = ob_get_contents();
            ob_end_clean();
            if (ENVIRONMENT == 'development')
                throw new Exception($str);
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $trace = "<h1>Call Trace</h1><pre>" . $e->getTraceAsString() . "<pre>";
            //append our stack trace to the error message
            $err = str_replace('</div>', $trace . '</div>', $msg);
            echo $err;
        }
    }

}
