<?php

class BaseController {

    public static function get_user_logged_in() {
        // Toteuta kirjautuneen käyttäjän haku tähän
        if (isset($_SESSION["user"])) {
            $user_id = $_SESSION["user"];
            $user = Kayttaja::find($user_id);

            return $user;
        }
        return null;
    }

    public static function check_logged_in() {
        // Toteuta kirjautumisen tarkistus tähän.
        // Jos käyttäjä ei ole kirjautunut sisään, ohjaa hänet toiselle sivulle (esim. kirjautumissivulle).
        if (!BaseController::get_user_logged_in()) {
            Redirect::to("/login", array("errors" => array("Ole hyvä ja kirjaudu sisään.")));
        }
    }

    /**
     * Palauttaa nykyisen aikaleiman.
     * @return int Aikaleima
     */
    public static function get_current_timestamp() {
        return time();
    }

}
