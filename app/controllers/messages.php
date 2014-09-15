<?php

class Messages extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->protectedArea();

        $this->load->model("ChatQuery");

        $this->setData("title", "Messages");
        $this->setData("sub_title", __("Your conversations"));
        $this->setData("active_tab", "messages");
        $this->setData("start_new_chat", 0);
    }

    public function index($id = null) {

        $this->_getChatsList(false);

        if ($id !== null) {
            $this->setData("load_chat_directly", "$id");
        }

        $users = UserQuery::getInstance()->all("id != {$this->currentUser()->model()->id}", UserQuery::RETURN_AS_PRESENTER);
        $this->setData("users", $users);

        return $this->view_loader->load("internal/messages/index", $this->data, 'internal_no_footer');
    }

    public function create() {
        $this->setData("start_new_chat", 1);
        $this->index();
    }

    /**
     * Ajax request
     * @param type $id
     * @return type
     */
    public function chat($id) {
        $chat = $this->loadChat($id);
        if (!$chat) {
            echo "<div class='text-danger'>" . __("Chat loading error: this chat does not exist or is not available for you.") . "</div>";
            return false;
        }
        $this->prepareViewData($chat);
        return $this->view_loader->load("internal/messages/chat", $this->data, 'empty');
    }

    protected function prepareViewData(ChatModel $chat, $newMessages = false) {
        $chatParticipant = $chat->getChatParticipant($this->currentUser()->model());
        if (!$chatParticipant) {
            return false;
        }

        if ($newMessages) {
            $chatMessagesModel = $chatParticipant->getNewMessages();
            if ($chatMessagesModel->count() == 0) {
                return null;
            }
        } else {
            $chatMessagesModel = $chatParticipant->getMessages(null, true);
        }

        $chatMessagesPresenter = $chatMessagesModel->presenterSet();
        $this->setData("messages", $chatMessagesPresenter);
        $this->setData("chat", $chat->presenter());
        return true;
    }

    /**
     * @param type $id
     * @return null|ChatMessageModel
     */
    protected function loadChat($id) {
        $chat = ChatQuery::getInstance()->findById($id);
        if (!$chat || $chat === null) {
            return null;
        }
        if (!$chat->isChatParticipant($this->currentUser()->model())) {
            return false;
        }
        return $chat;
    }

    protected function _loadChatNewMessages(ChatModel $chat) {
        $result = $this->prepareViewData($chat, true);
        if ($result != true) {
            return $result;
        }
        $this->view_loader->load("internal/messages/messages", $this->data, 'empty');
        return true;
    }

    public function loadChatNewMessages($id) {
        $chat = $this->loadChat($id);
        if (!$chat) {
            return false;
        }
        $result = $this->_loadChatNewMessages($chat);
        if ($result === null) {
            $this->output->set_status_header(204);
            return null;
        }
    }

    public function send() {

        $message = "";

        $this->setData("writer", $this->currentUser()->presenter());
        $this->setData("time", dt(date("Y-m-d H:i:s"), "j F Y, h:i a", null, $this->currentUser()->timezone()));

        try {

            $this->load->library('form_validation');

            $this->form_validation->set_rules("id", "Chat ID", "required|xss_clean|is_natural_no_zero");
            $this->form_validation->set_rules("message", "Message", "required|xss_clean");

            if (!$this->form_validation->run()) {
                $errors = $this->form_validation->error_string();
                throw new Exception($errors, 1);
            }

            $message = $this->input->post("message");
            $chatId = $this->input->post("id");

            $chat = ChatQuery::getInstance()->findById($chatId);
            $chatMessage = $chat->sendMessage($message, $this->currentUser()->model());

            $this->_loadChatNewMessages($chat);
        } catch (Exception $e) {
            $message .= "<div class='text-danger'><strong>" . __("Error! ") . "</strong> " . __("Failed to send the message! Please try again later.") . "</div>";
            $this->output->set_status_header(211, "post-failed");
            $this->setData("script", $message);
            return $this->view_loader->load("internal/messages/message", $this->data, 'empty');
        }
    }

    public function startChat($userId) {
        $user = UserQuery::getInstance()->findById($userId);
        if (!$user) {
            return $this->redirectWithOperationMessage("landing", "The user is invalid", 1);
        }
        $chat = ChatQuery::getInstance()->getTwoPartiesChat($this->currentUser()->model(), $user);
        if (!$chat) {
            return $this->redirectWithOperationMessage("messages", "Can not start a chat with this user!", 1);
        }
        return $this->redirect("messages/{$chat->id}");
    }

    public function new_chat() {
//        $this->output->enable_profiler(TRUE);

        try {

            $this->load->library('form_validation');

            $this->form_validation->set_rules("participants", "Chat Participants", "required|xss_clean");
            $this->form_validation->set_rules("title", "Chat Title", "xss_clean");

            if (!$this->form_validation->run()) {
                $errors = $this->form_validation->error_string();
                throw new Exception($errors, 1);
            }

            $participantsStr = $this->input->post("participants");
            $participants = explode(",", $participantsStr);
            $title = $this->input->post("title");

            if (count($participants) == 1 && $title == null) {
                return $this->startChat($participants[0]);
            }

            $chat = ChatQuery::getInstance()->createNewChat($this->currentUser()->model(), UserModelSet::create(UserQuery::getInstance()->all()->where("id", $participants)), $title);

            if ($chat) {
                $this->redirect("messages/{$chat->id}");
            } else {
                return $this->redirectWithOperationMessage("messages", "Can not start this chat!", 1);
            }
        } catch (Exception $e) {
            $message .= "<div class='text-danger'><strong>" . __("Error!") . " </strong> " . __("Failed to start this chat! Please try again later.") . "</div>";
            $this->output->set_status_header(211, "post-failed");
            $this->setData("script", $message);
            return $this->view_loader->load("internal/messages/message", $this->data, 'empty');
        }
    }

    public function getChatsList() {
        return $this->_getChatsList(true);
    }

    protected function _getChatsList($output = false) {

        $chats = ChatQuery::getInstance()->all($this->currentUser()->model())->order("last_update desc");
        $counts = ChatQuery::getInstance()->allChatsNewMessagesCounts($this->currentUser()->model());

        $this->setData('chats', $chats->presenterSet());
        $this->setData('counts', $counts);

        if ($output) {
            return $this->view_loader->load("internal/messages/chats_list", $this->data, 'empty');
        } else {
            return true;
        }
    }

    public function getNewChatsCount() {
        $this->output->set_content_type("text/plain");
        return $this->view_loader->load("internal/messages/new_chats_count", $this->data, 'empty');
    }

    public function editChat() {
//        $this->output->enable_profiler(TRUE);

        try {

            $this->load->library('form_validation');

            $this->form_validation->set_rules("id", "Chat ID", "required|xss_clean|is_natural_no_zero");
            $this->form_validation->set_rules("title", "Chat Title", "required|xss_clean");

            if (!$this->form_validation->run()) {
                $errors = $this->form_validation->error_string();
                throw new Exception($errors, 1);
            }

            $id = $this->input->post('id');
            $title = $this->input->post('title');

            $chat = ChatQuery::getInstance()->findById($id);
            if (!$chat || !$this->currentUser()->canEditChat($chat)) {
                throw new Exception("Not a chat or can not edit it!", 1);
            }

            $chat->setTitle($title);

            return $this->redirectWithOperationMessage("messages/{$id}", "Chat has been edited successfully!");
        } catch (Exception $e) {
            return $this->redirectWithOperationMessage("messages", $e->getMessage(), $e->getCode());
        }
    }

    public function leaveChat() {
//        $this->output->enable_profiler(TRUE);

        try {

            $this->load->library('form_validation');

            $this->form_validation->set_rules("id", "Chat ID", "required|xss_clean|is_natural_no_zero");

            if (!$this->form_validation->run()) {
                $errors = $this->form_validation->error_string();
                throw new Exception($errors, 1);
            }

            $id = $this->input->post('id');

            $chat = ChatQuery::getInstance()->findById($id);
            if (!$chat) {
                throw new Exception("Not a chat!", 1);
            }

            $chatParticipant = $chat->getChatParticipant($this->currentUser()->model());
            if (!$chatParticipant) {
                throw new Exception("Not a chat participant!", 1);
            }

            $chatParticipant->leave();

            return $this->redirectWithOperationMessage("messages", "Chat has been left successfully!");
        } catch (Exception $e) {
            return $this->redirectWithOperationMessage("messages", $e->getMessage(), $e->getCode());
        }
    }

}
