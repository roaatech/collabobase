<?php

class Account extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->library('form_validation');
    }

    public function index() {
        $this->protectedArea();
        $this->redirect("landing");
    }

    public function Register() {
        return $this->Login();
//        
//        $this->generalOnlyArea();
//
//        if ($this->session->userdata('current_user'))
//            return $this->land();
//
//        $data['title'] = __('Register');
//        $data["active_tab"] = __('register');
//        $data["num_of_errors"] = $this->form_validation->error_count();
//        $data["errors"] = $this->form_validation->error_string();
//        $this->view_loader->load('account/register', $data, 'general');
    }

    public function Login() {

        $this->generalOnlyArea();

        if ($this->session->userdata('current_user'))
            return $this->land();

        $data['title'] = __('Login');
        $data["active_tab"] = __('login');
        $data["num_of_errors"] = $this->form_validation->error_count();
        $data["errors"] = $this->form_validation->error_string();
        $this->view_loader->load('account/login', $data, 'login');
    }

    public function DoLogin() {

        if (!$this->session->userdata('current_user')) {

            $this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
            $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean|md5|callback_check_login');

            if ($this->form_validation->run() == false) {
                return $this->login();
            }
        }

        return $this->land();
    }

    public function check_login($password) {

        $username = $this->input->post('username');

        $this->load->model('UserQuery');

        $user = $this->UserQuery->findByLogin($username, $password);

        if ($user) {
            $this->session->set_userdata('current_user', $user->col('id'));
            return true;
        } else {
            $this->form_validation->set_message('check_login', __('The username and password combination is incorrect.'));
            return false;
        }
    }

    public function logout() {

        $this->session->unset_userdata('current_user');
        @session_destroy();
        return $this->should_login();
    }

    protected function land() {
        $redirectTo = $this->session->userdata("url_requested_login");
        if (!$redirectTo) {
            return $this->redirect("landing");
        } else {
            return redirect($redirectTo);
        }
    }

    protected function should_login() {
        return internal_redirect('account', 'login');
    }

    public function isAuthenticated() {
        $this->protectedArea();
    }

}
