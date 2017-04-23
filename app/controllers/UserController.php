<?php

use \Alehuo\Time2 as Time2;
use \Alehuo\Date2 as Date2;
use \Alehuo\Course as Course;
use \Alehuo\Timetable as Timetable;
use \Alehuo\WeekDay as WeekDay;
use \Alehuo\Color as Color;

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
        if (!$user) {
            Redirect::to('/', array("errors" => array("Käyttäjää ei löydy!")));
        }
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

    public static function renderTimetable() {
        $day = date('N') - 1;
        $week_start = date('j-n-Y', strtotime('-' . $day . ' days'));
        $week_end = date('j-n-Y', strtotime('+' . (6 - $day) . ' days'));

        $startingString = explode("-", $week_start);
        $endingString = explode("-", $week_end);

        $timetable = new Timetable("Lukujärjestys tälle viikolle");
        $timetable->setStartingDate(new Date2($startingString[0], $startingString[1], $startingString[2]));
        $timetable->setEndingDate(new Date2($endingString[0], $endingString[1], $endingString[2]));

        $user = self::get_user_logged_in();

        $kurssiIlmot = KurssiIlmoittautuminen::findByUserAndBetweenDates($user->id, strtotime($week_start), strtotime($week_end));


        foreach ($kurssiIlmot as $kurssiIlmo) {
            $harjRyhmaIlmo = HarjoitusRyhmaIlmoittautuminen::findByUserAndCourse($user->id, $kurssiIlmo->kurssiId);
            if ($harjRyhmaIlmo) {
                $kurssiIlmo->harjoitusryhma = $harjRyhmaIlmo;
            }
            $course = Kurssi::find($kurssiIlmo->kurssiId);
            $course->opetusajat = Opetusaika::findByKurssiIdAndTyyppi($kurssiIlmo->kurssiId, 0);


            foreach ($course->opetusajat as $opetusaika) {
                $kurssi = new Course($course->nimi);
                $viikonpaiva = $opetusaika->viikonpaiva + 1;
                $start_time = new Time2((int) floor($opetusaika->aloitusAika / 60), $opetusaika->aloitusAika % 60);
                $end_time = new Time2((int) floor($opetusaika->lopetusAika / 60), $opetusaika->lopetusAika % 60);
                $kurssi->addLecture($viikonpaiva, $start_time, $end_time);
                $kurssi->setClassroom($opetusaika->huone);
                $timetable->addCourse($kurssi);
            }
            if ($kurssiIlmo->harjoitusryhma) {
                $kurssi = new Course("Harjoitusryhmä");
                $harjRyhmat = Opetusaika::findByKurssiIdAndTyyppi($course->id, 1);
                foreach ($harjRyhmat as $harjRyhma) {
//                    if ($harjRyhma->id == $harjRyhmaIlmo->id) {
                        $viikonpaiva = $harjRyhma->viikonpaiva + 1;
                        $start_time = new Time2((int) floor($harjRyhma->aloitusAika / 60), $harjRyhma->aloitusAika % 60);
                        $end_time = new Time2((int) floor($harjRyhma->lopetusAika / 60), $harjRyhma->lopetusAika % 60);
                        $kurssi->addLecture($harjRyhma->viikonpaiva, $start_time, $end_time);
                        $kurssi->setClassroom($harjRyhma->huone);
                        $timetable->addCourse($kurssi);
                        break;
//                    }
                }
            }
        }
        return $timetable->render(true);
    }

}
