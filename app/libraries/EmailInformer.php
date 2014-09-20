<?php

class EmailInformer {

    /**
     * 
     * @return EmailInformer
     */
    public static function getInstance() {
        return new EmailInformer();
    }

    public function __construct() {
        require_once ROOT_PATH . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
    }

    protected function mailer() {
        $mail = new PHPMailer;
        $mail->SMTPAuth = false;
        $mail->From = get_instance()->config->item('sender_email');
        $mail->FromName = get_instance()->config->item('sender_name');

        return $mail;
    }

    protected function renderEmailMessage($messagePath, array $values = array()) {
        if (!file_exists($messagePath)) {
            user_error("Email message can not be found on $messagePath", E_USER_WARNING);
            return false;
        }
        extract($values);
        ob_start();
        require($messagePath);
        $message = ob_get_clean();
        return $message;
    }

    protected function sendEmail($content, $title, $users) {

        //get users
        $users = UserQuery::getInstance()->all("id in ($users)", Model::RETURN_AS_PRESENTER);

        $result = "";
        foreach ($users as $user) {
            /* @var $user UserPresenter */
            $email = $user->email;
            if (!$email) {
                continue;
            }
            $name = $user->displayName();
            $message = sprintf($content, $name, $email);
            $tResult = $this->send($message, $title, $name, $email);
            if (!$tResult) {
                $result.="$tResult\n";
            }
        }
        return $result === "" ? true : $result;
    }

    protected function send($message, $title, $receiver, $email) {
        $mail = $this->mailer();

        $mail->addAddress($email, $receiver);

        $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
        $mail->isHTML(false);                                  // Set email format to HTML

        $mail->Subject = $title;
        $mail->Body = $message;

        if (!$mail->send()) {
            return 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            return true;
        }
    }

    public function informFileReceivers(FileModel $file, FileVersionModel $version = null) {
        //prepare users
        $users = str_replace("|", ",", trim($file->rights, "|"));
        //return if no user rights specified
        if (!$users) {
            return;
        }

        //prepare values array
        $values = array(
            "file" => $file->title,
            "uploader" => $file->uploader()->presenter()->displayName(),
            "version" => $version ? $version->version : $file->getLastVersion()->version,
            "link" => FilePermaLink::view($file->id)
        );
        $fs = DIRECTORY_SEPARATOR;
        $content = $this->renderEmailMessage(APPPATH . "views{$fs}emails{$fs}newSpecificFile.phtml", $values);
        if (!$content) {
            return false;
        }

        return $this->sendEmail($content, "New file for your attention", $users);
    }

    public function informNewFileVersionReceivers(FileModel $file, FileVersionModel $version) {
        //prepare users
        $users = str_replace("|", ",", trim($file->rights, "|"));
        //return if no user rights specified
        if (!$users) {
            return;
        }

        //prepare values array
        $values = array(
            "file" => $file->title,
            "uploader" => $file->uploader()->presenter()->displayName(),
            "version" => $version ? $version->version : $file->getLastVersion()->version,
            "link" => FilePermaLink::view($file->id)
        );
        $fs = DIRECTORY_SEPARATOR;
        $content = $this->renderEmailMessage(APPPATH . "views{$fs}emails{$fs}newFileVersion.phtml", $values);
        if (!$content) {
            return false;
        }

        return $this->sendEmail($content, "New file version for your attention", $users);
    }

    public function informFileNewComment(FileModel $file, PostModel $comment) {
        //prepare users
        $users = str_replace("|", ",", trim($file->rights, "|"));
        //return if no user rights specified
        if (!$users) {
            return;
        }

        //prepare values array
        $values = array(
            "file" => $file->title,
            "commentor" => $comment->presenter()->author()->displayName(),
            "title" => $comment->presenter()->title(),
            "comment" => $comment->presenter()->content(),
            "link" => FilePermaLink::view($file->id)
        );
        $fs = DIRECTORY_SEPARATOR;
        $content = $this->renderEmailMessage(APPPATH . "views{$fs}emails{$fs}newFileComment.phtml", $values);
        if (!$content) {
            return false;
        }

        return $this->sendEmail($content, "New file comment for your attention", $users);
    }

    public function informNewPost(PostModel $post) {
        //prepare users
        $users = str_replace("|", ",", trim($post->rights, "|"));
        //return if no user rights specified
        if (!$users) {
            return;
        }

        //prepare values array
        $values = array(
            "post" => $post->title,
            "link" => PostPermalink::view($post->id),
            "author" => $post->author()->presenter()->displayName()
        );
        $fs = DIRECTORY_SEPARATOR;
        $content = $this->renderEmailMessage(APPPATH . "views{$fs}emails{$fs}newPost.phtml", $values);
        if (!$content) {
            return false;
        }

        return $this->sendEmail($content, "New post for your attention", $users);
    }

    public function informNewPostReply(PostModel $post, PostModel $reply) {
        //prepare users
        $users = str_replace("|", ",", trim($post->rights, "|"));
        //return if no user rights specified
        if (!$users) {
            return;
        }

        //prepare values array
        $values = array(
            "post" => $post->title,
            "author" => $reply->presenter()->author()->displayName(),
            "title" => $reply->presenter()->title(),
            "reply" => $reply->presenter()->content(),
            "link" => PostPermaLink::view($post->id)
        );
        $fs = DIRECTORY_SEPARATOR;
        $content = $this->renderEmailMessage(APPPATH . "views{$fs}emails{$fs}newPostReply.phtml", $values);
        if (!$content) {
            return false;
        }

        return $this->sendEmail($content, "New post reply for your attention", $users);
    }

}
