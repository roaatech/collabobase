<?php

class Categories extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->data['title'] = "System Administration";
        $this->data['sub_title'] = "Categories";
        $this->data['active_tab'] = "admin";

        $this->load->library('form_validation');
        $this->protectedArea(UserModel::ROLE_ADMIN);
        $this->load->model('TagQuery');
        
//        $this->output->enable_profiler(TRUE);
    }

    public function Index() {
        if ($this->currentUser()->isAdmin()) {
            return $this->adminList();
        } else {
            return $this->userList();
        }
    }

    public function adminList() {

        list($tree, $total) = CategoryStates::getFlatTree();

        $this->setData("categories", $tree);
        $this->setData("total", $total);
        $this->session->set_flashdata("current_url", "categories");

        return $this->view_loader->load("internal/categories/list", $this->data, 'internal');
    }

    public function submit() {
        try {

            $this->form_validation->set_rules("name", "Category name", "required|xss_clean");
            $this->form_validation->set_rules("categoryId", "Category ID", "xss_clean");
            $this->form_validation->set_rules("parent", "Parent category", "xss_clean");

            if (!$this->form_validation->run()) {
                $errors = $this->form_validation->error_string();
                throw new Exception($errors, 1);
            }

            $id = $this->input->post('categoryId');
            $name = $this->input->post('name');
            $parent = $this->input->post('parent');

            if ($parent) {
                $parent = CategoryQuery::getInstance()->findById($parent);
                if (!$parent) {
                    throw new Exception("Not valid parent category ID", 1);
                }
            } else {
                $parent = null;
            }

            if (!$id) {
                $category = CategoryQuery::getInstance()->InsertNew($name, $parent);
            } else {
                $category = CategoryQuery::getInstance()->findById($id);
                if (!$category) {
                    throw new Exception("Not valid category ID", 1);
                }
                $category->name($name);
                $category->parentTag($parent, true);
            }

            return $this->redirectWithOperationMessage("categories", "The category has been " . ($id ? "updated" : "added") . " successfully!", 0);
        } catch (Exception $e) {
            return $this->redirectWithOperationMessage($this->session->flashdata("current_url"), $e->getMessage(), $e->getCode());
        }
    }

    public function delete() {
        try {

            $this->form_validation->set_rules("categoryId", "Category ID", "required|xss_clean");

            if (!$this->form_validation->run()) {
                $errors = $this->form_validation->error_string();
                throw new Exception($errors, 1);
            }

            $id = $this->input->post('categoryId');
            $category = CategoryQuery::getInstance()->findById($id);
            $category->delete();

            return $this->redirectWithOperationMessage("categories", "The category has been deleted successfully!", 0);
        } catch (Exception $e) {
            return $this->redirectWithOperationMessage($this->session->flashdata("current_url"), $e->getMessage(), $e->getCode());
        }
    }

}
