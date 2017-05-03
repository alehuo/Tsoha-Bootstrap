<?php

use \Alehuo\Time2 as Time2;
use \Alehuo\Date2 as Date2;
use \Alehuo\Course as Course;
use \Alehuo\Timetable as Timetable;
use \Alehuo\WeekDay as WeekDay;
use \Alehuo\Color as Color;

class UserController extends BaseController {

    /**
     * Lisää käyttäjä
     */
    public static function addUser() {
        $errors = array();
        $params = $_POST;

        $user = new Kayttaja(array(
            "tyyppi" => intval($params["type"]),
            "nimi" => trim($params["username"]),
            "salasana" => $params["password"]
        ));

        if (!in_array($user->tyyppi, range(0, 1))) {
            $errors[] = "Virheellinen käyttäjätilin tyyppi!";
        }

        if ($params["password"] != $params["repeatPassword"]) {
            $errors[] = "Salasanat eivät täsmää!";
        }

        $usr = Kayttaja::findByUsername(strtolower(trim($params["username"])));

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

    /**
     * Sisäänkirjautuminen
     */
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

    /**
     * Uloskirjautuminen
     */
    public static function handleLogout() {
        $_SESSION['user'] = null;
        Redirect::to('/login', array('success' => 'Olet kirjautunut ulos!'));
    }

    /**
     * Kaikkien käyttäjien listaaminen
     */
    public static function listAllUsers() {
        $users = Kayttaja::fetchAll();
        View::make("listusers.html", array("kayttajat" => $users));
    }

    /**
     * Käyttäjätilin poisto
     * @param type $id
     */
    public static function deleteUser($id) {
        $id = intval($id);
        $user = Kayttaja::find($id);
        if ($user && $user->id == 1) {
            Redirect::to('/listusers', array("errors" => array("Pääkäyttäjätiliä ei voi poistaa!")));
            exit();
        }
        if (!$user) {
            Redirect::to('/listusers', array("errors" => array("Käyttäjää ei löydy!")));
            exit();
        }
        $user->destroy();
        Redirect::to('/listusers', array("success" => "Käyttäjä poistettu onnistuneesti."));
    }

    /**
     * Käyttäjätilin muokkaus
     * @param int $id
     */
    public static function editUserPage($id) {
        $id = intval($id);
        $user = Kayttaja::find($id);
        if (!$user) {
            Redirect::to('/listusers', array("errors" => array("Käyttäjää ei löydy!")));
            exit();
        }
        View::make("edituser.html", array("form" => array(
                "username" => $user->nimi,
//                "password" => $user->salasana,
//                "repeatPassword" => $user->salasana,
                "type" => $user->tyyppi
            ), "id" => $id));
    }

    /**
     * Käyttäjätilin muokkauksen käsittely
     * @param int $id Käyttäjätilin id
     */
    public static function handleEditUser($id) {
        $errors = array();
        $params = $_POST;
        $user = Kayttaja::find($id);
        if ($user) {
            $uusinimi = $params["username"];

            if (Kayttaja::userExists(strtolower($uusinimi)) && strtolower(trim($uusinimi)) != strtolower(trim($user->nimi))) {
                $errors[] = "Käyttäjänimi on jo käytössä!";
            } else {
                $user->nimi = $uusinimi;
            }

//            $user->salasana = Kayttaja::createPassword($params["password"]);
            $user->tyyppi = intval($params["type"]);
            if (!in_array($user->tyyppi, range(0, 1))) {
                $errors[] = "Virheellinen käyttäjätilin tyyppi!";
            }



            $errors = array_merge($errors, $user->errors());
            if (!$errors) {
                $user->update();
                Redirect::to('/edituser/' . $id, array("form" => $params, "success" => "Käyttäjätilin muokkaus onnistui."));
            } else {
                Redirect::to('/edituser/' . $id, array("form" => $params, "errors" => $errors));
            }
        }
    }

    /**
     * Arvosanojen tarkastelu
     */
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

    /**
     * Piirrä lukujärjestys
     * @return Lukujärjestys
     */
    public static function renderTimetable() {

        $user = self::get_user_logged_in();

        if ($user) {

            $day = date('N') - 1;
            $week_start = date('j-n-Y', strtotime('-' . $day . ' days'));
            $week_end = date('j-n-Y', strtotime('+' . (6 - $day) . ' days'));

            $startingString = explode("-", $week_start);
            $endingString = explode("-", $week_end);

            $timetable = new Timetable("Lukujärjestys tälle viikolle");
            $timetable->setStartingDate(new Date2($startingString[0], $startingString[1], $startingString[2]));
            $timetable->setEndingDate(new Date2($endingString[0], $endingString[1], $endingString[2]));
            $timetable->setEndingTime(new Time2(23, 00));



            $kurssiIlmot = KurssiIlmoittautuminen::findByUserAndBetweenDates($user->id, strtotime($week_start), strtotime($week_end));


            //Käydään kurssi-ilmot yksitellen läpi
            foreach ($kurssiIlmot as $kurssiIlmo) {

                $course = Kurssi::find($kurssiIlmo->kurssiId);

                //Harjoitusryhmäilmoittautuminen
                $harjRyhmaIlmo = HarjoitusRyhmaIlmoittautuminen::findByUserAndCourse($user->id, $kurssiIlmo->kurssiId);

                if ($harjRyhmaIlmo) {
                    $kurssiIlmo->harjoitusryhma = $harjRyhmaIlmo;
                    $harjoitusRyhmaOpetusaika = Opetusaika::findByHarjoitusRyhmaIlmo($harjRyhmaIlmo->id);

                    if ($harjoitusRyhmaOpetusaika) {
                        $harjRyhma = new Course("Harjoitusryhmä (" . $course->nimi . ")");
                        $harjRyhma->setColor($timetable->nextColor());
                        $huone = $harjoitusRyhmaOpetusaika->huone;
                        $harjRyhma->setClassroom($huone);
                        $viikonpaiva = $harjoitusRyhmaOpetusaika->viikonpaiva + 1;
                        $start_time = new Time2((int) floor($harjoitusRyhmaOpetusaika->aloitusAika / 60), $harjoitusRyhmaOpetusaika->aloitusAika % 60);
                        $end_time = new Time2((int) floor($harjoitusRyhmaOpetusaika->lopetusAika / 60), $harjoitusRyhmaOpetusaika->lopetusAika % 60);
                        $harjRyhma->addLecture($viikonpaiva, $start_time, $end_time);

                        //Lisää opetusaika lukujärjestykseen
                        $timetable->addCourse($harjRyhma);
                    }
                }


                $course->opetusajat = Opetusaika::findByKurssiIdAndTyyppi($kurssiIlmo->kurssiId, 0);
                $kurssi = new Course($course->nimi);
                $kurssi->setColor($timetable->nextColor());
                foreach ($course->opetusajat as $opetusaika) {
                    $huone = $opetusaika->huone;
                    $kurssi->setClassroom($huone);
                    $viikonpaiva = $opetusaika->viikonpaiva + 1;
                    $start_time = new Time2((int) floor($opetusaika->aloitusAika / 60), $opetusaika->aloitusAika % 60);
                    $end_time = new Time2((int) floor($opetusaika->lopetusAika / 60), $opetusaika->lopetusAika % 60);
                    $kurssi->addLecture($viikonpaiva, $start_time, $end_time);

                    //Lisää opetusaika lukujärjestykseen
                    $timetable->addCourse($kurssi);
                }
            }
            return $timetable->render(true);
        }
    }

}
