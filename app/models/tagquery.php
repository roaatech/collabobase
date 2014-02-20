<?php

class TagTypeQuery extends ORM {

    /**
     * 
     * @param \PDO $connection
     * @param \NotORM_Structure $structure
     * @param \NotORM_Cache $cache
     * @return TagTypeQuery
     */
    public static function getInstance(\PDO $connection = null, \NotORM_Structure $structure = null, \NotORM_Cache $cache = null) {
        return new static($connection, $structure, $cache);
    }

    public function __construct() {
        parent::__construct();
    }

    /**
     * @param type $id
     * @return TagTypeModel
     */
    public function findById($id) {
        $result = $this->tag_type[$id];
        if ($result)
            $result = TagTypeModel::create($result);
        return $result;
    }

    /**
     * @param type $id
     * @return TagTypeModel
     */
    public function findByName($name) {
        $result = $this->tag_type()->where("name like '$name'")->fetch();
        if ($result)
            $result = TagTypeModel::create($result);
        return $result;
    }

    /**
     * 
     * @param type $conditions
     * @return NotORM_Result
     */
    public function all($conditions = null) {
        return $this->tag_type($conditions);
    }

    /**
     * 
     * @param type $title
     * @param type $content
     * @param type $user
     * @return boolean|TagTypeModel
     */
    public function InsertNew($name) {
        try {
            $this->transaction = 'BEGIN';

            $data = $this->tag_type()->insert(array(
                'name' => $name,
            ));

            $tagTypeModel = TagTypeModel::create($data);

            $this->transaction = 'COMMIT';

            return $tagTypeModel;
        } catch (Exception $e) {

            $this->transaction = 'ROLLBACK';
            return false;
        }
    }

}

class TagTypeModel extends Model {

    public function __construct(NotORM_Row $model) {
        $this->assertRowTable($model, 'tag_type');
        parent::__construct($model);
    }

    public function presenter() {
        return TagTypePresenter::create($this);
    }

    /**
     * @param NotORM_Row $model
     * @return \TagTypeModel|boolean
     */
    public static function create(NotORM_Row $model) {
        if (!$model->isOfTable('tag_type'))
            return false;
        return new TagTypeModel($model);
    }

    public function name($name = null) {
        $this->assertExists();
        if ($name === null) {
            return $this->col('name');
        } else {
            $this->row['name'] = $name;
            $this->row->update();
            return $this;
        }
    }

    public function id() {
        $this->assertExists();
        return $this->col('id');
    }

    /**
     * 
     * @return NotORM_Result|array Array of TagModel objects
     */
    public function tags(callable $callback = null) {
        if ($callback) {
            return array_map($callback, $this->row->tag());
        } else {
            return $this->row->tag();
        }
    }

    /**
     * 
     * @param type $name
     * @param type $dataType
     * @param TagModel $parent
     * @return TagModel
     */
    public function insertTag($name, $dataType = TagModel::DATA_TYPE_STRING, TagModel $parent = null) {
        return TagQuery::getInstance()->InsertNew($name, $dataType, $this, $parent);
    }

}

class TagTypePresenter extends Presenter {

    public function __construct(TagTypeModel $model) {
        parent::__construct($model);
    }

    public function __toString() {
        return $this->me();
    }

    public static function create(Model $model) {
        if (!($model instanceof TagTypeModel))
            return false;
        return new TagTypePresenter($model);
    }

    public function me() {
        return $this->model()->col("name");
    }

}

class TagQuery extends ORM {

    const TAG_TYPE = null;
    const TAG_TYPE_NAME = null;

    /**
     * 
     * @param \PDO $connection
     * @param \NotORM_Structure $structure
     * @param \NotORM_Cache $cache
     * @return TagQuery
     */
    public static function getInstance(\PDO $connection = null, \NotORM_Structure $structure = null, \NotORM_Cache $cache = null) {
        return new static($connection, $structure, $cache);
    }

    public function __construct() {
        parent::__construct();
    }

    protected function getTagsOfType($type = null) {
        if (!$type) {
            return $this->tag("tag_type_id is null");
        }
        $temp = $type;
        if (!($temp instanceof TagTypeModel)) {
            $temp = TagTypeQuery::getInstance()->findById($type);
        }
        if (!$temp) {
            $temp = TagTypeQuery::getInstance()->findByName($type);
        }
        $type = $temp;
        if ($type instanceof TagTypeModel) {
            return $type->tags();
        } else {
            return null;
        }
    }

    /**
     * @param type $id
     * @return TagModel
     */
    public function findById($id) {
        $result = $this->getTagsOfType()->where("id = $id")->fetch();
        if ($result)
            $result = TagModel::create($result);
        return $result;
    }

    /**
     * @param type $name
     * @return TagModel
     */
    public function findByName($name) {
        $result = $this->getTagsOfType()->where("name like '$name'")->fetch();
        if ($result)
            $result = TagModel::create($result);
        return $result;
    }

    /**
     * 
     * @param type $parent
     * @param type $conditions
     * @return NotORM_Result|array|TagModelSet|TagPresenterSet
     */
    public function all($parent = null, $conditions = null, $returnType = self::RETURN_AS_RAW) {
        $result = $this->getTagsOfType($parent)->where($conditions);
        switch ($returnType) {
            case self::RETURN_AS_MODEL:
                return TagModelSet::create($result);
                break;
            case self::RETURN_AS_PRESENTER:
                return TagPresenterSet::create($result);
                break;
            default:
                return array_map(function(NotORM_Row $tag) {
                    return $tag->model();
                }, $result);
        }
    }

    /**
     * 
     * @param type $title
     * @param type $content
     * @param type $user
     * @return boolean|TagModel
     */
    public function InsertNew($name, $dataType = TagModel::DATA_TYPE_STRING, TagTypeModel $tagType = null, TagModel $parent = null) {
        try {
            $this->transaction = 'BEGIN';

            if ($dataType != TagModel::DATA_TYPE_BOOLEAN && $dataType != TagModel::DATA_TYPE_NUMBER && $dataType != TagModel::DATA_TYPE_STRING) {
                $dataType = TagModel::DATA_TYPE_STRING;
            }

            $tag = $this->tag()->insert(array(
                'name' => $name,
                'data_type' => $dataType,
                'tag_type_id' => $tagType !== null ? $tagType('id') : null,
                'tag_id' => $parent !== null ? $parent('id') : null,
            ));

            if (!$tag) {
                throw new Exception("Can not insert new tag with this name!");
            }

            $tagModel = TagModel::create($tag);

            $this->transaction = 'COMMIT';

            return $tagModel;
        } catch (Exception $e) {

            $this->transaction = 'ROLLBACK';
            return false;
        }
    }

    /**
     * 
     * @param type $value
     * @param TagTypeModel $tagType
     * @param TagModel $parent
     * @param type $dataType
     * @return NotORM_Result
     */
    public function searchByName($value, TagTypeModel $tagType = null, TagModel $parent = null, $dataType = null, $returnType = self::RETURN_AS_RAW) {
        $result = $this->getTagsOfType($tagType);

        if ($parent) {
            $result->where("tag_id = {$parent->col('id')}");
        } else {
            $result->where("tag_id is null");
        }

        if ($dataType && ($dataType == TagModel::DATA_TYPE_BOOLEAN || $dataType == TagModel::DATA_TYPE_NUMBER || $dataType == TagModel::DATA_TYPE_STRING)) {
            $result->where("data_type = '$dataType'");
        }

        $result->where("name like '$value%'");

        switch ($returnType) {
            case self::RETURN_AS_MODEL:
                return TagModelSet::create($result);
                break;
            case self::RETURN_AS_PRESENTER:
                return TagPresenterSet::create($result);
                break;
            default:
                return $result;
        }
    }

}

class TagModel extends Model {

    const DATA_TYPE_STRING = "string";
    const DATA_TYPE_BOOLEAN = "boolean";
    const DATA_TYPE_NUMBER = "number";

    /**
     * 
     * @param NotORM_Row $model
     * @return \TagModel|boolean
     */
    public static function create(NotORM_Row $model) {
        if (!$model->isOfTable('tag'))
            return false;
        return new static($model);
    }

    public function __construct(NotORM_Row $model) {
        $this->assertRowTable($model, 'tag');
        parent::__construct($model);
    }

    /**
     * 
     * @return TagPresenter
     */
    public function presenter() {
        return TagPresenter::create($this);
    }

    public function id() {
        $this->assertExists();
        return $this->col('id');
    }

    public function name($name = null) {
        $this->assertExists();
        if ($name === null) {
            return $this->col('name');
        } else {
            $this->row['name'] = $name;
            $this->row->update();
            return $this;
        }
    }

    /**
     * 
     * @return TagModel
     */
    public function parentTag(TagModel $tag = null, $forceUpdate = false) {
        $this->assertExists();
        if ($tag === null && !$forceUpdate) {
            if ($this->row['tag_id'] == null)
                $result = null;
            else
                $result = $this->row->tag->model();
            return $result;
        }else {
            $this->row['tag_id'] = $tag ? $tag->id() : null;
            $this->row->update();
            return $this;
        }
    }

    public function dataType($dataType = null) {
        $this->assertExists();
        if ($dataType !== null) {
            if ($dataType != TagModel::DATA_TYPE_BOOLEAN && $dataType != TagModel::DATA_TYPE_NUMBER && $dataType != TagModel::DATA_TYPE_STRING) {
                $dataType = TagModel::DATA_TYPE_STRING;
            }
            $this->row['data_type'] = $dataType;
            $this->row->update();
            return $this;
        } else {
            return $this->row['data_type'];
        }
    }

    /**
     * 
     * @param TagTypeModel $type
     * @return \TagModel
     */
    public function tagType(TagTypeModel $type = null, $forceUpdate = false) {
        $this->assertExists();
        if ($type === null && !$forceUpdate) {
            $result = $this->row->tag_type;
            if ($result != null) {
                $result = $result->model();
            }
            return $result;
        } else {
            $this->row['tag_type_id'] = $type ? $type->id() : null;
            $this->row->update();
            return $this;
        }
    }

    /**
     * 
     * @return CategoryModel|boolean
     */
    public function getCategoryIfApplicable() {
        $this->assertExists();
        if ($this->col('tag_type_id') == CategoryQuery::TAG_TYPE) {
            return CategoryModel::create($this->row);
        } else {
            return null;
        }
    }

}

class TagPresenter extends Presenter {

    public function __construct(TagModel $model) {
        parent::__construct($model);
    }

    public function __toString() {
        return $this->me();
    }

    /**
     * 
     * @param Model $model
     * @return \TagPresenter|boolean
     */
    public static function create(Model $model) {
        if (!($model instanceof TagModel))
            return false;
        return new static($model);
    }

    public function me() {
        $raw = $this->model()->raw();
        return $raw['name'];
    }

}

/**
 * @property TagTypeModel $categoryTagType
 * 
 */
class CategoryQuery extends TagQuery {

    const TAG_TYPE = 1;
    const TAG_TYPE_NAME = "category";

    protected $categoryTagType;

    public function __construct() {
        parent::__construct();
        $this->categoryTagType = TagTypeQuery::getInstance()->findByName(self::TAG_TYPE_NAME);
    }

    public function findById($id) {
        $results = $this->getTagsOfType($this->categoryTagType);
        $result = $results->where("id", $id)->fetch();
        if ($result) {
            $result = CategoryModel::create($result);
        }
        return $result;
    }

    /**
     * @param type $id
     * @return CategoryModel
     */
    public function findByName($name, CategoryModel $parent = null) {
        $results = $this->getTagsOfType($this->categoryTagType);
        if ($parent != null) {
            $results = $results->where("tag_id = " . $parent->id());
        }
        $result = $results->where("name = '$name'")->fetch();
        if ($result) {
            $result = CategoryModel::create($result);
        }
        return $result;
    }

    /**
     * 
     * @param type $conditions
     * @return NotORM_Result|CategoryModelSet|CategoryPresenterSet
     */
    public function all($conditions = null, $returnType = self::RETURN_AS_RAW) {
        $results = $this->getTagsOfType($this->categoryTagType);
        if ($conditions) {
            $results->where($conditions);
        }
        if ($returnType == self::RETURN_AS_RAW) {
            return $results;
        } elseif ($returnType == self::RETURN_AS_MODEL) {
            return CategoryModelSet::create($results);
        } else {
            return CategoryPresenterSet::create($results);
        }
    }

    /**
     * 
     * @param type $conditions
     * @return array
     */
    public function allForStates($conditions = null) {
        $result = CategoryStates::get("*", $conditions);
        return $result;
    }

    /**
     * 
     * @param CategoryModel $category
     * @return NotORM_Result
     */
    public function allUnder(CategoryModel $category = null) {
        if ($category === null) {
            $results = $this->all("tag_id is null");
        } else {
            $results = $category->raw()->tag();
        }
        return $results;
    }

    /**
     * 
     * @param type $name
     * @param \TagModel $parent
     * @return CategoryModel
     */
    public function InsertNew($name, \TagModel $parent = null) {
        $model = parent::InsertNew($name, TagModel::DATA_TYPE_STRING, $this->categoryTagType, $parent);
        if ($model) {
            $model = CategoryModel::create($model->raw());
        }
        return $model;
    }

}

class CategoryModel extends TagModel {

    /**
     * 
     * @param \NotORM_Row $model
     * @return CategoryModel|boolean
     */
    public static function create(\NotORM_Row $model) {
        return parent::create($model);
    }

    public function __construct(\NotORM_Row $model) {
        parent::__construct($model);
    }

    /**
     * 
     * @return NotORM_Result
     */
    public function subCategories() {
        $this->assertExists();
        $result = CategoryQuery::getInstance()->allUnder($this);
        return $result;
    }

    /**
     * 
     * @return CategoryPresenter
     */
    public function presenter() {
        return CategoryPresenter::create($this);
    }

    /**
     * 
     * @param type $name
     * @return CategoryModel
     */
    public function createSubCategory($name) {
        $this->assertExists();
        return CategoryQuery::getInstance()->InsertNew($name, $this);
    }

    public function delete() {
        try {

            $parent = $this->parentTag();

            $files = $this->row->file_tag();
            foreach ($files as $fileTag) {
//TODO: Remove file category through the file model instead of this way
                if ($parent) {
                    $fileTag['tag_id'] = $parent('id');
                    $fileTag->update();
                } else {
                    $fileTag->delete();
                }
            }

            $posts = $this->row->post_tag();
            foreach ($posts as $postTag) {
//TODO: Remove file category through the file model instead of this way
                if ($parent) {
                    $postTag['tag_id'] = $parent('id');
                    $postTag->update();
                } else {
                    $postTag->delete();
                }
            }

            $categories = $this->row->tag();
            foreach ($categories as $category) {
                if ($parent) {
                    $category['tag_id'] = $parent('id');
                } else {
                    $category['tag_id'] = null;
                }
                $category->update();
            }

            $this->row->delete();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

}

class CategoryPresenter extends TagPresenter {

    /**
     * 
     * @param \Model $model
     * @return CategoryPresenter|boolean
     */
    public static function create(\Model $model) {
        return parent::create($model);
    }

    /**
     * 
     * @return CategoryModel
     */
    public function model() {
        return parent::model();
    }

}

class CategoryStates {

    static protected $cache = [];

    /**
     * 
     * @param type $cols
     * @param type $where
     * @param type $orderBy
     * @param type $from
     * @param type $groupBy
     * @param type $limit
     * @return array
     */
    public static function get($cols = "*", $where = null, $orderBy = "parent,name", $from = null, $groupBy = null, $limit = null) {
        $pdo = CategoryQuery::getInstance()->getPdo();
        $query = "select $cols from category_states" . ($from !== null ? ", $from" : "") . ($where !== null ? " where $where" : "") . ($groupBy !== null ? " group by $groupBy" : "") . " order by $orderBy" . ($limit !== null ? " limit $limit" : "");
        $queryKey = md5($query);
        if (array_key_exists($queryKey, self::$cache)) {
            return self::$cache[$queryKey];
        } else {
            $statement = $pdo->query($query);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            self::$cache[$queryKey] = $result;
            return $result;
        }
    }

    /**
     * 
     * @param type $cols
     * @param type $where
     * @param type $orderBy
     * @param type $from
     * @param type $groupBy
     * @param type $limit
     * @return array
     */
    public static function getFlatTree($cols = "*", $where = null, $orderBy = "parent,name", $from = null, $groupBy = null, $limit = null) {
        $categories = self::get($cols, $where, $orderBy, $from, $groupBy, $limit);
        $tree = [];
        foreach ($categories as $row) {
            $key = $row['parent'];
            if ($key === null) {
                $key = 'root';
            }
            $tree[$key][] = $row;
        }
        return [$tree, count($categories)];
    }

    public static function getTree($cols = "*", $where = null, $orderBy = "parent,name", $from = null, $groupBy = null, $limit = null) {
        list($categories) = self::getFlatTree($cols, $where, $orderBy, $from, $groupBy, $limit);
        $result = self::createCategoryTree($categories, ['id' => 'root'], 'id');
        return $result;
    }

    static protected function createCategoryTree($categories, $category, $idName = 'id') {
        $result = [];
        $result['data'] = $category;
        if (array_key_exists($category[$idName], $categories)) {
            $result['children'] = [];
            foreach ($categories[$category[$idName]] as $subCategory) {
                $result['children'][$subCategory[$idName]] = self::createCategoryTree($categories, $subCategory, $idName);
            }
        }
        return $result;
    }

}

class TagModelSet extends ModelSet {

    /**
     * 
     * @param \NotORM_Row $row
     * @return TagModel
     */
    protected function get(\NotORM_Row $row) {
        return TagModel::create($row);
    }

    /**
     * 
     * @return TagPresenterSet
     */
    public function presenterSet() {
        return TagPresenterSet::create($this->rawSet());
    }

}

class TagPresenterSet extends PresenterSet {

    /**
     * 
     * @param \NotORM_Row $row
     * @return TagPresenter
     */
    protected function get(\NotORM_Row $row) {
        return TagPresenter::create($row);
    }

    /**
     * 
     * @return TagModelSet
     */
    public function modelSet() {
        return TagModelSet::create($this->rawSet());
    }

    public function asSelectListOptions($selected = null, $emptyOption = false) {
        $result = "";
        if ($emptyOption) {
            $result = "<option>{$emptyOption}</option>";
        }
        foreach ($this as $option) {
            /* @var $option TagPresenter */
            if ($selected instanceof TagModel || $selected instanceof TagPresenter) {
                $selected = $selected->id;
            }
            $selectedText = $option->id == $selected ? "selected='selected'" : "";
            $result.="<option {$selectedText} value='{$option->id}'>{$option->me()}</option>";
        }
        return $result;
    }

}

class CategoryModelSet extends TagModelSet {

    /**
     * 
     * @param \NotORM_Row $row
     * @return CategoryModel
     */
    protected function get(\NotORM_Row $row) {
        return CategoryModel::create($row);
    }

    /**
     * 
     * @return CategoryPresenterSet
     */
    public function presenterSet() {
        return CategoryPresenterSet::create($this->rawSet());
    }

}

class CategoryPresenterSet extends TagPresenterSet {

    /**
     * 
     * @param \NotORM_Row $row
     * @return CategoryPresenter
     */
    protected function get(\NotORM_Row $row) {
        return CategoryPresenter::create($row->model());
    }

    /**
     * 
     * @return CategoryModelSet
     */
    public function modelSet() {
        return CategoryModelSet::create($this->rawSet());
    }

    public function asSelectListOptionsTree($selected = null, $empty = false, $keepCategoryPath = false) {
        $array = [];
        foreach ($this as $category) {
            /* @var $category CategoryPresenter */
            $parent = $category->tag_id;
            if ($parent === null) {
                $parent = "root";
            }
            if (!array_key_exists($parent, $array)) {
                $array[$parent] = [];
            }
            $array[$parent][] = $category;
        }

        if ($selected instanceof CategoryModel || $selected instanceof CategoryPresenter) {
            $selected = $selected->id;
        }

        $result = "";
        if ($empty) {
            $result.="<option>{$empty}</option>";
        }
        $func = function($array, $key, $selected, $func) {
            static $level = 0;
            if (!array_key_exists($key, $array)) {
                return "";
            }
            $result = "";
            foreach ($array[$key] as $item) {
//                var_dump($item);
                $append = str_repeat("&nbsp;", $level * 3);
                $selectedText = $item->id == $selected ? "selected='selected'" : "";
                $result.="<option value='{$item->id}' {$selectedText}>{$append}{$item}</option>";
                $level++;
                $result.=$func($array, $item->id, $selected, $func);
                $level--;
            }
            return $result;
        };
        $result = $func($array, "root", $selected, $func);
        return $result;
    }

}
