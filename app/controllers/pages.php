<?php

class Pages extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('userquery');
        $this->load->model('pagequery');
    }

    public function _remap($method, $paras = null) {
        if (strtolower($method) == 'edit' || strtolower($method) == 'save') {
            return $this->$method(@$paras[0], @$paras[1]);
        }
        if (!file_exists("app/views/pages/{$this->getLanguage()}/{$method}.phtml")) {
            show_404($method);
        }
        $this->data['title'] = __(ucfirst(strtolower(labelize($method))));
        $this->data["active_tab"] = $method;
        $this->view_loader->load("pages/{$this->getLanguage()}/{$method}", $this->data, 'general');
    }

    protected function edit($page = null, $language = "english") {
        $this->protectedArea(UserModel::ROLE_ADMIN);

        $language = $language == "ar" ? "arabic" : "english";

        $this->setData("title", "System Administration");
        $this->setData("sub_title", __("Static Pages"));

        if ($page === null) {
            return $this->view_loader->load("internal/admin/pages_list", $this->data, 'internal');
        }

        if (!file_exists("app/views/pages/{$language}/{$page}.phtml")) {
            return show_404($page);
        }
        $content = file_get_contents("app/views/pages/{$language}/$page.phtml");

        $this->setData("content", $content);
        $this->setData("page", $page);
        $this->setData("edit_language", $language);
        $this->setData("sub_title", "<a href='" . base_url("pages/edit") . "'>" . __("Static Pages") . "</a> &gt; " . __("Editing") . " '$page'");

        $this->view_loader->load("internal/admin/edit_page", $this->data, 'internal');
    }

    protected function save() {
        $this->protectedArea(UserModel::ROLE_ADMIN);

        $page = $this->input->post("page", true);
        $content = $this->input->post("content", true);
        $language = $this->input->post("edit_language", true);

        if (!file_exists("app/views/pages/{$language}/{$page}.phtml")) {
            return show_404($method);
        }

        $content = file_put_contents("app/views/pages/{$language}/{$page}.phtml", $content);

        return $this->redirectWithOperationMessage("pages/edit", "You have successfully saved the page");
    }

}
