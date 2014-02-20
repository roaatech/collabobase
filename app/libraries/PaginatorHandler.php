<?php

/**
 * @property Paginator $paginator
 */
class PaginatorHandler {

    protected $paginator = null;

    /**
     * 
     * @param type $paginator
     * @return \PaginatorHandler
     */
    public static function getInstance($paginator) {
        return new PaginatorHandler($paginator);
    }

    public function __construct($paginator) {
        if (is_array($paginator) && $paginator[0] instanceof Paginator) {
            $paginator = $paginator[0];
        } elseif (!($paginator instanceof Paginator)) {
            user_error("Should be a paginator instance", E_USER_ERROR);
            die;
        }
        $this->paginator = $paginator;
    }

}
