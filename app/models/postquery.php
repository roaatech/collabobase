<?php

class PostQuery extends ORM
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * @param type $id
     * @return PostModel
     */
    function findById($id)
    {
        $result = $this->post[$id];
        if ($result)
            $result = PostModel::create($result);
        return $result;
    }

    /**
     * 
     * @param type $conditions
     * @return NotORM_Result
     */
    function all($conditions = null)
    {
        $result = $this->post();
        if ($conditions) {
            $result->where($conditions);
        }
        return $result;
    }

    /**
     * 
     * @param type $conditions
     * @return NotORM_Result
     */
    function allRoots($conditions = null)
    {
        $result = $this->post("post_id is null");
        if ($conditions) {
            $result->where($conditions);
        }
        return $result;
    }

    /**
     * 
     * @param \PDO $connection
     * @param \NotORM_Structure $structure
     * @param \NotORM_Cache $cache
     * @return PostQuery
     */
    static function getInstance(\PDO $connection = null, \NotORM_Structure $structure = null, \NotORM_Cache $cache = null)
    {
        return new self($connection, $structure, $cache);
    }

    /**
     * 
     * @param type $title
     * @param type $content
     * @param type $user
     * @return boolean|PostModel
     */
    public function InsertNew($title, $content, UserModel $user, $fileId = null, PostModel $parent = null, array $rights = [])
    {
        try {

            if ($fileId === null) {
                $this->transaction = 'BEGIN';
            }

            $now = date('Y-m-d H:i:s');

            $post = $this->post()->insert(array(
                'title' => $title,
                'content' => $content,
                'user_id' => $user->id,
                'file_id' => $fileId,
                'post_id' => $parent ? $parent->id : null,
                'root_post_id' => $parent ? $parent->getRootPost()->id : null,
                'status' => PostModel::STATUS_DRAFT,
                'time' => $now,
                'last_update_time' => $now,
                'last_update_user_id' => $user->id,
                "last_reply_time" => $now,
                'last_reply_user_id' => $user->id,
                'rights' => "|" . implode("|", $rights) . "|",
            ));

            $postModel = PostModel::create($post);

            if ($fileId === null) {
                $this->transaction = 'COMMIT';
            }

            return $postModel;
        } catch (Exception $e) {

            if ($fileId === null) {
                $this->transaction = 'ROLLBACK';
            }
            return false;
        }
    }

}

/**
 * @property UserModel $user
 */
class PostModel extends Model
{

    const STATUS_ACTIVE = 'active';
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_DELETED = 'removed';
    const STATUS_CLOSED = 'closed';

    protected $user = null;

    public static function create(NotORM_Row $model)
    {
        return new PostModel($model);
    }

    /**
     * 
     * @param UserModel $user
     * @return PostModel
     */
    public static function createEmpty(UserModel $user)
    {
        $row = new NotORM_Row(array(
            "id" => null,
            "title" => "",
            "content" => "",
            "user_id" => $user->col('id'),
            "file_id" => null,
            "post_id" => null,
            "time" => date('Y-m-d H:i:s'),
            "status" => PostModel::STATUS_DRAFT,
            "last_update_user_id" => null,
            "last_update_time" => null,
            "root_post_id" => null,
            "last_reply_post_id" => null,
            "last_reply_user_id" => null,
            "last_reply_time" => null,
                ), new NotORM_Result('post', ORM::getInstance()));
        return new static($row, $user);
    }

    public function __construct(NotORM_Row $model, UserModel $user = null)
    {
        $this->assertRowTable($model, 'post');
        parent::__construct($model);
        @$oUser = $this->row->user;
        if ($oUser) {
            $user = $oUser->model();
        } elseif ($this->row['user_id']) {
            $user = UserQuery::getInstance()->findById($this->row['user_id']);
        } elseif ($user === null) {
            throw new Exception("A user should be provided if no user is set in the post raw data!", 1);
        }
        $this->user = $user;
    }

    public function presenter()
    {
        return PostPresenter::create($this);
    }

    public function col($col, $default = null)
    {
        switch (strtolower($col)) {
            default:
                $result = parent::col($col, $default);
        }
        if (!$result)
            $result = $default;
        return $result;
    }

    public function update($title = null, $content = null, CategoryModel $category = null, $tags = "", UserModel $user = null, array $rights = [])
    {
        $this->assertExists();

        $now = date('Y-m-d H:i:s');

        if ($user === null) {
            $user = $this->author();
        }

        $this->row['title'] = $title;
        $this->row['content'] = $content;
        $this->row['last_update_time'] = $now;
        $this->row['last_update_user_id'] = $user->id;
        $this->row['rights'] = "|" . implode("|", $rights) . "|";
        $this->row->update();

        $this->category($category, true);
        $this->setTags($tags);


        return $this;
    }

    public function setStatus($status)
    {
        if ($status != self::STATUS_ACTIVE && $status != self::STATUS_DELETED && $status != self::STATUS_DRAFT && $status != self::STATUS_PENDING && $status != self::STATUS_CLOSED)
            return false;
        $this->row['status'] = $status;
        $this->row->update();
        return $this;
    }

    /**
     * 
     * @return null|UserModel
     */
    public function author()
    {
        return $this->user;
    }

    /**
     * 
     * @return NotORM_Result
     */
    public function tags()
    {
        $result = $this->row->post_tag("tag_id in (select id from tag where tag_type_id is null and tag_id is null)");
        return $result;
    }

    /**
     * 
     * @return null|CategoryModel|PostModel
     */
    public function category(CategoryModel $category = null, $forceUpdate = false)
    {
        if ($category === null && !$forceUpdate) {
            $result = $this->row->post_tag("tag_id in (select id from category)")->fetch();
            if ($result) {
                $result = $result->tag;
            }
            if ($result) {
                $result = CategoryModel::create($result);
            }
            return $result;
        } else {
            $cur = $this->row->post_tag("tag_id in (select id from category)")->fetch();
            if ($category && $cur && $cur['tag_id'] == $category->id) {
                return $this;
            }
            if ($cur) {
                $cur->delete();
            }
            if (!$category) {
                return $this;
            }
            $this->addTag($category);
            return $this;
        }
    }

    public function setTags($tags)
    {
        $result = $this->row->result()->orm()->post_tag("post_id = {$this->id} and tag_id in (select id from tag where tag_type_id is null and tag_id is null and name not in ('" . str_replace(",", "','", $tags) . "'))");
        $result->delete();
        $this->addTags($tags);
    }

    public function addTags($tags)
    {
        foreach (explode(",", $tags) as $tag) {
            $this->addTagByName($tag);
        }
    }

    /**
     * 
     * @param type $tag
     * @return boolean|integer
     */
    protected function addTagByName($tag)
    {

        $tg = TagQuery::getInstance()->findByName($tag);

        $new = null;
        if (!$tg) {
            $tg = TagQuery::getInstance()->InsertNew($tag);
            $new = true;
        }

        if (!$new && $this->row->post_tag("tag_id = {$tg->id()}")->count()) {
            $new = false;
        }

        $new = true;

        if ($new) {
            return $this->addTag($tg);
        }

        return false;
    }

    /**
     * 
     * @param TagModel $tag
     * @return int
     */
    protected function addTag(TagModel $tag)
    {
        $tag = $this->row->post_tag()->insert(["tag_id" => $tag->id]);
        return $tag['id'];
    }

    /**
     * 
     * @return boolean
     */
    public function isModified()
    {
        return $this->time != $this->last_update_time;
    }

    /**
     * 
     * @return boolean
     */
    public function isSaved()
    {
        return $this->id != null;
    }

    /**
     * 
     * @return PostModel
     */
    public function getRootPost()
    {
        $this->assertExists();
        $result = $this->root_post_id;
        if (!$result) {
            $result = $this->id;
        }
        $result = PostQuery::getInstance()->findById($result);
        return $result;
    }

    /**
     * 
     * @return PostModel
     */
    public function getParentPost()
    {
        $this->assertExists();
        $result = $this->post_id;
        if ($result) {
            $result = PostQuery::getInstance()->findById($result);
        }
        return $result;
    }

    /**
     * 
     * @return boolean
     */
    public function isRootPost()
    {
        $this->assertExists();
        return !$this->post_id && !$this->root_post_id;
    }

    /**
     * 
     * @param type $content
     * @param type $title
     * @param UserModel $user
     * @return PostModel the reply post model object
     */
    public function addReply($content, $title, UserModel $user = null)
    {
        if ($user === null) {
            $user = $this->user;
        }
        $reply = PostQuery::getInstance()->InsertNew($title, $content, $user, $this->file_id, $this, []);
        if (!$reply) {
            return $reply;
        }
        $reply->setStatus(self::STATUS_ACTIVE);
        $this->updateReplies($reply, $user);
        if (!$this->isRootPost()) {
            $this->getRootPost()->updateReplies($reply, $user);
        }
        return $reply;
    }

    protected function updateReplies(PostModel $reply, UserModel $user = null)
    {
        if ($user === null) {
            $user = $this->user;
        }
        $this->set("last_reply_post_id", $reply->id);
        $this->set("last_reply_user_id", $user->id);
        $this->set("last_reply_time", date('Y-m-d H:i:s'));
        $this->set("total_replies", (@$this->total_replies? : 0) + 1);
        $this->save();
    }

    /**
     * 
     * @param type $conditions
     * @return NotORM_Result
     */
    public function getReplies($conditions = null)
    {
        $replies = PostQuery::getInstance()->all("root_post_id = {$this->id}");
        if ($conditions) {
            $replies->where($conditions);
        }
        return $replies;
    }

    public function isForFile()
    {
        return $this->file_id != null;
    }

    public function isDraft()
    {
        return $this->status == 'draft';
    }

    public function delete()
    {
        $this->assertExists();
        $this->setStatus(self::STATUS_DELETED);
        return true;
    }

    public function isOpen()
    {
        return $this->getRootPost()->status == "active";
    }

    public function close()
    {
        $this->assertExists();
        $this->setStatus(self::STATUS_CLOSED);
        return true;
    }

    public function isClosed()
    {
        return $this->status == self::STATUS_CLOSED;
    }

}

class PostPresenter extends Presenter
{

    function __construct(PostModel $model)
    {
        parent::__construct($model);
    }

    public function __toString()
    {
        return $this->me();
    }

    public static function create(Model $model)
    {
        if (!($model instanceof PostModel))
            return false;
        return new PostPresenter($model);
    }

    public function me()
    {
        $raw = $this->model()->raw();
        return $raw['title'];
    }

    public function title()
    {
        return $this->model()->col("title");
    }

    public function scrambled($length = 30)
    {
        $add = ".";
        $content = $this->model()->col('content');
        $content = strip_tags($content);
        if (stripos($content, " ") === false) {
            $add.="..";
            $content = mb_substr($content, 0, $length * 10);
        } else {
            $content = preg_replace("#\s+#", " ", $content);
            $content = explode(" ", $content);
            if (count($content) > $length) {
                $add.="..";
            }
            $content = array_slice($content, 0, $length);
            $content = join(" ", $content);
        }
        return $content . $add;
    }

    public function time($format = "Y-m-d H:i:s", $timezone = null)
    {
        $time = $this->model()->col('time');
        $date = new DateTime($time);
        if ($timezone != null) {
            $tz = new DateTimeZone($timezone);
            if ($tz)
                $date->setTimezone($tz);
        }
        return $date->format($format);
    }

    public function last_update_time($format = "Y-m-d H:i:s", $timezone = null)
    {
        $time = $this->model()->col('last_update_time');
        $date = new DateTime($time);
        if ($timezone != null) {
            $tz = new DateTimeZone($timezone);
            if ($tz)
                $date->setTimezone($tz);
        }
        return $date->format($format);
    }

    public function last_reply_time($format = "Y-m-d H:i:s", $timezone = null)
    {
        $time = $this->model()->col('last_reply_time');
        if (!$time)
            return null;
        $date = new DateTime($time);
        if ($timezone != null) {
            $tz = new DateTimeZone($timezone);
            if ($tz)
                $date->setTimezone($tz);
        }
        return $date->format($format);
    }

    public function writer()
    {
        $userId = $this->model()->col('user_id');
        $UserModel = UserQuery::getInstance()->findById($userId);
        return $UserModel->presenter();
    }

    public function updater()
    {
        $userId = $this->model()->col('last_update_user_id');
        $UserModel = UserQuery::getInstance()->findById($userId);
        return $UserModel->presenter();
    }

    public function lastReplier()
    {
        $userModel = null;
        $userId = $this->model()->col('last_reply_user_id');
        if ($userId) {
            $userModel = UserQuery::getInstance()->findById($userId);
        }
        if ($userModel) {
            return $userModel->presenter();
        } else {
            return null;
        }
    }

    public function viewUrl()
    {
        if ($this->model()->isDraft()) {
            return base_url("posts/edit/{$this->model()->col("id")}");
        } else {
            return base_url("posts/post/{$this->model()->col("id")}");
        }
    }

    /**
     * 
     * @return PostModel
     */
    public function model()
    {
        $model = parent::model();
        return $model;
    }

    /**
     * 
     * @return array
     */
    public function tagsArray()
    {
        $tags = $this->model()->tags();
        $result = [];
        foreach ($tags as $tag) {
            $tag = $tag->tag;
            $result[$tag['id']] = $tag['name'];
        }
        return $result;
    }

    public function tagsCsv($null = null)
    {
        $tags = $this->tagsArray();
        $result = implode(", ", $tags);
        if ($result == "") {
            $result = $null;
        }
        return $result;
    }

    public function totalReplies()
    {
        return $this->model()->col('total_replies')? : 0;
    }

    /**
     * @return string|CategoryPresenter
     */
    public function category()
    {
        $category = $this->model()->category();
        if (!$category) {
            $category = "Uncategorized";
        } else {
            $category = $category->presenter();
        }
        return $category;
    }

    public function status()
    {
        $status = $this->model()->status;
        switch ($status) {
            case PostModel::STATUS_ACTIVE:
                return "published";
                break;
            case PostModel::STATUS_DELETED:
                return "deleted";
                break;
            case PostModel::STATUS_DRAFT:
                return "drafted";
                break;
            case PostModel::STATUS_PENDING:
                return "pended";
                break;
        }
    }

    public function content()
    {
        return $this->model()->content;
    }

    public function __call($name, $arguments)
    {
        return $this->$name;
    }

    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'id':
            case 'content':
            case 'title':
            case 'post_id':
            case 'time':
                return $this->model()->$name;
                break;
        }
    }

    public function author()
    {
        return $this->model()->author()->presenter();
    }

    public function isClosed()
    {
        return $this->model()->isClosed();
    }

    public function accessRights()
    {
        $rights = $this->model()->rights;
        if ($rights === "||") {
            return __("Open");
        }
        $usersList = str_replace("|", ",", trim($rights, "|"));
        $users = UserQuery::getInstance()->all("id in ($usersList)", ORM::RETURN_AS_PRESENTER);
        $result = "";
        foreach ($users as $user) {
            /* @var $user UserPresenter */
            $result.=($result === "" ? "" : ", ") . $user->displayName();
        }
        return $result;
    }

}

class PostModelSet extends ModelSet
{

    /**
     * 
     * @param \NotORM_Row $row
     * @return PostModel
     */
    protected function get(\NotORM_Row $row)
    {
        return PostModel::create($row);
    }

    /**
     * 
     * @return PostPresenterSet
     */
    public function presenterSet()
    {
        return PostPresenterSet::create($this->rawSet());
    }

}

class PostPresenterSet extends PresenterSet
{

    /**
     * 
     * @param \NotORM_Row $row
     * @return PostPresenter
     */
    protected function get(\NotORM_Row $row)
    {
        return PostPresenter::create($row->model());
    }

    /**
     * 
     * @return PostModelSet
     */
    public function modelSet()
    {
        return PostModelSet::create($this->rawSet());
    }

}
