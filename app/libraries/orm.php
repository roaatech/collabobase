<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include APPPATH . 'third_party/NotORM/NotORM.php';

/**
 * @property PDO $_pdo PDO connection
 */
class ORM extends NotORM {

    static protected $_pdo = null;

    const RETURN_AS_MODEL = 1;
    const RETURN_AS_PRESENTER = 2;
    const RETURN_AS_RAW = 3;

    public function __construct(\PDO $connection = null, \NotORM_Structure $structure = null, \NotORM_Cache $cache = null) {
        if ($connection == null) {
            if (self::$_pdo == null) {
                include APPPATH . "config/database.php";
                $ddb = $db["default"];
                $dsn = $ddb["dbdriver"] . ':host=' . $ddb["hostname"] . ';dbname=' . $ddb["database"];
                $username = $ddb["username"];
                $password = $ddb["password"];
                $options = array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $ddb["char_set"],
                );
                self::$_pdo = new \PDO($dsn, $username, $password, $options);
            }
            $connection = self::$_pdo;
        }
        if ($structure == null) {
            $structure = new ORM_Structure_Convention();
        }
        parent::__construct($connection, $structure, $cache);
    }

    static function getInstance(\PDO $connection = null, \NotORM_Structure $structure = null, \NotORM_Cache $cache = null) {
        return new static($connection, $structure, $cache);
    }

    static function lastError() {
        return array('code' => self::$_pdo->errorCode(), 'info' => self::$_pdo->errorInfo());
    }

    /**
     * 
     * @return \PDO
     */
    public function getPdo() {
        return self::$_pdo;
    }

}

class ORM_Structure_Convention extends NotORM_Structure_Convention {

    public function __construct($primary = 'id', $foreign = '%s_id', $table = '%s', $prefix = '') {
        parent::__construct($primary, $foreign, $table, $prefix);
    }

    public function getReferencedColumn($name, $table) {
        $result = parent::getReferencedColumn($name, $table);
//        var_dump("name: $name"); //the destination entity
//        var_dump("table: $table"); //the source entity
        $selector = "$name|$table";
        switch ($selector) {
            case "user_information|user":
            case "user_contact|user":
            case "user_address|user":
            case "user_mission|user":
            case "user_emergency_contact|user":
                $result = "id";
                break;
        }
//        var_dump("result: $result");
        return $result;
    }

    public function getPrimary($table) {
        $result = parent::getPrimary($table);
        switch ($table) {
            case "user_information":
                $result = "user_id";
                break;
            case "user_mission":
            case "user_contact":
            case "user_address":
            case "user_emergency_contact":
                $result = "user_id";
                break;
        }
        return $result;
    }

}

/**
 * @property NotORM_Row $row
 */
abstract class Model {

    const RETURN_AS_MODEL = 1;
    const RETURN_AS_PRESENTER = 2;
    const RETURN_AS_RAW = 3;

    protected $row = null;

    const PK_NAME = 'id';

    final function assertExists() {
        if (!$this->row)
            throw new Exception('using of a not-exists model');
    }

    final protected function assertRowTable(NotORM_Row $row, $table) {
        if (!$row->isOfTable($table))
            throw new Exception('Provided data model is not of type ' . $table);
    }

    public function __construct(NotORM_Row $model) {
        $this->setRow($model);
    }

    public function raw() {
        return $this->row;
    }

    abstract public static function create(NotORM_Row $row);

    protected function setRow(NotORM_Row $model) {
        $this->row = $model;
    }

    public function col($col, $default = null) {
        $row = $this->row;
        if (strpos($col, ":") > -1) {
            $joins = explode(":", $col);
            $col = $joins[count($joins) - 1];
            unset($joins[count($joins) - 1]);
            foreach ($joins as $relation)
                $row = $row->$relation;
        }
        $result = $row[$col];
        if (!$result)
            $result = $default;
        return $result;
    }

    abstract public function presenter();

    public function __toString() {
        return $this->presenter()->me();
    }

    public function cols(array $cols) {
        if (count($cols) == 0)
            return array();
        $result = [];
        foreach ($cols as $col) {
            $result[$col] = $this->col($col);
        }
        return $result;
    }

    public function __invoke() {
        if (count(func_get_args()) == 0) {
            return $this->__toString();
        } else {
            $args = func_get_args();
            return $this->cols($args);
        }
    }

    /**
     * 
     * @param type $key
     * @param type $value
     * @param type $save
     * @return \Model
     */
    protected function set($key, $value, $save = false) {
        $this->row[$key] = $value;
        if ($save) {
            $this->save();
        }
        return $this;
    }

    protected function save() {
        $this->row->update();
    }

    public function __get($name) {
        return $this->col($name);
    }

    public function __set($name, $value) {
        return $this->set($name, $value);
    }

    public function __call($name, $arguments) {
        if (count($arguments) == 0) {
            return $this->$name;
        } elseif (count($arguments) == 1) {
            return $this->set($name, $arguments[0]);
        } else {
            return $this->set($name, $arguments[0], $arguments[1]);
        }
    }

    /**
     * 
     * @return PDO
     */
    protected function getPdo() {
        $this->assertExists();
        return $this->raw()->result()->orm()->getPdo();
    }

}

abstract class Presenter {

    protected $model;

    public function __construct(Model $model) {
        $this->model = $model;
    }

    abstract public static function create(Model $model);

    /**
     * 
     * @return Model
     */
    public function model() {
        return $this->model;
    }

    abstract public function __toString();

    abstract public function me();

    public function __get($name) {
        return $this->model()->col($name);
    }

}

/**
 * @property NotORM_Result $result
 */
class Paginator implements Iterator {

    protected $result = null;
    protected $pageSize = 10;
    protected $page = 1;
    protected $total = 0;
    protected $current = 1;

    /**
     * 
     * @param NotORM_Result $result
     * @param type $page
     * @param type $pageSize
     * @return Paginator
     */
    public static function getInstance(NotORM_Result $result, $page = 1, $pageSize = 10) {
        return new static($result, $page, $pageSize);
    }

    public function __construct(NotORM_Result $result, $page = 1, $pageSize = 10) {
        $this->result = $result;
        $this->total = $this->result->count();
        $this->setPageSize($pageSize);
        $this->setPage($page);
        $this->result->limit($this->pageSize, $this->getStart() - 1);
    }

    protected function setPage($page) {
        if (is_int($page) && $page > 0 || $page + 0 > 0) {
            $page = $page + 0;
        } else {
            $page = 1;
        }
        $this->page = $page + 0;
    }

    protected function rewindKey() {
        $this->current = $this->getStart();
    }

    protected function setPageSize($pageSize) {
        if (is_int($pageSize) && $pageSize > 0)
            $this->pageSize = $pageSize;
    }

    public function getTotal() {
        return $this->total;
    }

    public function getStart() {
        $start = ($this->page - 1) * $this->pageSize + 1;
        if ($start > $this->total)
            $start = $this->total;
        return $start;
    }

    public function getEnd() {
        $end = $this->getStart() + $this->pageSize - 1;
        if ($end > $this->total)
            $end = $this->total;
        return $end;
    }

    public function getPagesCount() {
        return ceil($this->total / $this->pageSize);
    }

    public function getCurrentPage() {
        return $this->page;
    }

    public function current() {
        return $this->result->current();
    }

    public function key() {
        $this->result->key();
        return $this->current;
    }

    public function next() {
        $this->current++;
        return $this->result->next();
    }

    public function rewind() {
        $this->rewindKey();
        return $this->result->rewind();
    }

    public function valid() {
        return $this->result->valid();
    }

}

/**
 * @property NotORM_Result $set
 */
abstract class ResultSet implements Iterator {

    protected $set;

    /**
     * 
     * @param NotORM_Result $set
     * @return \ResultSet
     */
    public static function create(NotORM_Result $set) {
        return new static($set);
    }

    public function __construct(NotORM_Result $set) {
        $this->set = $set;
    }

    /**
     * 
     * @return NotORM_Result
     */
    public function rawSet() {
        return $this->set();
    }

    /**
     * 
     * @return NotORM_Result;
     */
    protected function set() {
        return $this->set;
    }

    /**
     * 
     * @param type $columns
     * @return \ResultSet
     */
    public function order($columns) {
        $this->set()->order($columns);
        return $this;
    }

    /**
     * 
     * @param type $condition
     * @param type $parameters
     * @return \ResultSet
     */
    public function where($condition, $parameters = array()) {
        $this->set()->where($condition, $parameters);
        return $this;
    }

    /**
     * 
     * @param type $limit
     * @param type $offset
     * @return \ResultSet
     */
    public function limit($limit, $offset = null) {
        $this->set()->limit($limit, $offset);
        return $this;
    }

    public function current() {
        return $this->set()->current();
    }

    public function key() {
        return $this->set()->key();
    }

    public function next() {
        return $this->set()->next();
    }

    public function rewind() {
        return $this->set()->rewind();
    }

    public function valid() {
        return $this->set()->valid();
    }

    public function count($field = "") {
        return $this->set()->count($field);
    }

    public function getOffset($offset) {
        $obj = $this->set()->offsetGet($offset);
        if ($obj) {
            $obj = $this->get($obj);
        } else {
            $obj = null;
        }
        return $obj;
    }

    abstract protected function get(NotORM_Row $row);
}

abstract class ModelSet extends ResultSet {

    /**
     * 
     * @return Model
     */
    public function current() {
        $result = parent::current();
        if ($result) {
            $result = $this->get($result);
            if (!($result instanceof Model)) {
                throw new Exception("The type of the result is not valid!", 1);
            }
        }
        return $result;
    }

    public function key() {
        return parent::key();
    }

    public function next() {
        return parent::next();
    }

    public function rewind() {
        return parent::rewind();
    }

    public function valid() {
        return parent::valid();
    }

    abstract public function presenterSet();
}

abstract class PresenterSet extends ResultSet {

    /**
     * 
     * @return Presenter
     */
    public function current() {
        $result = parent::current();
        if ($result) {
            $result = $this->get($result);
            if (!($result instanceof Presenter)) {
                throw new Exception("The type of the result is not valid!", 1);
            }
        }
        return $result;
    }

    public function key() {
        return parent::key();
    }

    public function next() {
        return parent::next();
    }

    public function rewind() {
        return parent::rewind();
    }

    public function valid() {
        return parent::valid();
    }

    abstract public function modelSet();
}

/**
 * @property ResultSet $result
 */
class ResultSetPaginator extends Paginator {

    /**
     * 
     * @param ResultSet $set
     * @param type $page
     * @param type $pageSize
     * @return ResultSetPaginator
     */
    public static function getInstance(ResultSet $set, $page = 1, $pageSize = 10) {
        return new static($set, $page, $pageSize);
    }

    public function __construct(ResultSet $set, $page = 1, $pageSize = 10) {
        $this->result = $set;
        $this->total = $this->result->count();
        $this->setPageSize($pageSize);
        $this->setPage($page);
        $this->result->limit($pageSize, $this->getStart() - 1);
    }

}
