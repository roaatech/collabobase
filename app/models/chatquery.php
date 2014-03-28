<?php

class ChatQuery extends ORM {

    /**
     * 
     * @param \PDO $connection
     * @param \NotORM_Structure $structure
     * @param \NotORM_Cache $cache
     * @return ChatQuery
     */
    public static function getInstance(\PDO $connection = null, \NotORM_Structure $structure = null, \NotORM_Cache $cache = null) {
        return parent::getInstance($connection, $structure, $cache);
    }

    public function __construct(\PDO $connection = null, \NotORM_Structure $structure = null, \NotORM_Cache $cache = null) {
        parent::__construct($connection, $structure, $cache);
    }

    /**
     * 
     * @param UserModel $user
     * @return NotORM_Result
     */
    protected function _all($condition = "") {
        $list = $this->chat($condition);
        return $list;
    }

    /**
     * 
     * @param type $id
     * @return null|ChatModel
     */
    public function findById($id) {
        @$result = $this->chat[$id];
        if (!$result) {
            return null;
        }
        return ChatModel::create($result);
    }

    /**
     * 
     * @param UserModel $user
     * @param type $returnAs
     * @return NotORM_Row|ChatPresenterSet|ChatModelSet
     */
    public function all(UserModel $user, $returnAs = self::RETURN_AS_MODEL) {
        $set = $this->_all("id in (select chat_id from chat_participant where user_id = {$user->id} and `role`!='deleted') and (user_id={$user->id} or last_chat_message_id is not null)");
        switch ($returnAs) {
            case self::RETURN_AS_MODEL:
                $result = ChatModelSet::create($set);
                break;
            case self::RETURN_AS_PRESENTER:
                $result = ChatPresenterSet::create($set);
                break;
            default:
                $result = $set;
        }
        return $result;
    }

    /**
     * 
     * @param UserModel $user1
     * @param UserModel $user2
     * @return ChatModel
     */
    public function getTwoPartiesChat(UserModel $user1, UserModel $user2) {
        $chat = $this->_all("id in (select chat_id from chat_participant where user_id in ({$user1->id}, {$user2->id}) group by chat_id having count(user_id)=2) and title is null")->fetch();
        if (!$chat) {
            $chat = $this->createNewChat($user1, NULL, null);
            $chat->addChatParticipant($user2);
        } else {
            $chat = ChatModel::create($chat);
        }
        return $chat;
    }

    /**
     * 
     * @param UserModel $owner
     * @param UserModelSet $participants
     * @param type $title
     * @return ChatModel
     */
    public function createNewChat(UserModel $owner, UserModelSet $participants = null, $title = null) {
        $time = date('Y-m-d H:i:s');
        $chat = $this->chat()->insert([
            "id" => null,
            "title" => $title === null ? null : "$title",
            "time" => $time,
            "user_id" => $owner->id,
            "last_update" => $time
        ]);
        /* @var $chat NotORM_Row */
        $chatModel = $chat->model();
        /* @var $chatModel ChatModel */
        $chatModel->addChatParticipant($owner, ChatParticipantModel::ROLE_ADMIN);
        if ($participants) {
            foreach ($participants as $participant) {
                /* @var $participant UserMdel */
                $chatModel->addChatParticipant($participant);
            }
        }
        return $chatModel;
    }

    /**
     * Returns a list of user chats and the new messages of it.
     * @param UserModel $user
     * @return array
     */
    public function allChatsNewMessagesCounts(UserModel $user) {
        $stmt = $this->getPdo()->prepare("select chat.id as id, count(cm.id) as cnt from chat left outer join chat_message as cm on chat.id=cm.chat_id where cm.time>(select last_check from chat_participant where user_id={$user->id} and chat_id=chat.id) group by chat.id;");
        $eResult = $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
        $res = [];
        foreach ($results as $k => $v) {
            $res[$k] = $v[0]['cnt'];
        }
        return $res;
    }

    public function allNewChatsCount(UserModel $user) {
        $stmt = $this->getPdo()->prepare("select count(*) as cnt from chat as c join chat_participant cp on c.id=cp.chat_id where cp.user_id={$user->id} and last_update > cp.last_check and c.last_chat_message_id is not null;");
        $eResult = $stmt->execute();
        $result = $stmt->fetchColumn(0);
        return $result;
    }

}

class ChatModelSet extends ModelSet {

    /**
     * 
     * @param \NotORM_Result $set
     * @return ChatModelSet
     */
    public static function create(\NotORM_Result $set) {
        return parent::create($set);
    }

    /**
     * 
     * @param \NotORM_Row $row
     * @return ChatModel
     */
    protected function get(\NotORM_Row $row) {
        return ChatModel::create($row);
    }

    /**
     * 
     * @return ChatPresenterSet
     */
    public function presenterSet() {
        return ChatPresenterSet::create($this->rawSet());
    }

}

class ChatPresenterSet extends PresenterSet {

    /**
     * 
     * @param \NotORM_Result $set
     * @return ChatPresenterSet
     */
    public static function create(\NotORM_Result $set) {
        return parent::create($set);
    }

    /**
     * 
     * @param \NotORM_Row $row
     * @return ChatPresenter
     */
    protected function get(\NotORM_Row $row) {
        return ChatPresenter::create(ChatModel::create($row));
    }

    /**
     * 
     * @return ChatModelSet
     */
    public function modelSet() {
        return ChatModelSet::create($this->rawSet());
    }

}

/**
 * @property ChatMessageModelSet $messages
 * @property array $keys
 */
class ChatModel extends Model {

    protected $messages = null;
    protected $keys = [];
    protected $lastKey = null;
    protected $participants = [];

    /**
     * 
     * @param \NotORM_Row $row
     * @return ChatModel
     */
    public static function create(\NotORM_Row $row) {
        return new ChatModel($row);
    }

    public function __construct(\NotORM_Row $model) {
        parent::__construct($model);
    }

    /**
     * 
     * @return ChatPresenter
     */
    public function presenter() {
        return ChatPresenter::create($this);
    }

    /**
     * 
     * @return ChatMessageModel
     */
    public function lastMessage() {
        if ($this->messages()->count() == 0) {
            return null;
        }
        $message = $this->messages()->getOffset($this->lastKey);
        return $message;
    }

    /**
     * 
     * @param type $condition
     * @return ChatMessageModelSet
     */
    public function messages($condition = null) {
        if (!$this->messages) {
            $messages = $this->raw()->chat_message();
            if ($condition !== null) {
                $messages->where($condition);
            }
            $this->messages = ChatMessageModelSet::create($messages);
            $key = null;
            foreach ($this->messages as $key => $value) {
                $this->keys[] = $key;
            }
            $this->lastKey = $key;
        }
        return $this->messages;
    }

    /**
     * 
     * @param type $message
     * @param UserModel $user
     * @return ChatMessageModel
     * @throws Exception
     */
    public function sendMessage($message, UserModel $user) {
        $this->assertExists();
        $chatParticipantModel = $this->getChatParticipant($user);
        if (!$chatParticipantModel) {
            throw new Exception("User is invalid participant in this chat!");
        }
        $chatMessage = $chatParticipantModel->sendMessage($message);
        if (!$chatMessage) {
            return false;
        }
        $this->last_update = $chatMessage->time;
        $this->last_chat_message_id = $chatMessage->id;
        $this->save();
        return $chatMessage;
    }

    /**
     * 
     * @param UserModel $user
     * @return ChatParticipantModel
     */
    public function getChatParticipant(UserModel $user) {
        if (!array_key_exists($user->id, $this->participants)) {
            $participant = $this->row->chat_participant("user_id = {$user->id}")->fetch();
            if (!$participant) {
                $participant = null;
            } else {
                $participant = $participant->model();
            }
            $this->participants[$user->id] = $participant;
        }
        return $this->participants[$user->id];
    }

    /**
     * 
     * @param UserModel $user
     * @param type $role
     * @return false|ChatParticipantModel
     */
    public function addChatParticipant(UserModel $user, $role = "user") {
        $role = ChatParticipantModel::assureRole($role);
        if ($this->isChatParticipant($user)) {
            return $this->getChatParticipant($user);
        } else {
            $participant = $this->raw()->chat_participant()->insert([
                "id" => null,
                "user_id" => $user->id,
                "role" => $role,
                "chat_id" => $this->id,
                "last_check" => date('Y-m-d H:i:s')
            ]);
            if ($participant) {
                $participant = $participant->model();
                $this->participants[$user->id] = $participant;
            }
            return $participant;
        }
    }

    public function isChatParticipant(UserModel $user) {
        return $this->getChatParticipant($user) !== null;
    }

    /**
     * 
     * @return ChatParticipantModelSet
     */
    public function getChatParticipants($includeDeleted = false) {
        $participants = $this->raw()->chat_participant();
        if (!$includeDeleted) {
            $participants->where("role != 'deleted'");
        }
        return ChatParticipantModelSet::create($participants);
    }

    public function setTitle($title) {
        $this->assertExists();
        $this->set("title", $title);
        $this->save();
        return true;
    }

}

/**
 * @method ChatModel model() returns the chat model object of this presenter
 */
class ChatPresenter extends Presenter {

    protected $store = [];

    /**
     * 
     * @param \Model $model
     * @return \ChatPresenter
     */
    public static function create(\Model $model) {
        return new ChatPresenter($model);
    }

    public function __construct(\ChatModel $model) {
        parent::__construct($model);
    }

    public function __toString() {
        return $this->me();
    }

    public function me() {
        return $this->displayTitle();
    }

    public function displayTitle() {
        return $this->title? : $this->paritcipantsCsv();
    }

    public function __get($name) {
        switch (strtolower($name)) {
            case 'id':
            case 'title':
            case 'user_id':
            case 'time':
                return $this->model()->$name;
                break;
            default:
                throw new Exception("Can not find the property `$name` in ChatPresenter object", 1);
        }
    }

    public function excerpt($length = 10, UserModel $user = null) {
        $lastMessage = $this->model()->lastMessage();
        if (!$lastMessage) {
            return "No messages yet!";
        } else {
            return $lastMessage = $lastMessage->presenter()->excerpt($length, $user ? $user->presenter() : null);
        }
    }

    public function time($timezone = null) {
        static $tz = [];
        if (!array_key_exists($timezone, $tz)) {
            $tz[$timezone] = dt($this->model->time, "j F Y, h:i a", null, $timezone);
        }
        return $tz[$timezone];
    }

    public function time_humanized($timezone = null) {
        static $tz = [];
        if (!array_key_exists($timezone, $tz)) {
            $tz[$timezone] = date_human($this->model->time, $timezone);
        }
        return $tz[$timezone];
    }

    public function paritcipantsCsv() {
        if (!array_key_exists("pcsv", $this->store)) {
            $this->store['pcsv'] = $this->model()->getChatParticipants()->presenterSet()->toCsv();
        }
        return $this->store['pcsv'];
    }

}

class ChatMessageQuery extends ORM {

    /**
     * 
     * @param \PDO $connection
     * @param \NotORM_Structure $structure
     * @param \NotORM_Cache $cache
     * @return ChatMessageQuery
     */
    public static function getInstance(\PDO $connection = null, \NotORM_Structure $structure = null, \NotORM_Cache $cache = null) {
        return parent::getInstance($connection, $structure, $cache);
    }

    public function __construct(\PDO $connection = null, \NotORM_Structure $structure = null, \NotORM_Cache $cache = null) {
        parent::__construct($connection, $structure, $cache);
    }

    /**
     * 
     * @param string $condition
     * @return ChatMessageModelSet
     */
    public function all($condition = null) {
        $qry = $this->chat_message();
        if ($condition !== null) {
            $qry->where($condition);
        }
        return ChatMessageModelSet::create($qry);
    }

}

class ChatMessageModel extends Model {

    /**
     * 
     * @return ChatMessagePresenter
     */
    public function presenter() {
        return ChatMessagePresenter::create($this);
    }

    /**
     * 
     * @param \NotORM_Row $row
     * @return \ChatMessageModel
     */
    public static function create(\NotORM_Row $row) {
        return new ChatMessageModel($row);
    }

    /**
     * 
     * @return UserModel
     */
    public function writer() {
        return $this->raw()->chat_participant->user->model();
    }

    public function time() {
        return $this->time;
    }

}

class ChatMessagePresenter extends Presenter {

    /**
     * 
     * @param \Model $model
     * @return \ChatMessagePresenter
     */
    public static function create(\Model $model) {
        return new ChatMessagePresenter($model);
    }

    public function __construct(\Model $model) {
        parent::__construct($model);
    }

    public function __toString() {
        return $this->me();
    }

    public function me() {
        return $this->model()->script;
    }

    public function script() {
        return $this->model()->script;
    }

    public function excerpt($length = 10, UserPresenter $user = null) {
        return ($user && $user->id() == $this->writer()->id() ? "You" : $this->writer()) . ": " . get_snippet($this->script(), $length);
    }

    /**
     * 
     * @return UserPresenter
     */
    public function writer() {
        return $this->model()->writer()->presenter();
    }

    public function time_humanized($timezone = null) {
        static $tz = [];
        if (!array_key_exists($timezone, $tz)) {
            $tz[$timezone] = date_human($this->model->time, $timezone);
        }
        return $tz[$timezone];
    }

    public function time($timezone = null, $format = "j F Y, h:i a") {
        static $tz = [];
        $time = $this->model()->time();
        $key = $timezone . $format . $time;
        if (!array_key_exists($key, $tz)) {
            $tz[$key] = dt($time, $format, null, $timezone);
        }
        return $tz[$key];
    }

    /**
     * 
     * @return ChatMessageModel
     */
    public function model() {
        return parent::model();
    }

}

class ChatMessageModelSet extends ModelSet {

    /**
     * 
     * @param \NotORM_Row $row
     * @return ChatMessageModel
     */
    protected function get(\NotORM_Row $row) {
        return ChatMessageModel::create($row);
    }

    /**
     * 
     * @return ChatMessagePresenterSet
     */
    public function presenterSet() {
        return ChatMessagePresenterSet::create($this->set());
    }

}

class ChatMessagePresenterSet extends PresenterSet {

    /**
     * 
     * @param \NotORM_Row $row
     * @return ChatMessagePresenter
     */
    protected function get(\NotORM_Row $row) {
        return ChatMessagePresenter::create(ChatMessageModel::create($row));
    }

    /**
     * 
     * @return ChatMessageModelSet
     */
    public function modelSet() {
        return ChatMessageModelSet::create($this->set());
    }

}

class ChatParticipantQuery extends ORM {

    /**
     * 
     * @param \PDO $connection
     * @param \NotORM_Structure $structure
     * @param \NotORM_Cache $cache
     * @return ChatParticipantQuery
     */
    public static function getInstance(\PDO $connection = null, \NotORM_Structure $structure = null, \NotORM_Cache $cache = null) {
        return parent::getInstance($connection, $structure, $cache);
    }

    public function __construct(\PDO $connection = null, \NotORM_Structure $structure = null, \NotORM_Cache $cache = null) {
        parent::__construct($connection, $structure, $cache);
    }

    /**
     * 
     * @param type $id
     * @return false|ChatParticipantModel
     */
    public function findById($id) {
        $result = $this->chat_participant("id = '$id'")->fetch();
        if ($result) {
            $result = $result->model();
        }
        return $result;
    }

}

class ChatParticipantModel extends Model {

    const ROLE_ADMIN = "admin";
    const ROLE_USER = "user";
    const ROLE_VIEWER = "viewer";
    const ROLE_DELETED = "delete";

    public function __construct(\NotORM_Row $model) {
        parent::__construct($model);
    }

    public function presenter() {
        return ChatParticipantPresenter::create($this);
    }

    /**
     * 
     * @param \NotORM_Row $row
     * @return \ChatParticipantModel
     */
    public static function create(\NotORM_Row $row) {
        return new ChatParticipantModel($row);
    }

    public static function assureRole($role) {
        if ($role === self::ROLE_ADMIN || $role === self::ROLE_DELETED || $role === self::ROLE_USER || $role === self::ROLE_VIEWER) {
            return $role;
        } else {
            return self::ROLE_USER;
        }
    }

    /**
     * 
     * @param type $message
     * @return false|ChatMessageModel
     * @throws Exception
     */
    public function sendMessage($message) {
        $this->assertExists();
        $message = trim($message);
        if (strlen($message) == 0) {
            throw new Exception("Empty message can not be sent");
        }
        $message = html_entity_encode($message);
        $messages = $this->row->chat_message();
        /* @var $messages NotORM_Result */
        $chatMessage = $messages->insert(["script" => $message, "time" => date('Y-m-d H:i:s'), "chat_id" => $this->row['chat_id']]);
        if ($chatMessage) {
            $chatMessage = $chatMessage->model();
        }
        return $chatMessage;
    }

    /**
     * 
     * @return ChatMessageModelSet
     */
    public function getNewMessages() {
        $this->assertExists();
        $messages = $this->getMessages("`id` > (select `last_check_chat_message_id` from chat_participant where id={$this->id})", true);
        return $messages;
    }

    /**
     * 
     * @param type $condition
     * @return ChatMessageModelSet
     */
    public function getMessages($condition = null, $updateLastCheck = false) {
        $this->assertExists();
        $messages = $this->chat()->messages($condition);
        if ($updateLastCheck) {
            $this->updateLastCheck();
        }
        return $messages;
    }

    protected function updateLastCheck() {
        $this->assertExists();
        $this->last_check = date('Y-m-d H:i:s');
        $this->last_check_chat_message_id = $this->getPdo()->query("select max(id) from chat_message where chat_id = '{$this->chat_id}'")->fetchColumn();
        $this->save();
    }

    /**
     * 
     * @return ChatModel
     */
    public function chat() {
        $this->assertExists();
        return $this->row->chat->model();
    }

    /**
     * 
     * @return UserModel
     */
    public function user() {
        return $this->raw()->user->model();
    }

    public function leave() {
        $this->set("role", "deleted", true);
        return true;
    }

}

class ChatParticipantModelSet extends ModelSet {

    /**
     * 
     * @param \NotORM_Row $row
     * @return ChatParticipantModel
     */
    protected function get(\NotORM_Row $row) {
        return ChatParticipantModel::create($row);
    }

    /**
     * 
     * @return ChatParticipantPresenterSet
     */
    public function presenterSet() {
        return ChatParticipantPresenterSet::create($this->rawSet());
    }

}

class ChatParticipantPresenter extends Presenter {

    /**
     * 
     * @return ChatParticipantModel
     */
    public function model() {
        return parent::model();
    }

    public function __toString() {
        return $this->me();
//        return $this->model()->user()->presenter() . "." . $this->model()->chat()->presenter(); //TODO
    }

    public function me() {
        return $this->model()->user()->presenter()->me();
    }

    /**
     * 
     * @param \Model $model
     * @return ChatParticipantPresenter
     */
    public static function create(\Model $model) {
        return new static($model);
    }

}

class ChatParticipantPresenterSet extends PresenterSet {

    /**
     * 
     * @param \NotORM_Row $row
     * @return ChatParticipantPresenter
     */
    protected function get(\NotORM_Row $row) {
        return ChatParticipantPresenter::create($row->model());
    }

    /**
     * 
     * @return ChatParticipantModelSet
     */
    public function modelSet() {
        return ChatParticipantModelSet::create($this->rawSet());
    }

    public function toCsv() {
        $result = "";
        foreach ($this as $participant) {
            /* @var $participant ChatParticipantPresenter */
            $result.=($result === "" ? "" : ", ") . $participant;
        }
        return $result;
    }

}
