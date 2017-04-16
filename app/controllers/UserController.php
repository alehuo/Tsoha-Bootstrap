<?php

class UserController extends BaseController {

    public static function viewAddUserPage() {
        
    }

    public static function addUser() {
        $params = $_POST;

        $user = new Kayttaja(array(
            "tyyppi" => $params["tyyppi"],
            "nimi" => $params["username"],
            "salasana" => $params["password"]
        ));

        $errors = $user->errors();

        if (!$errors) {
            $user->save();
        } else {
            Redirect::to('/adduser', array("errors" => $errors));
            exit();
        }
    }

    public static function handleLogin() {
        $params = $_POST;

        $user = Kayttaja::authenticate($params["username"], $params["password"]);

        if ($user) {
            //Kaikki ok
            $_SESSION["user"] = $user->id;
            Redirect::to("/", array("success" => "Kirjauduttu sisään!"));
        } else {
            //Väärä käyttäjätunnus tai salasana
            Redirect::to("/login", array("errors" => array("Väärä käyttäjätunnus tai salasana")));
        }
    }

    public static function handleLogout() {
        $_SESSION['user'] = null;
        Redirect::to('/login', array('success' => 'Olet kirjautunut ulos!'));
    }

}
