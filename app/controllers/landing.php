<?php

class Landing extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->protectedArea();

        $this->load->model('tagquery');
        $this->load->model('filequery');
        $this->load->model('postquery');
        
    }

    function Index() {
        $this->setData('title', 'Welcome');
        $this->setData('active_tab', 'landing');

        $this->setData("files", FileQuery::getInstance()->allActive(null, FileQuery::RETURN_AS_MODEL)->order("update_time desc, `time` desc")->limit(3)->presenterSet());
        $this->setData("posts", PostModelSet::create(PostQuery::getInstance()->allRoots("file_id is null and status='active'")->order("last_update_time desc, `time` desc")->limit(3))->presenterSet());

        return $this->view_loader->load("internal/landing", $this->data, 'internal');
    }

}
