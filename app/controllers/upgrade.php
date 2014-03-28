<?php

class Upgrade extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->protectedArea(UserModel::ROLE_ADMIN);
    }

    public function index() {
        $version = $this->config->item('app_version');
        if (!$version) {
            $version = 0;
        }

        $this->output->set_content_type("text/plain; charset=UTF-8");

        switch ($version) {
            case 0:
                echo "Upgrading to v1.1:\n";
                $this->exec("ALTER TABLE `chat_participant` ADD `last_check_chat_message_id` INT NULL , ADD INDEX (`last_check_chat_message_id`) ;", "Altering chat_participant table");
                $this->exec("update chat_participant p set last_check_chat_message_id = (select max(id) from chat_message where time<=p.last_check and chat_id = p.chat_id)", "Updaing chat_participant table data");
        }
    }

    protected function exec($stmt, $title) {
        static $pdo;
        if (!$pdo) {
            $pdo = UserQuery::getInstance()->getPdo();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        echo "$title: ";
        try {
            $pdo->exec($stmt);
            echo "Done.";
        } catch (Exception $e) {
            echo "Failed: {$e->getCode()}: {$e->getMessage()}";
        }
        echo "\n";
    }

}
