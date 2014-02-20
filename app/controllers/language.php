<?php

class Language extends MY_Controller {

    public function _remap($language, $paras = null) {
        if ($language != "arabic") {
            $language = "english";
        }
        $this->session->set_userdata("display_language", $language);
        if (array_key_exists("HTTP_REFERER", $_SERVER)) {
            $to = $_SERVER["HTTP_REFERER"];
        } else {
            $to = base_url();
        }
        header("location: $to");
    }

}
