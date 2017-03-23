<?php

class DefaultController extends BaseController {

    /**
     * Etusivu.
     */
    public static function index() {
        View::make('index.html');
    }

    public static function sandbox() {
        // Testaa koodiasi täällä
        echo 'Hello World!';
    }

}
