<?php

class DefaultController extends BaseController {

    /**
     * Etusivu.
     */
    public static function index() {
        View::make('index.html', array("timetable" => UserController::renderTimetable(), "timetablePage" => true));
    }

    /**
     * Ei käyttöoikeutta -sivu
     */
    public static function unauthorizedPage() {
        View::make('unauthorized.html');
    }

}
