<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'postquery.php';

class FileQuery extends ORM {

    /**
     * 
     * @param type $title
     * @param type $content
     * @param type $user
     * @return boolean|PostModel
     */
    public function insertNew($title, $description, UserModel $user) {
        try {


            $this->transaction = 'BEGIN';

            $now = date('Y-m-d H:i:s');

            $file = $this->file()->insert(array(
                'title' => $title,
                'user_id' => $user->id,
                'time' => $now,
                'update_time' => $now,
            ));

            $fileModel = FileModel::create($file);

            $postModel = PostQuery::getInstance()->InsertNew($title, $description, $user, $fileModel->id);
            $postModel->setStatus(PostModel::STATUS_ACTIVE);
            $fileModel->post($postModel);

            $this->transaction = 'COMMIT';

            return $fileModel;
        } catch (Exception $e) {
            $this->transaction = 'ROLLBACK';
            return false;
        }
    }

    /**
     * 
     * @param type $id
     * @return FileModel
     */
    public function findById($id) {
        $result = $this->file()->where("id = '$id'")->fetch();
        if ($result)
            $result = FileModel::create($result);
        return $result;
    }

    /**
     * 
     * @param string $condition
     * @param integer $returnAs
     * @return NotORM_Result|FileModelSet|FilePresenterSet
     */
    public function allActive($condition = null, $returnAs = self::RETURN_AS_MODEL) {
        $files = $this->file("`status` = 'active'");
        /* @var $files NotORM_Result */
        if ($condition) {
            $files->where($condition);
        }
        if ($returnAs == self::RETURN_AS_MODEL) {
            $files = FileModelSet::create($files);
        } elseif ($returnAs == self::RETURN_AS_PRESENTER) {
            $files = FilePresenterSet::create($files);
        }
        return $files;
    }

}

/**
 * @property PostModel $post
 * @property FileVersionModel $lastVersion
 */
class FileModel extends Model {

    protected $post = null;
    protected $lastVersion = null;

    /**
     * 
     * @return FilePresenter
     */
    public function presenter() {
        return FilePresenter::create($this);
    }

    /**
     * 
     * @param \NotORM_Row $row
     * @return \FileModel
     */
    public static function create(\NotORM_Row $row) {
        return new FileModel($row);
    }

    /**
     * 
     * @param type $fileName
     * @param type $originalName
     * @param type $fileType
     * @param UserModel $user
     * @return boolean|FileVersionModel
     */
    public function insertVersion($fileName, $originalName, $fileType, UserModel $user, $version, $size, $title = null, $description = null) {

        try {

            if (!$title) {
                $title = "New version";
            }
            if (!$description) {
                $description = "<p>Version {$version} is now ready for download.</p>";
            }

            //creating the post
            $post = $this->comment($user, $this->post(), $title, $description);
            $now = date('Y-m-d H:i:s');
            $versionModel = $this->raw()->file_version()->insert([
                "time" => $now,
                "file_type" => $fileType,
                "file_name" => $fileName,
                "original_name" => $originalName,
                "user_id" => $user->id,
                "post_id" => $post->id,
                "version" => $version,
                "file_size" => $size,
            ]);
            if (!$version) {
                return false;
            }
            $versionModel = FileVersionModel::create($versionModel);
            $versionModel->post($post);

            $this->updateVersion($version, $versionModel);

            return $versionModel;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    protected function updateVersion($version, FileVersionModel $fileVersion) {
        if (is_nan($version) || $version <= 0) {
            return false;
        }
        $this->set("version", $version);
        $this->set("last_version_id", $fileVersion->id);
        $this->set("update_time", date('Y-m-d H:i:s'));
        $this->save();
        $this->lastVersion($fileVersion);
        return true;
    }

    protected function fileVersion(FileVersionModel $fileVersion) {
        $this->lastVersion = $fileVersion;
    }

    /**
     * 
     * @param UserModel $user
     * @param PostModel $replyToPost
     * @param type $title
     * @param type $content
     * @return boolean|PostModel
     */
    public function comment(UserModel $user, PostModel $replyToPost, $title, $content) {
        if ($replyToPost->getRootPost()->id != $this->post()->id) {
            return false;
        }
        $post = $replyToPost->addReply($content, $title, $user);
        $this->set("update_time", date('Y-m-d H:i:s'));
        $this->save();
        return $post;
    }

    /**
     * Sets or gets the file post model object
     * @param PostModel $post
     * @return PostModel|null
     */
    public function post(PostModel $post = null) {
        if ($post && $post->file_id == $this->id) {
            $this->post = $post;
        }

        if ($this->post === null) {
            $post = $this->raw()->post()->fetch();
            if ($post) {
                $post = $post->model();
            }
            $this->post = $post;
        }

        return $this->post;
    }

    /**
     * 
     * @return null|FileVersionModel
     */
    public function getLastVersion() {
        $this->assertExists();
        if ($this->lastVersion == null) {
            if ($this->version == 0 || $this->last_version_id == null) {
                $this->lastVersion = null;
                return null;
            }
            $lastVersion = $this->raw()->file_version("id = {$this->last_version_id}")->fetch();
            if ($lastVersion) {
                $lastVersion = $lastVersion->model();
            }
            $this->lastVersion = $lastVersion;
        }
        return $this->lastVersion;
    }

    /**
     * 
     * @return FileVersionModelSet
     */
    public function versions($condition = null) {
        $result = $this->raw()->file_version();
        /* @var $result NotORM_Result */
        if ($condition) {
            $result->where($condition);
        }
        $result = FileVersionModelSet::create($result);
        return $result;
    }

    /**
     * 
     * @param type $title
     * @param type $content
     * @param UserModel $user
     * @return PostModel
     */
    public function addComment($title, $content, UserModel $user) {
        $this->assertExists();
        $post = $this->post()->addReply($content, $title, $user);
        return $post;
    }

    /**
     * 
     * @param PostPresenter $post
     * @return boolean
     */
    public function isPostForVersion(PostModel $post) {
        return $this->versions("post_id = {$post->id}")->count() > 0;
    }

    /**
     * 
     * @param PostPresenter $post
     * @return null|FileVersionModel
     */
    public function getPostVersion(PostModel $post) {
        $version = $this->versions("post_id = {$post->id}")->fetch();
        if ($version) {
            $version = $version->model();
        }
        return $version;
    }

    /**
     * 
     * @param type $condition
     * @return FileCommentModelSet
     */
    public function comments($condition = null) {
        $replies = $this->post()->getReplies($condition);
        return FileCommentModelSet::create($replies, $this);
    }

    /**
     * 
     * @param type $versionId
     * @return FileVersionModel
     */
    public function getVersion($versionId) {
        $version = $this->versions("id={$versionId}");
        $result = null;
        foreach ($version as $result)
            break;
        return $result;
    }

    public function remove() {
        $this->assertExists();
        $this->set("status", "deleted");
        $this->save();
        return true;
    }

    public function isRemoved() {
        return $this->status == "deleted";
    }

    /**
     * 
     * @return UserModel
     */
    public function uploader() {
        $this->assertExists();
        return $this->raw()->user->model();
    }

    /**
     * 
     * @return UserModel
     */
    public function lastUpdater() {
        return $this->getLastVersion()->user();
    }

    public function update($title, $content, $category, $tags) {
        try {
            $this->post()->update($title, $content, $category, $tags);
            $this->set("title", $title);
            $this->save();
            return false;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function updateLastVersion(FileVersionModel $lastVersion) {
        $this->assertExists();
        $this->updateVersion($lastVersion->version, $lastVersion);
    }

    public function removeVersion($id) {
        $fileVersion = $this->getVersion($id);
        if (!$fileVersion) {
            throw new Exception("Not a file version", 1);
        }
        $fileVersion->remove();
        foreach ($this->versions("status!='deleted'")->order("version desc") as $nextVersion)
            break;
        if ($nextVersion) {
            $this->last_version_id = $nextVersion->id;
            $this->version = $nextVersion->version;
            $this->save();
        } else {
            $this->remove();
        }
    }

    /**
     * 
     * @return CategoryModel|null
     */
    public function category() {
        $category = $this->post()->category();
        return $category;
    }

}

class FilePresenter extends Presenter {

    public function __toString() {
        return $this->me();
    }

    public function me() {
        return $this->title;
    }

    /**
     * 
     * @param \Model $model
     * @return \FilePresenter
     */
    public static function create(\Model $model) {
        return new FilePresenter($model);
    }

    public function description() {
        return $this->model()->post()->content;
    }

    /**
     * 
     * @return FileModel
     */
    public function model() {
        return parent::model();
    }

    /**
     * @return PostPresenterSet
     */
    public function comments() {
        return $this->model()->comments()->presenterSet();
    }

    public function versions() {
        return $this->model()->versions()->presenterSet();
    }

    /**
     * 
     * @return UserPresenter
     */
    public function uploader() {
        return $this->model()->uploader()->presenter();
    }

    /**
     * 
     * @return UserPresenter
     */
    public function lastUpdater() {
        return $this->model()->lastUpdater()->presenter();
    }

    /**
     * 
     * @return FileVersionPresenter
     */
    public function lastVersion() {
        return $this->model()->getLastVersion()->presenter();
    }

    /**
     * 
     * @return CategoryPresenter
     */
    public function category() {
        $category = $this->model()->category();
        if ($category) {
            $category = $category->presenter();
        }
        return $category;
    }

    /**
     * 
     * @return PostPresenter
     */
    public function post() {
        return $this->model()->post()->presenter();
    }

}

class FileModelSet extends ModelSet {

    /**
     * 
     * @param \NotORM_Row $row
     * @return FileModel
     */
    protected function get(\NotORM_Row $row) {
        return FileModel::create($row);
    }

    /**
     * 
     * @return FilePresenterSet
     */
    public function presenterSet() {
        return FilePresenterSet::create($this->rawSet());
    }

}

class FilePresenterSet extends PresenterSet {

    /**
     * 
     * @param \NotORM_Row $row
     * @return FilePresenter
     */
    protected function get(\NotORM_Row $row) {
        return FilePresenter::create($row->model());
    }

    /**
     * 
     * @return FileModelSet
     */
    public function modelSet() {
        return FileModelSet::create($this->rawSet());
    }

}

/**
 * @property PostModel $post
 * @property FileModel $file
 */
class FileVersionModel extends Model {

    protected $post = null;
    protected $file = null;

    /**
     * 
     * @return FileVersionPresenter
     */
    public function presenter() {
        return FileVersionPresenter::create($this);
    }

    /**
     * 
     * @param \NotORM_Row $row
     * @return \FileVersionModel
     */
    public static function create(\NotORM_Row $row) {
        return new static($row);
    }

    /**
     * 
     * @param PostModel $post
     * @return PostModel
     */
    public function post(PostModel $post = null) {
        if ($post && $post->id == $this->post_id) {
            $this->post = $post;
        }

        if ($this->post === null) {
            $this->post = $this->raw()->post->model();
        }

        return $this->post;
    }

    /**
     * 
     * @return FileModel
     */
    public function file() {
        $this->assertExists();
        if (!$this->file) {
            $this->file = $this->raw()->file->model();
        }
        return $this->file;
    }

    public function getDownloadName() {
        $this->assertExists();
        $name = str_replace([" ", "\t"], "_", $this->file()->title) . "_" . $this->version;
        $name.="." . pathinfo($this->file_name, PATHINFO_EXTENSION);
        return $name;
    }

    /**
     * 
     * @return UserModel
     */
    public function user() {
        $this->assertExists();
        return $this->raw()->user->model();
    }

    public function remove() {
        $this->assertExists();
//        unlink(ROOT_PATH . DIRECTORY_SEPARATOR . "assets/uploads/{$this->file_name}");
//        $this->raw()->post->delete();
        $this->set("status", "deleted");
        $this->save();
        return true;
    }

}

class FileVersionPresenter extends Presenter {

    public function __toString() {
        return $this->me();
    }

    public function me() {
        return $this->version;
    }

    /**
     * 
     * @param \Model $model
     * @return \FileVersionPresenter
     */
    public static function create(\Model $model) {
        return new static($model);
    }

    /**
     * 
     * @return FileVersionModel
     */
    public function model() {
        return parent::model();
    }

    /**
     * 
     * @return UserPresenter
     */
    public function user() {
        return $this->model()->user()->presenter();
    }

    public function size($unit = "b") {
        $size = $this->file_size;
        $divider = 1;
        switch ($unit) {
            case 'kb':
                $divider = 1024;
                break;
            case 'mb':
                $divider = 1024 * 1024;
                break;
            case 'gb':
                $divider = 1024 * 1024 * 1024;
                break;
            case 'tb':
                $divider = 1024 * 1024 * 1024 * 1024;
                break;
        }
        return round($size / $divider, 2);
    }

}

class FileVersionModelSet extends ModelSet {

    /**
     * 
     * @param \NotORM_Row $row
     * @return FileVersionModel
     */
    protected function get(\NotORM_Row $row) {
        return FileVersionModel::create($row);
    }

    /**
     * 
     * @return FileVersionPresenterSet
     */
    public function presenterSet() {
        return FileVersionPresenterSet::create($this->rawSet());
    }

}

class FileVersionPresenterSet extends PresenterSet {

    /**
     * 
     * @param \NotORM_Row $row
     * @return FileVersionPresenter
     */
    protected function get(\NotORM_Row $row) {
        return FileVersionPresenter::create($row->model());
    }

    /**
     * 
     * @return FileVersionModelSet
     */
    public function modelSet() {
        return FileVersionModelSet::create($this->rawSet());
    }

}

/**
 * @property FileVersionModel $version
 */
class FileCommentModel extends PostModel {

    protected $version = false;

    /**
     * 
     * @param \NotORM_Row $model
     * @return \FileCommentModel
     */
    public static function create(\NotORM_Row $model) {
        return new FileCommentModel($model);
    }

    public function __construct(\NotORM_Row $model, \UserModel $user = null) {
        parent::__construct($model, $user);
    }

    /**
     * 
     * @return FileModel
     */
    public function file() {
        return FileQuery::getInstance()->findById($this->file_id);
    }

    /**
     * 
     * @return FileCommentPresenter
     */
    public function presenter() {
        return parent::presenter();
    }

    /**
     * 
     * @return FileVersionModelSet
     */
    protected function version() {
        if ($this->version === false) {
            $version = $this->file()->versions("post_id = {$this->id}");
            $result = null;
            foreach ($version as $result) {
                break;
            }
            $this->version = $result;
        }
        return $this->version;
    }

    public function isVersion() {
        return $this->version() != false;
    }

    /**
     * 
     * @return null|FileVersionModel
     */
    public function getVersion() {
        return $this->version();
    }

}

class FileCommentPresenter extends PostPresenter {

    /**
     * 
     * @param \Model $model
     * @return \FileCommentPresenter
     */
    public static function create(\Model $model) {
        return new FileCommentPresenter($model);
    }

    public function __construct(\PostModel $model) {
        parent::__construct($model);
        $this->model = FileCommentModel::create($model->raw());
    }

    /**
     * 
     * @return FileCommentModel
     */
    public function model() {
        return parent::model();
    }

    public function isVersion() {
        return $this->model()->isVersion();
    }

    /**
     * 
     * @return null|FileVersionPresenter
     */
    public function getVersion() {
        $version = $this->model()->getVersion();
        if ($version) {
            $version = $version->presenter();
        }
        return $version;
    }

}

/**
 * @property FileModel $fileModel
 */
class FileCommentModelSet extends PostModelSet {

    protected $fileModel = null;

    /**
     * 
     * @param \NotORM_Result $set
     * @param FileModel $file
     * @return \FileCommentModelSet
     */
    public static function create(\NotORM_Result $set, FileModel $file) {
        return new FileCommentModelSet($set, $file);
    }

    public function __construct(\NotORM_Result $set, FileModel $file) {
        parent::__construct($set);
        $this->fileModel = $file;
    }

    public function file() {
        return $this->fileModel;
    }

    /**
     * 
     * @param \NotORM_Row $row
     * @return FileCommentModel
     */
    protected function get(\NotORM_Row $row) {
        return FileCommentModel::create($row);
    }

    /**
     * 
     * @return FileCommentPresenterSet
     */
    public function presenterSet() {
        return FileCommentPresenterSet::create($this->rawSet(), $this->fileModel->presenter());
    }

}

/**
 * @property FilePresenter $filePresenter
 */
class FileCommentPresenterSet extends PostPresenterSet {

    protected $filePresenter = null;

    /**
     * 
     * @param \NotORM_Result $set
     * @param FilePresenter $file
     * @return \FileCommentPresenterSet
     */
    public static function create(\NotORM_Result $set, FilePresenter $file) {
        return new FileCommentPresenterSet($set, $file);
    }

    public function __construct(\NotORM_Result $set, FilePresenter $file) {
        parent::__construct($set);
        $this->filePresenter = $file;
    }

    /**
     * 
     * @return FilePresenter
     */
    public function file() {
        return $this->filePresenter;
    }

    /**
     * 
     * @param \NotORM_Row $row
     * @return FileCommentPresenter
     */
    protected function get(\NotORM_Row $row) {
        return FileCommentPresenter::create($row->model());
    }

    /**
     * 
     * @return FileCommentModelSet
     */
    public function modelSet() {
        return FileCommentModelSet::create($this->rawSet(), $this->filePresenter->model());
    }

}
