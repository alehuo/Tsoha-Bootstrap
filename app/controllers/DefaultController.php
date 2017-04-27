<?php

class DefaultController extends BaseController {

    /**
     * Etusivu.
     */
    public static function index() {
        View::make('index.html', array("timetable" => UserController::renderTimetable(), "timetablePage" => true));
    }

    public static function sandbox() {
        UserController::renderTimetable();
    }

    public static function unauthorizedPage() {
        View::make('unauthorized.html');
    }

}
