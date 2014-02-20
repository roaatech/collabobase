<?php

class Tags extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('TagQuery');
    }

    public function index() {
        
    }

    public function search($value) {
        $tags = TagQuery::getInstance()->searchByName($value, null, null, TagModel::DATA_TYPE_STRING);
        $array = [];
        foreach ($tags as $tag) {
            /* @var $tag NotORM_Row */
            $array[] = array(
                "id" => $tag['id'],
                "tag" => $tag['name'],
            );
        }

        $this->output->set_content_type("application/json");
        echo json_encode($array);
    }

    public function __call($name, $arguments) {
        
    }

}
