<?php

class Profile extends MY_Controller {

    protected $data = array(
        'sub_title' => 'Your Profile',
        'title' => 'Control Panel',
        'has_errors' => false,
        'display_message' => false,
        'active_tab' => 'profile',
    );

    function __construct() {
        parent::__construct();
        $this->protectedArea();
        $this->setData("sub_title", "Your Profile");
    }

    function Index($tab = "account") {
        return $this->View($tab);
    }

    function View($tab = "account") {

        $userModel = $this->currentUser()->model();

        $this->data['model'] = $userModel;
        $this->data['is_me'] = true;

        $this->data['tab'] = $tab;
        $this->data['operation_result'] = $this->session->flashdata('operation_result');

        $this->data['edit_url'] = "profile/edit";
        $this->data['change_password_url'] = "profile/change_password";
        $this->data['view_url'] = "profile/view";

        $this->view_loader->load("internal/users/view", $this->data, 'internal');
    }

    function change_password() {
        $this->data['model'] = $this->currentUser()->model();
        $this->data['return_url'] = "profile";
        $this->session->set_flashdata("same_url", "profile/change_password");
        $this->data['operation_result'] = $this->session->flashdata('operation_result');
        $this->data['is_me'] = true;
        $this->view_loader->load("internal/users/change_password", $this->data, 'internal');
    }

    function edit() {
        $userModel = $this->currentUser()->model();
        if (!$userModel) {
            return $this->redirect("");
        }

        $this->session->keep_flashdata('post');
        $this->session->set_flashdata("same_url", "profile/edit");

        $this->data['model'] = $userModel;
        $this->data['return_url'] = "profile/view";
        $this->data['post'] = $this->session->flashdata('post', array());
        $this->data['is_me'] = true;
        
        return $this->view_loader->load("internal/users/edit", $this->data, 'internal');
    }

}
