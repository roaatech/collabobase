<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    const LEVEL_UP = "..";

    protected static $language = null;
    protected $data = array(
        'sub_title' => '',
        'title' => '',
        'has_errors' => false,
        'display_message' => false,
        'active_tab' => '',
        'current_url' => '',
        'new_chats_count' => '0',
        'language' => 'english',
    );

    function __construct() {
        parent::__construct();
        $this->setData('operation_result', $this->getOperationResult());
        $this->setData('current_url', $this->currentUrl(false, false));
        $this->setData('current_url_full', $this->currentUrl());
        $this->setData('current_user', null);

        if ($this->currentUser()) {
            $this->load->model("ChatQuery");
            $result = ChatQuery::getInstance()->allNewChatsCount($this->currentUser()->model());
            $this->setData("new_chats_count", $result);
            $this->setData('current_user', $this->currentUser());
        }

        $this->loadLanguage();
    }

    /**
     * 
     * @return boolean|CurrentUser
     */
    function currentUser() {
        if (!$this->session->userdata('current_user'))
            return false;
        else
            return CurrentUser::getInstance();
    }

    function setOperationResult($message, $code = 0) {
        $this->session->set_flashdata('operation_result', array('code' => $code, 'message' => $message));
    }

    function getOperationResult() {
        return $this->session->flashdata("operation_result");
    }

    function redirectWithOperationMessage($url, $message, $code = 0) {
        $this->setOperationResult($message, $code);
        return redirect(base_url($url));
    }

    function redirect($url, $code = 302) {
        $this->output->set_status_header($code);
        return redirect(base_url($url));
    }

    function protectedArea($role = null, $url = null) {
        if (!$url)
            $url = $this->currentUrl();
        if (!$this->currentUser()) {
            if ($this->input->is_ajax_request()) {
                $this->output->set_status_header(401);
                exit();
            }
            $this->session->set_userdata('url_requested_login', $url);
            return $this->redirect("account/login");
        }
        if ($role && !$this->currentUser()->model()->checkRole($role)) {
            return $this->redirectWithOperationMessage("landing", "You can not access this area!", -1);
        }
    }

    function generalOnlyArea() {
        if ($this->currentUser()) {
            $this->redirect("landing");
        }
    }

    function currentUrl($withQueryString = false, $withSiteUrl = true) {
        $query = "";
        if ($withQueryString) {
            $query = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
        }
        $url = ($withSiteUrl ? $this->config->site_url() : "") . $this->uri->uri_string() . $query;
        return $url;
    }

    /**
     * 
     * @param type $key
     * @param type $value
     * @return \MY_Controller
     */
    protected function setData($key, $value = null) {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * 
     * @return MY_Controller
     */
    public static function &get_instance() {
        return parent::get_instance();
    }

    protected function loadLanguage($file = "general", $language = null) {
        if (!$language) {
            $language = $this->getLanguage();
        }
        $this->lang->load($file, $language);
        $this->setData('language', $language);
        self::$language = $language;
    }

    public function getLanguage() {
        if (!self::$language) {
            if ($this->currentUser()) {
                $displayLanguage = $this->currentUser()->getDisplayLanguage();
            } else {
                $displayLanguage = MY_Controller::get_instance()->session->userdata("display_language");
                if (!$displayLanguage) {
                    $displayLanguage = "english";
                    MY_Controller::get_instance()->session->set_userdata("display_language", "english");
                }
            }
            self::$language = $displayLanguage;
        }
        return self::$language;
    }

}
