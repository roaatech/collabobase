<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_View_loader {

    function load($view, $data = null, $template = null) {
        $controller = CI_Controller::get_instance();
        if (!$data)
            $data = array();

        if (!key_exists('title', $data))
            $data['title'] = '';

        $data["_is_ajax_request"] = $controller->input->is_ajax_request();
        $data["_is_normal_request"] = !$controller->input->is_ajax_request();

        if (file_exists("app/views/$view.phtml")) {
            if (!$controller->input->is_ajax_request() && $template != null && file_exists("app/views/templates/$template/header.phtml"))
                $controller->load->view("templates/$template/header.phtml", $data);
            $controller->load->view("$view.phtml", $data);
            if (!$controller->input->is_ajax_request() && $template != null && file_exists("app/views/templates/$template/footer.phtml"))
                $controller->load->view("templates/$template/footer.phtml", $data);
            return true;
        }else {
            show_404($view);
            return false;
        }
    }

}
