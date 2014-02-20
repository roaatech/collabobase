<?php

class Permalink extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function file_view($id) {
        return $this->redirect("files/view/$id", 301);
    }

    public function file_download($id, $versionId = null) {
        return $this->redirect("files/download/$id" . ($versionId ? "/{$versionId}" : ""), 301);
    }

}
