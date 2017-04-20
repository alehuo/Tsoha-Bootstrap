<?php

class UserController extends BaseController {

    public static function viewAddUserPage() {
        
    }

    public static function addUser() {
        $errors = array();
        $params = $_POST;

        $user = new Kayttaja(array(
            "tyyppi" => intval($params["type"]),
            "nimi" => $params["username"],
            "salasana" => $params["password"]
        ));

        if ($params["password"] != $params["repeatPassword"]) {
            $errors[] = "Salasanat eivät täsmää!";
        }

        $usr = Kayttaja::findByUsername($params["username"]);

        if (!$usr) {
            $errors = array_merge($errors, $user->errors());
        } else {
            $errors[] = "Käyttäjätunnus on jo käytössä!";
        }

        if (!$errors) {
            $user->save();
            Redirect::to('/listusers', array("success" => "Käyttäjä lisättiin onnistuneesti."));
        } else {
            Redirect::to('/adduser', array("errors" => $errors, "form" => $params));
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

    public static function listAllUsers() {
        $users = Kayttaja::fetchAll();
        View::make("listusers.html", array("kayttajat" => $users));
    }

    public static function deleteUser($id) {
        $user = Kayttaja::find($id);
        $user->destroy();
        Redirect::to('/listusers', array("success" => "Käyttäjä poistettu onnistuneesti."));
    }

    public static function editUserPage($id) {
        $user = Kayttaja::find($id);
        View::make("edituser.html", array("form" => array(
                "username" => $user->nimi,
//                "password" => $user->salasana,
//                "repeatPassword" => $user->salasana,
                "type" => $user->tyyppi
            ), "id" => $id));
    }

    public static function handleEditUser($id) {
        $params = $_POST;
        $user = Kayttaja::find($id);
        if ($user) {
            $user->nimi = $params["username"];
//            $user->salasana = Kayttaja::createPassword($params["password"]);
            $user->tyyppi = $params["type"];
            $errors = $user->errors();
            if (!$errors) {
                $user->update();
                Redirect::to('/edituser/' . $id, array("form" => $params, "success" => "Käyttäjätilin muokkaus onnistui."));
            } else {
                Redirect::to('/edituser/' . $id, array("form" => $params, "errors" => $errors));
            }
        }
    }

    public static function viewGrades() {
        $user = self::get_user_logged_in();
        $suoritukset = KurssiSuoritus::findByUser($user->id);
        foreach ($suoritukset as $suoritus) {
            $suoritus->vastuuyksikko = Vastuuyksikko::find($suoritus->vastuuyksikko);
            $suoritus->kurssi = Kurssi::find($suoritus->kurssiId);
        }
        $facultys = Vastuuyksikko::all();
        View::make('grades.html', array("grades" => $suoritukset, "facultys" => $facultys));
    }

}
