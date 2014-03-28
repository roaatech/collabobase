<?php

class Users extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->setData('sub_title', 'Users');
        $this->setData('title', 'System Administration');
        $this->setData('has_errors', false);
        $this->setData('display_message', false);
        $this->setData('active_tab', 'admin');

        $this->load->library('form_validation');
        $this->protectedArea();
    }

    public function Index($p = 1) {
        $this->protectedArea(UserModel::ROLE_ADMIN);

        define('rpp', 10);

        //if redirected after an operation
        $result = $this->session->flashdata('operation_result');
        if ($result) {
            $this->data['result_type'] = $result['code'] == 0 ? 'alert-success' : 'alert-warning';
            $this->data['result_message'] = $result['message'];
            $this->data['display_message'] = true;
        }

        //paging
        $users = UserQuery::getInstance()->all();
        $total = $users->count();
        if (($p - 1) * rpp > $total)
            $p = 1;
        if ($p < 1)
            $p = 1;
        $users->limit(rpp, ($p - 1) * rpp);

        $this->data['users'] = $users;
        $this->data['current_page'] = $p;
        $this->data['total_pages'] = ceil($total / rpp);
        $this->data['total_results'] = $total;
        $this->data['records_per_page'] = rpp;
        $this->setData("sub_title", __("Users"));

        return $this->view_loader->load("internal/users/list", $this->data, 'internal');
    }

    public function Create() {

        $this->protectedArea(UserModel::ROLE_ADMIN);

        $this->form_validation->set_rules('username', 'Username', 'required|is_unique[user.username]|min_length[4]|max_length[20]|alpha_dash|xss_clean');
        $this->form_validation->set_rules('firstname', 'First Name', 'required|max_length[20]|xss_clean');
        $this->form_validation->set_rules('lastname', 'Last Name', 'required|max_length[20]|xss_clean');
        $this->form_validation->set_rules('role', 'User Role', 'required|callback_check_role');
        $this->form_validation->set_rules('note', 'Note', 'xss_clean');

        if ($this->form_validation->run() == false) {
            $this->data['has_errors'] = true;
            return $this->Index();
        }

        $userModel = UserQuery::getInstance()->InsertNew($this->input->post('username'), null, $this->input->post('role'), array(
            'first_name' => $this->input->post('firstname'),
            'last_name' => $this->input->post('lastname'),
            'note' => $this->input->post('note')
        ));

        $this->session->set_flashdata('operation_result', array(
            'code' => 0,
            'message' => "The user has been successfully added!",
        ));
        return internal_redirect("users/view/{$userModel->id}");
    }

    public function check_role($role) {
        if ($role != UserModel::ROLE_ADMIN && $role != UserModel::ROLE_SUPERVISOR && $role != UserModel::ROLE_USER)
            return false;
        return true;
    }

    public function View($id, $tab = 'account') {
        $userModel = UserQuery::getInstance()->findById($id);
        if (!$userModel || !$this->currentUser()->isAdmin() && $userModel->id != $this->currentUser()->model()->id) {
            return $this->redirectWithOperationMessage("users", __("Not a user or insuffecient privileges"), 1);
        }

        $this->data['model'] = $userModel;
        $this->data['is_me'] = $userModel->id == $this->currentUser()->model()->id;

        $this->data['edit_url'] = "users/edit/$id";
        $this->data['change_password_url'] = "users/change_password/$id";
        $this->data['view_url'] = "users/view/$id";

        $this->data['tab'] = $tab;
//        $this->data['operation_result'] = $this->session->flashdata('operation_result');

        $this->view_loader->load("internal/users/view", $this->data, 'internal');
    }

    public function upload() {

        $this->protectedArea();

        $user = UserQuery::getInstance()->findById($this->input->post('user_id'));

        if (!$user || !$this->currentUser()->isAdmin() && $this->currentUser()->model()->id != $user->id) {
            return $this->redirectWithOperationMessage("users", "Not a user or insufficient privileges", 1);
        }

        $this->form_validation->set_rules('title', 'Title', 'required|xss_clean');
        $this->form_validation->set_rules('return_url', 'Return URL', 'required');
        $this->form_validation->set_rules('user_id', 'User ID', 'required|is_natural_no_zero|xss_clean|callback_check_user_id');

        $config['upload_path'] = __DIR__ . DIRECTORY_SEPARATOR . '../../assets/uploads/';
        $config['allowed_types'] = 'gif|jpg|png|pdf|doc|docx|txt|tif|tiff';
        $config['max_size'] = '2048';
        $config['max_width'] = '0';
        $config['max_height'] = '0';
        $config['encrypt_name'] = true;

        $this->load->library('upload', $config);

        $errors = "";
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_string();
            return $this->upload_error($errors);
        }

        if (!$this->upload->do_upload('file')) {
            $errors = $this->upload->display_errors('', '');
            return $this->upload_error($errors);
        }

        $uploadData = $this->upload->data();
        $return_url = $this->input->post('return_url');

        $file = $user->insertFile($uploadData['file_name'], $uploadData['file_type'], $this->input->post('title'), $this->input->post('description'), $uploadData['orig_name'], $uploadData);

        $this->session->set_flashdata('operation_result', array('code' => 0, 'message' => 'The file has been uploaded successfully.'));

        return redirect($this->config->base_url() . $return_url);
    }

    public function check_user_id($id) {
        $id = UserQuery::getInstance()->findById($id);
        return $id != false;
    }

    protected function upload_error($error) {
        $return_url = $this->input->post('return_url');
        $this->session->set_flashdata('operation_result', array('code' => 1, 'message' => $error));
        $this->session->set_flashdata('file_upload_title', $this->input->post('title'));
        $this->session->set_flashdata('file_upload_description', $this->input->post('description'));
        return redirect($this->config->base_url() . $return_url);
    }

    public function change_password($id) {
        $userModel = UserQuery::getInstance()->findById($id);
        if (!$userModel) {
            $this->redirectWithOperationMessage("users", "Not a user", 1);
        }
        $this->data['model'] = $userModel;
        $this->data['return_url'] = "users/view/$id";
        $this->session->set_flashdata("same_url", "users/change_password/$id");
//        $this->data['operation_result'] = $this->session->flashdata('operation_result');
        $this->data['is_me'] = false;
        $this->view_loader->load("internal/users/change_password", $this->data, 'internal');
    }

    public function do_change_password() {

        try {

            $userModel = UserQuery::getInstance()->findById($this->input->post('user_id'));

            if (!$userModel || !$this->currentUser()->isAdmin() && $this->currentUser()->model()->id != $userModel->id) {
                return $this->redirectWithOperationMessage("users", "Not a user or insufficient privileges", 1);
            }

            $this->form_validation->set_rules('user_id', 'User ID', 'required|is_natural_no_zero|xss_clean|callback_check_user_id');
            $this->form_validation->set_rules('return_url', 'Return URL', 'required');
            $this->form_validation->set_rules('new_password', 'New password', 'required|min_length[5]');
            $this->form_validation->set_rules('confirm_password', 'Confirm password', 'required|min_length[5]|matches[new_password]');

            if (!$this->form_validation->run()) {
                throw new Exception($this->form_validation->error_string());
            }

            if ($userModel->col('id') == $this->currentUser()->model()->col('id')) {
                if ($userModel->col('password') != md5($this->input->post('old_password'))) {
                    throw new Exception("The old password is incorrect!");
                }
            }

            $userModel->updatePassword($this->input->post('new_password'));
            $this->setOperationResult('The new password has been set', 0);
            return redirect(base_url($this->input->post('return_url')));
        } catch (Exception $e) {

            $this->setOperationResult($e->getMessage(), 1);
            $url = $this->session->flashdata('same_url');
            if (!$url)
                $url = "";

            return redirect(base_url($url));
        }
    }

    public function edit($id) {
        $userModel = UserQuery::getInstance()->findById($id);
        if (!$userModel || (!$this->currentUser()->isAdmin() && $this->currentUser()->model()->id != $userModel->id)) {
            return $this->redirectWithOperationMessage("users", "Not a user or insufficient privileges", 1);
        }

        $this->session->keep_flashdata('post');
        $this->session->set_flashdata("same_url", "users/edit/$id");

        $this->data['model'] = $userModel;
        $this->data['return_url'] = "users/view/$id";
        $this->data['post'] = $this->session->flashdata('post', array());
        $this->data['is_me'] = false;

        return $this->view_loader->load("internal/users/edit", $this->data, 'internal');
    }

    public function do_edit() {
        try {

            $userModel = UserQuery::getInstance()->findById($this->input->post('user_id'));
            if (!$userModel || (!$this->currentUser()->isAdmin() && $this->currentUser()->model()->id != $userModel->id)) {
                return $this->redirectWithOperationMessage("users", "Not a user or insufficient privileges", 1);
            }

            $this->form_validation->set_rules('user_id', 'User ID', 'required|is_natural_no_zero|xss_clean|callback_check_user_id');
            $this->form_validation->set_rules('return_url', 'Return URL', 'required');

            $fields = array(
                "first_name" => "required",
                "second_name" => "",
                "last_name" => "required",
                "birth_date" => "is_date_or_null",
                "contact_main_phone" => "integer|min_length[7]|max_length[15]",
                "contact_alternative_phone" => "integer|min_length[7]|max_length[15]",
                "contact_email" => "valid_email",
                "contact_skype_id" => "",
                "emergency_full_name" => "",
                "emergency_primary_phone" => "integer|min_length[7]|max_length[15]",
                "emergency_alternative_phone" => "integer|min_length[7]|max_length[15]",
                "emergency_email" => "valid_email",
                "emergency_skype_id" => "",
                "address_street" => "",
                "address_city" => "",
                "address_state" => "",
                "address_zip" => "",
                "mission_title" => "",
                "mission_department" => "",
                "mission_supervisor" => "",
                "mission_employee_id" => "",
                "mission_work_location" => "",
                "mission_start_date" => "is_date_or_null",
            );

            $data = array();
            foreach ($fields as $field => $checks) {
                $this->form_validation->set_rules($field, labelize($field), "xss_clean|$checks");
                $data[$field] = $this->input->post($field);
            }

            if (!$this->form_validation->run()) {
                throw new Exception($this->form_validation->error_string());
            }

            if (strtotime($data['birth_date']))
                $data['birth_date'] = date('Y-m-d', strtotime($data['birth_date']));
            else
                $data['birth_date'] = null;

            if (strtotime($data['mission_start_date']))
                $data['mission_start_date'] = date('Y-m-d', strtotime($data['mission_start_date']));
            else
                $data['mission_start_date'] = null;

            $userModel->updateProfile($data);

            $this->setOperationResult('The profile has been updated!', 0);

            return redirect(base_url($this->input->post('return_url')));
        } catch (Exception $e) {
            $this->session->set_flashdata('post', $data);
            $this->setOperationResult($e->getMessage(), 1);
            $url = $this->session->flashdata('same_url');
            return redirect(base_url($url));
        }
    }

    public function do_suspend() {
        try {

            $userModel = UserQuery::getInstance()->findById($this->input->post('user_id'));
            if (!$userModel || !$this->currentUser()->isAdmin() && $this->currentUser()->model()->id != $userModel->id) {
                return $this->redirectWithOperationMessage("users", "Not a user or insufficient privileges", 1);
            }

            $this->form_validation->set_rules('user_id', 'User ID', 'required|is_natural_no_zero|xss_clean|callback_check_user_id');

            if (!$this->form_validation->run()) {
                throw new Exception($this->form_validation->error_string());
            }

            $userModel->changeStatus(true);

            $this->setOperationResult('The user has been suspended successfully!', 0);

            return redirect(base_url("users/view/{$this->input->post('user_id')}"));
        } catch (Exception $e) {

            $this->session->set_flashdata('post', $data);
            $this->setOperationResult($e->getMessage(), 1);
            $url = $this->session->flashdata('same_url');
            return redirect(base_url($url));
        }
    }

    public function do_activate() {
        try {

            $userModel = UserQuery::getInstance()->findById($this->input->post('user_id'));
            if (!$userModel || !$this->currentUser()->isAdmin() && $this->currentUser()->model()->id != $userModel->id) {
                return $this->redirectWithOperationMessage("users", "Not a user or insufficient privileges", 1);
            }

            $this->form_validation->set_rules('user_id', 'User ID', 'required|is_natural_no_zero|xss_clean|callback_check_user_id');

            if (!$this->form_validation->run()) {
                throw new Exception($this->form_validation->error_string());
            }

            $userModel->changeStatus(false);

            $this->setOperationResult('The user has been activated successfully!', 0);

            return redirect(base_url("users/view/{$this->input->post('user_id')}"));
        } catch (Exception $e) {

            $this->session->set_flashdata('post', $data);
            $this->setOperationResult($e->getMessage(), 1);
            $url = $this->session->flashdata('same_url');
            return redirect(base_url($url));
        }
    }

    public function do_edit_file() {

        try {
            $userModel = UserQuery::getInstance()->findById($this->input->post('user_id'));
            if (!$userModel || !$this->currentUser()->isAdmin() && $this->currentUser()->model()->id != $userModel->id) {
                return $this->redirectWithOperationMessage("users", "Not a user or insufficient privileges", 1);
            }

            $this->form_validation->set_rules('title', 'Title', 'required|xss_clean');
            $this->form_validation->set_rules('return_url', 'Return URL', 'required');
            $this->form_validation->set_rules('user_id', 'User ID', 'required|is_natural_no_zero|xss_clean|callback_check_user_id');
            $this->form_validation->set_rules('file_id', 'File ID', 'required|is_natural_no_zero|xss_clean');

            $errors = "";
            if (!$this->form_validation->run()) {
                $errors = $this->form_validation->error_string();
                return $this->upload_error($errors);
            }

            $return_url = $this->input->post('return_url');
            $file = $userModel->getFile($this->input->post("file_id"));
            $title = html_entity_encode($this->input->post("title"), ENT_QUOTES | ENT_HTML5);
            $description = html_entity_encode($this->input->post("description"), ENT_QUOTES | ENT_HTML5);

            $file->update($title, $description);

            $this->session->set_flashdata('operation_result', array('code' => 0, 'message' => 'The file has been updated successfully.'));

            return redirect($this->config->base_url() . $return_url);
        } catch (Exception $e) {

            $this->setOperationResult($e->getMessage(), 1);
            $url = $this->session->flashdata('same_url');
            return redirect(base_url($url));
        }
    }

    public function do_delete_file() {

        try {
            $userModel = UserQuery::getInstance()->findById($this->input->post('user_id'));
            if (!$userModel || !$this->currentUser()->isAdmin() && $this->currentUser()->model()->id != $userModel->id) {
                return $this->redirectWithOperationMessage("users", "Not a user or insufficient privileges", 1);
            }

            $this->form_validation->set_rules('return_url', 'Return URL', 'required');
            $this->form_validation->set_rules('user_id', 'User ID', 'required|is_natural_no_zero|xss_clean|callback_check_user_id');
            $this->form_validation->set_rules('file_id', 'File ID', 'required|is_natural_no_zero|xss_clean');

            $errors = "";
            if (!$this->form_validation->run()) {
                $errors = $this->form_validation->error_string();
                return $this->upload_error($errors);
            }

            $return_url = $this->input->post('return_url');
            $file = $userModel->getFile($this->input->post("file_id"));
            $file->delete();

            $this->session->set_flashdata('operation_result', array('code' => 0, 'message' => 'The file has been deleted successfully.'));

            return redirect($this->config->base_url() . $return_url);
        } catch (Exception $e) {

            $this->setOperationResult($e->getMessage(), 1);
            $url = $this->session->flashdata('same_url');
            return redirect(base_url($url));
        }
    }

    public function delete() {
//        $this->output->enable_profiler(TRUE);

        $id = $this->input->post("user_id", true);

        if (!$id) {
            return $this->redirectWithOperationMessage("users", "Not a valid user", 1);
        }

        $user = UserQuery::getInstance()->findById($id);
        if (!$user || !$user->deletable($this->currentUser())) {
            return $this->redirectWithOperationMessage("users", "Not a valid user", 1);
        }

        $user->delete();

        $this->redirectWithOperationMessage("users", "The user has been deleted!");
    }

    public function setRoles() {
        $id = $this->input->post('user_id', true);
        $user = $this->input->post('user', true);
        $supervisor = $this->input->post('supervisor', true);
        $admin = $this->input->post('admin', true);

        $userModel = UserQuery::getInstance()->findById($id);
        if (!$userModel || !$this->currentUser()->isAdmin()) {
            return $this->redirectWithOperationMessage("users", "Not a user or insufficient privileges", 1);
        }
        
        if(!$user && !$supervisor && !$admin){
            return $this->redirectWithOperationMessage("users/view/$id", "Can not revoke all roles from the user!", 1);
        }

        $res = true;
        if ($user) {
            $res = $res && $userModel->addRole(UserModel::ROLE_USER);
        } else {
            $res = $res && $userModel->removeRole(UserModel::ROLE_USER);
        }

        if ($supervisor) {
            $res = $res && $userModel->addRole(UserModel::ROLE_SUPERVISOR);
        } else {
            $res = $res && $userModel->removeRole(UserModel::ROLE_SUPERVISOR);
        }

        if ($admin) {
            $res = $res && $userModel->addRole(UserModel::ROLE_ADMIN);
        } else {
            $res = $res && $userModel->removeRole(UserModel::ROLE_ADMIN);
        }

        if ($res) {
            return $this->redirectWithOperationMessage("users/view/$id", "User roles has been set successfully!");
        } else {
            return $this->redirectWithOperationMessage("users/view/$id", "Can not set user roles!", 1);
        }
    }

}
