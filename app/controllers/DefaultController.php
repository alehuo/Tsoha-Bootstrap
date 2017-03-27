<?php
require "app/models/Kurssi.php";
class DefaultController extends BaseController {

    /**
     * Etusivu.
     */
    public static function index() {
		View::make('index.html');
    }

    public static function sandbox() {
		// Testaa koodiasi täällä
        $kurssi = Kurssi::find(1);
        Kint::dump($kurssi);
        var_dump($kurssi);
    }

}
