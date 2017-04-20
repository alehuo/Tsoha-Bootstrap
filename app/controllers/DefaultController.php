<?php

class DefaultController extends BaseController {

    /**
     * Etusivu.
     */
    public static function index() {
        View::make('index.html');
    }

    public static function sandbox() {
        echo "42";
    }

    public static function unauthorizedPage() {
        View::make('unauthorized.html');
    }

}
