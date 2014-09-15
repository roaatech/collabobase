<?php

class Upgrade extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->protectedArea(UserModel::ROLE_ADMIN);
    }

    public function index() {
        $curVer = @file_get_contents(APPPATH . "config" . DIRECTORY_SEPARATOR . "version.txt");
        if (!$curVer) {
            $curVer = "1";
        }
        $lasVer = $this->config->item('app_version');

        $this->output->set_content_type("text/plain; charset=UTF-8");

        echo "Collabobase System Upgrader\n";
        echo "===========================\n";
        echo "\n";
        echo "Current version: {$curVer}\n";
        echo "Last version: {$lasVer}\n";
        echo "\n";

        switch ($curVer) {
            case "1":
                echo "Upgrading to v1.0.1:\n";
                echo "--------------------\n";
                $this->exec("ALTER TABLE `chat_participant` ADD `last_check_chat_message_id` INT NULL;", "Altering chat_participant table");
                $this->exec("update chat_participant p set last_check_chat_message_id = (select max(id) from chat_message where time<=p.last_check and chat_id = p.chat_id)", "Updaing chat_participant table data");
                echo "\n";


                //everything is before this
                echo "Setting config to v{$lasVer}: ";
                file_put_contents(APPPATH . "config" . DIRECTORY_SEPARATOR . "version.txt", $lasVer);
                echo "Done.\n";
                break;
            default:
                echo "Nothing to upgrade to!\n";
        }
        echo "\n";
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
            echo "Failed: {$e->getCode()}: {$e->getMessage()}.";
        }
        echo "\n";
    }

}
