<?php

class PageQuery extends ORM {

    /**
     * 
     * @param \PDO $connection
     * @param \NotORM_Structure $structure
     * @param \NotORM_Cache $cache
     * @return PageQuery
     */
    public static function getInstance(\PDO $connection = null, \NotORM_Structure $structure = null, \NotORM_Cache $cache = null) {
        return parent::getInstance($connection, $structure, $cache);
    }

    public function __construct(\PDO $connection = null, \NotORM_Structure $structure = null, \NotORM_Cache $cache = null) {
        parent::__construct($connection, $structure, $cache);
    }

    /**
     * 
     * @param type $condition
     * @return PageModelSet
     */
    public function all($condition = NULL) {
        $pages = $this->page();
        if ($condition) {
            $pages->where($condition);
        }
        echo $pages;
        return PageModelSet::create($pages);
    }

    /**
     * 
     * @param type $id
     * @return PageModel
     */
    public function findById($id) {
        $obj = $this->page("id=$id")->fetch();
        if ($obj) {
            $obj = PageModel::create($obj);
        }
        return $obj;
    }

    /**
     * 
     * @param type $id
     * @return PageModel
     */
    public function findByName($name, $language = "en") {
        $row = $this->page("name = '$name' and language = '$language'")->fetch();
        if ($row) {
            $row = PageModel::create($row);
        }
        return $row;
    }

}

class PageModelSet extends ModelSet {

    /**
     * 
     * @param \NotORM_Row $row
     * @return PageModel
     */
    protected function get(\NotORM_Row $row) {
        return PageModel::create($row);
    }

    /**
     * 
     * @return PagePresenterSet
     */
    public function presenterSet() {
        return PagePresenterSet::create($this->rawSet());
    }

}

class PageModel extends Model {

    /**
     * 
     * @return PagePresenter
     */
    public function presenter() {
        return PagePresenter::create($this);
    }

    /**
     * 
     * @param \NotORM_Row $row
     * @return \PageModel
     */
    public static function create(\NotORM_Row $row) {
        return new static($row);
    }

}

class PagePresenterSet extends PresenterSet {

    /**
     * 
     * @param \NotORM_Row $row
     * @return PagePresenter
     */
    protected function get(\NotORM_Row $row) {
        return PagePresenter::create($model);
    }

    /**
     * 
     * @return PageModelSet
     */
    public function modelSet() {
        return PageModelSet::create($this->rawSet());
    }

}

class PagePresenter extends Presenter {

    public function __toString() {
        return $this->me();
    }

    public function me() {
        return $this->title;
    }

    /**
     * 
     * @param \Model $model
     * @return \PagePresenter
     */
    public static function create(\Model $model) {
        return new static($model);
    }

}
