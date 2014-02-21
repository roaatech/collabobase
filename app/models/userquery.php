<?php

class UserQuery extends ORM {

    function __construct() {
        parent::__construct();
    }

    /**
     * @param type $id
     * @return UserModel
     */
    function findById($id) {
        $result = $this->user[$id];
        if ($result)
            $result = UserModel::create($result);
        return $result;
    }

    /**
     * @param type $username
     * @param type $password
     * @return UserModel
     */
    function findByLogin($username, $password) {
        $result = $this->user("username=? and password=? and user_information.status='active'", $username, $password)->fetch();
        if ($result)
            $result = UserModel::create($result);
        return $result;
    }

    /**
     * 
     * @param type $conditions
     * @return NotORM_Result|UserPresenterSet|UserModelSet
     */
    function all($conditions = null, $returnAs = self::RETURN_AS_RAW) {
        $c = "(deleted is null or deleted != '1')";
        $result = $this->user();
        if ($conditions) {
            $c .= " and $conditions";
        }
        $result->where($c);
        switch ($returnAs) {
            case self::RETURN_AS_RAW:
                return $result;
                break;
            case self::RETURN_AS_MODEL:
                return UserModelSet::create($result);
                break;
            case self::RETURN_AS_PRESENTER:
                return UserPresenterSet::create($result);
                break;
        }
    }

    /**
     * 
     * @param \PDO $connection
     * @param \NotORM_Structure $structure
     * @param \NotORM_Cache $cache
     * @return UserQuery
     */
    static function getInstance(\PDO $connection = null, \NotORM_Structure $structure = null, \NotORM_Cache $cache = null) {
        return new self($connection, $structure, $cache);
    }

    /**
     * 
     * @param type $username
     * @param type $password
     * @param type $role
     * @return boolean|UserModel
     */
    function InsertNew($username, $password = null, $role = null, $options = null) {
        try {
            $this->transaction = 'BEGIN';

            if ($password == null)
                $password = $username;
            $password = md5($password);

            if (empty($role))
                $role = UserModel::ROLE_USER;

            if (!UserModel::isValidRole($role))
                throw new Exception("$role is invalid role");

            $user = $this->user()->insert(array(
                'username' => $username,
                'password' => $password,
                'first_name' => key_or_default($options, 'first_name'),
                'last_name' => key_or_default($options, 'last_name'),
                'role' => $role,
                'note' => key_or_default($options, 'note')
            ));

            $user->user_information()->insert(array('status' => UserModel::STATUS_ACTIVE));
            $user->user_profile()->insert(array('user_id' => $user['id']));
            $user->user_contact()->insert(array('user_id' => $user['id']));
            $user->user_address()->insert(array('user_id' => $user['id']));
            $user->user_mission()->insert(array('user_id' => $user['id']));
            $user->user_emergency_contact()->insert(array('user_id' => $user['id']));

            $userMode = UserModel::create($user);

            $this->transaction = 'COMMIT';
            return $userMode;
        } catch (Exception $e) {
            $this->transaction = 'ROLLBACK';
            return false;
        }
    }

}

class UserModel extends Model {

    const ROLE_ADMIN = 'admin';
    const ROLE_SUPERVISOR = 'supervisor';
    const ROLE_USER = 'user';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';

    function __construct(NotORM_Row $model) {
        $this->assertRowTable($model, 'user');
        parent::__construct($model);
    }

    static function isValidRole($role) {
        return $role == self::ROLE_ADMIN || $role == self::ROLE_SUPERVISOR || $role == self::ROLE_USER;
    }

    function removeRole($role) {
        $this->assertExists();
        if (!self::isValidRole($role))
            throw new Exception("$role is invalid role value");
        $this->row['role'] = ltrim(str_replace(',,', ',', str_replace($role, "", $this->row['role'])), ',');
        $this->row->update();
        return true;
    }

    function addRole($role) {
        $this->assertExists();
        if (!self::isValidRole($role))
            throw new Exception("$role is invalid role value");
        $r = explode(",", $this->col('role'));
        array_push($r, $role);
        $this->row['role'] = implode(",", $r);
        $this->row->update();
        return true;
    }

    function updatePassword($password) {
        $password = md5($password);
        $this->row['password'] = $password;
        $this->row->update();
    }

    function updateProfile($data) {
        $profile = $this->row;
        $mission = $this->row->user_mission;
        $address = $this->row->user_address;
        $contact = $this->row->user_contact;
        $emergency = $this->row->user_emergency_contact;

        $object = $profile;
        foreach ($data as $field => $value) {
            $name_parts = explode("_", $field);
            $type = $name_parts[0];

            switch (strtolower($type)) {
                case 'mission':
                case 'address':
                case 'contact':
                case 'emergency':
                    unset($name_parts[0]);
                    $field = implode("_", $name_parts);
                    $object = $$type;
                    break;
                default:
                    $object = $profile;
            }

            $object[$field] = $value;
        }

        $profile->update();
        $mission->update();
        $address->update();
        $contact->update();
        $emergency->update();

        return true;
    }

    function getFile($fileId) {
        $this->assertExists();
        $file = $this->row->user_file()->where("id='$fileId'");
        $row = $file->fetch();
        return UserFileModel::create($row);
    }

    public function presenter() {
        return UserPresenter::create($this);
    }

    public static function create(NotORM_Row $model) {
        if (!$model->isOfTable('user'))
            return false;
        return new UserModel($model);
    }

    public function checkRole($role) {
        if (stripos($this->row['role'], $role) > -1)
            return true;
        else
            return false;
    }

    function isAdmin() {
        $this->assertExists();
        return $this->checkRole(UserModel::ROLE_ADMIN);
    }

    function isSupervisor() {
        $this->assertExists();
        return $this->checkRole(UserModel::ROLE_SUPERVISOR);
    }

    function isUser() {
        $this->assertExists();
        return $this->checkRole(UserModel::ROLE_USER);
    }

    function col($col, $default = null) {
        switch (strtolower($col)) {
            case 'full_name':
                $result = $this->row['first_name'];
                if (!empty($this->row['second_name']))
                    $result.=' ' . $this->row['second_name'];
                if (!empty($this->row['last_name']))
                    $result.=' ' . $this->row['last_name'];
                break;
            case 'main_phone':
            case 'alternative_phone':
            case 'email':
            case 'skype_id':
                $result = $this->row->user_contact[$col];
                break;
            default:
                $result = parent::col($col, $default);
        }
        if (!$result)
            $result = $default;
        return $result;
    }

    function insertFile($fileName, $fileType, $title, $description, $origName, $uploadData = null) {
        $file = $this->row->user_file()->insert(array('user_id' => $this->col('id'), 'file_type' => $fileType, 'file_name' => $fileName, 'title' => $title, 'description' => $description, 'local_name' => $origName, 'others' => serialize($uploadData)));
        return $file;
    }

    function changeStatus($suspend = true) {
        $this->assertExists();
        $this->raw()->user_information["status"] = $suspend ? self::STATUS_PAUSED : self::STATUS_ACTIVE;
        $this->raw()->user_information->update();
        return true;
    }

    function updateLastDiscussionsCheck() {
        $this->row->user_information['last_discussions_check'] = date('Y-m-d H:i:s');
        $this->row->user_information()->update();
    }

    function updateLastAccess() {
        $this->row->user_information['last_access'] = date('Y-m-d H:i:s');
        $this->row->user_information()->update();
    }

    function getLastAccessDate() {
        return $this->col('user_information:last_access');
    }

    function getLastDiscussionsCheckDate() {
        return $this->col('user_information:last_discussions_check');
    }

    public function deletable(CurrentUser $currentUser) {
        return $this->deleted != 1 && $this->id != $currentUser->model()->id;
    }

    public function delete() {
        $this->changeStatus($suspend);
        $this->set("deleted", 1);
        $this->set("note", $this->note . ", originally for username " . $this->username);
        $this->set("username", md5($this->username . $this->id));
        $this->save();
        return true;
    }

}

class UserModelSet extends ModelSet {

    /**
     * 
     * @param \NotORM_Row $row
     * @return \UserModel
     */
    protected function get(\NotORM_Row $row) {
        return UserModel::create($row);
    }

    /**
     * 
     * @return \UserPresenterSet
     */
    public function presenterSet() {
        return UserPresenterSet::create($this->rawSet());
    }

}

class UserPresenter extends Presenter {

    function __construct(UserModel $model) {
        parent::__construct($model);
    }

    public function __toString() {
        return $this->me();
    }

    public static function create(Model $model) {
        if (!($model instanceof UserModel))
            return false;
        return new UserPresenter($model);
    }

    public function displayName() {
        $name = $this->first_name;
        $name .= ($name && $this->last_name ? " " : "") . $this->last_name;
        return $name;
    }

    public function me() {
        $name = $this->username;
        return $name;
//        $name = $this->displayName();
//        return "<a href='" . base_url("users/view/{$this->id}") . "' title='$name'>$name</a>";
    }

    function roleList() {
        return implode(", ", array_map(function($v) {
                    return __(ucfirst(strtolower($v)));
                }, explode(",", $this->model->col('role'))));
    }

    public function id() {
        return $this->model()->id;
    }

}

class UserPresenterSet extends PresenterSet {

    /**
     * 
     * @param \NotORM_Row $row
     * @return \UserPresenter
     */
    protected function get(\NotORM_Row $row) {
        return UserPresenter::create($row->model());
    }

    /**
     * @return UserModelSet
     */
    public function modelSet() {
        return UserModelSet::create($this->rawSet());
    }

    public function asSelectListOptions($selected = null) {
        $options = "";
        foreach ($this as $user) {
            /* @var $user UserPresenter */
            $selectedText = "";
            if ($selected && $user->id == $selected) {
                $selectedText = " selected='selected'";
            }
            $options .= "<option value='{$user->id()}'{$selectedText}>{$user->displayName()} ({$user->me()})</option>";
        }
        return $options;
    }

}

/**
 * @property UserModel $model
 * @property UserPresenter $presenter
 */
class CurrentUser {

    static protected $model = null;
    static protected $timeZone = 'Asia/Dubai';

    public function presenter() {
        return $this->model()->presenter();
    }

    public function model() {
        return self::$model;
    }

    public static function getInstance() {
        return new self();
    }

    public function __construct() {
        if (!self::$model) {
            $controller = CI_Controller::get_instance();
            $userId = $controller->session->userdata('current_user');
            if (!$userId)
                throw new Exception('not logged in');
            $userQuery = new UserQuery();
            $model = $userQuery->findById($userId);
            if (!$model)
                throw new Exception('invalid user id for current user');
            self::$model = $model;
        }
    }

    public function dateTime($date, $format = null) {
        $tz = new DateTimezone(self::$timeZone);
        if ($format == null)
            $format = 'Y/m/d h:i:sa';
        $date = new DateTime($date);
        $date->setTimezone($tz);
        return $date->format($format);
    }

    public function getTimezone() {
        return self::$timeZone;
    }

    public function isAdmin() {
        return self::$model->isAdmin();
    }

    public function isSupervisor() {
        return self::$model->isSupervisor();
    }

    public static function timezone() {
        return self::$timeZone;
    }

    public function canReplyToPost(PostModel $post) {
        $parent = $post->getRootPost();
        $open = $parent->isOpen();
        $allowed = true;
        //Later, in case of privileges
        return $open && $allowed;
    }

    public function canEditPost(PostModel $post) {
        return $post->author()->id === $this->model()->id && $post->isOpen() || $this->isAdmin() || $this->isSupervisor();
    }

    public function canClosePost(PostModel $post) {
        return $this->isAdmin() || $this->isSupervisor();
    }

    public function canEditFile(FileModel $file) {
        return $file->user_id == $this->model()->id || $this->isAdmin() || $this->isSupervisor();
    }

    public function canEditChat(ChatModel $chat) {
        return $chat->user_id == $this->model()->id || $this->isAdmin() || $chat->getChatParticipant($this->model())->role == ChatParticipantModel::ROLE_ADMIN;
    }

    public function getDisplayLanguage() {
        //should read it from db
        $displayLanguage = MY_Controller::get_instance()->session->userdata("display_language");
        if (!$displayLanguage) {
            $displayLanguage = "english";
            MY_Controller::get_instance()->session->set_userdata("display_language", "english");
        }
        return $displayLanguage;
    }

}

class UserFileModel extends Model {

    function __construct(\NotORM_Row $model) {
        $this->assertRowTable($model, 'user_file');
        parent::__construct($model);
    }

    public function presenter() {
        return UserFilePresenter::create($this);
    }

    public static function create(\NotORM_Row $row) {
        return new UserFileModel($row);
    }

    public function update($title, $description = "") {
        $this->assertExists();
        $this->row["title"] = $title;
        $this->row["description"] = $description;
        $this->row->update();
        return true;
    }

    public function delete() {
        $this->assertExists();
        $this->row->delete();
        return true;
    }

}

class UserFilePresenter extends Presenter {

    function __construct(\Model $model) {
        parent::__construct($model);
    }

    public function __toString() {
        return $this->model()->col('title');
    }

    public function me() {
        return $this->__toString();
    }

    public static function create(\Model $model) {
        return new UserFilePresenter($model);
    }

    function getUrl() {
        $this->model()->assertExists();
        return base_url('assets/uploads/' . $this->model()->col('file_name'));
    }

}
