<?php

class CourseController extends BaseController {

    public static function searchPage() {
        View::make("courses.html");
    }

    public static function search() {
        $params = $_POST;
        $hakusana = $params['hakusana'];

        $courses = Kurssi::findAllByHakusana('%' . $hakusana . '%');

        View::make('courses.html', array("courses" => $courses, "lkm" => count($courses), "searchTerm" => $hakusana));
    }

    public static function viewCourse($id) {
        $course = Kurssi::find($id);
        $opetusajat = Opetusaika::findByKurssiIdAndTyyppi($id, '0');
        $harjoitusryhmat = Opetusaika::findByKurssiIdAndTyyppi($id, '1');

        View::make('course.html', array("course" => $course, "opetusajat" => $opetusajat, "harjoitusryhmat" => $harjoitusryhmat));
    }

    public static function addCourseForm() {
        $ajat = array();
        $hours = 7;
        $minutes = 0;
        $start = 7 * 60;
        for ($i = 0; $i < 65; $i++) {
            $val = str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0", STR_PAD_LEFT);

            $ajat[] = array("id" => $start, "arvo" => $val);

            if ($minutes == 45) {
                $hours++;
                $minutes = 0;
            } else {
                $minutes += 15;
            }

            $start += 15;
        }

        $vastuuyksikot = Vastuuyksikko::all();

        View::make('addcourse.html', array("ajat" => $ajat, "vastuuyksikot" => $vastuuyksikot));
    }

    /**
     * Kurssin lisäys lomakkeen kautta.
     * 
     */
    public static function addCourse() {
        $db = DB::connection();
        $db->beginTransaction();
        $errors = array();
        try {
            $postData = $_POST;

            $kurssin_nimi = $postData["nimi"];

            if (empty($postData["uusiVastuuYksikko"])) {
                $vastuuyksikko = $postData["vastuuyksikkoSelect"];
            } else {
                //Luo uusi vastuuyksikkö ja palauta id
                $uusiVastuuyksikko = new Vastuuyksikko(array("nimi" => $postData["uusiVastuuYksikko"]));
                $errors = array_merge($errors, $uusiVastuuyksikko->errors());
                $uusiVastuuyksikko->save();
                $vastuuyksikko = $uusiVastuuyksikko->id;
            }

            $alkamisPvm = strtotime($postData["startingDate"]);

            $lopetusPvm = strtotime($postData["endingDate"]);

            $op = $postData["op"];

//        if (isset($p["arvosteluTyyppi"])) {
//            $arvostelu = $p["arvosteluTyyppi"];
//        }


            $kuvaus = $postData["kuvaus"];

            $kurssi = new Kurssi(array(
                'nimi' => $kurssin_nimi,
                'kuvaus' => $kuvaus,
                'opintoPisteet' => $op,
                'aloitusPvm' => $alkamisPvm,
                'lopetusPvm' => $lopetusPvm,
                'vastuuYksikkoId' => $vastuuyksikko
            ));

            $courseErrors = $kurssi->errors();

            if (!$courseErrors) {
                $kurssi->save();
            } else {
                //Uudelleenohjaa kurssisivulle virheiden kera
                Redirect::to('/addcourse', array("errors" => $courseErrors));
                exit();
            }


            $ajat = array();

            for ($i = 1; $i < count($postData["opetusaikaAloitusaika"]); $i++) {

                $loppuaika = intval($postData["opetusaikaAloitusaika"][$i]) + 60 * intval($postData["opetusaikaKesto"][$i]);

                $opetusaika = new Opetusaika(array(
                    'huone' => $postData['opetusaikaHuone'][$i],
                    'viikonpaiva' => intval($postData["opetusaikaViikonpaiva"][$i]),
                    'aloitusAika' => intval($postData["opetusaikaAloitusaika"][$i]),
                    'lopetusAika' => $loppuaika,
                    'kurssiId' => $kurssi->id,
                    'tyyppi' => 0
                ));

                $ajat[] = $opetusaika;
            }

            for ($i = 1; $i < count($postData["harjoitusryhmaAloitusaika"]); $i++) {
                $loppuaika = intval($postData["harjoitusryhmaAloitusaika"][$i]) + 60 * intval($postData["harjoitusryhmaKesto"][$i]);

                $harjoitusryhma = new Opetusaika(array(
                    'huone' => $postData['harjoitusryhmaHuone'][$i],
                    'viikonpaiva' => intval($postData["harjoitusryhmaViikonpaiva"][$i]),
                    'aloitusAika' => intval($postData["harjoitusryhmaAloitusaika"][$i]),
                    'lopetusAika' => $loppuaika,
                    'kurssiId' => $kurssi->id,
                    'tyyppi' => 1
                ));

                $ajat[] = $harjoitusryhma;
            }


            foreach ($ajat as $key => $opetusaika) {
                $errors = array_merge($errors, $opetusaika->errors());
                $opetusaika->save();
            }

            if (!$errors) {
                $db->commit();
                Redirect::to("/", array("courseAdded" => "Kurssi lisätty onnistuneesti"));
                exit();
            } else {
                //Rollback ja vie takaisin lisäyssivuille virheiden kera
                $db->rollBack();
                Redirect::to('/addcourse', array("errors" => $errors));
                exit();
            }
        } catch (PDOException $ex) {
            $db->rollBack();
        }
    }

    public static function addGrade(KurssiIlmoittautuminen $ilmo) {
        $params = $_POST;
        $user = Kayttaja::find($ilmo->kayttajaId);
        $kurssi = Kurssi::find($ilmo->kurssiId);

        $suoritus = new KurssiSuoritus(array("kurssiId" => $kurssi->id, "kayttajaId" => $user->id, "arvosana" => intval($params["arvosana"]), "paivays" => BaseController::get_current_timestamp()));
        $errors = $suoritus->errors();
        if (!$errors) {
            $suoritus->save();
            Redirect::to('/', array("success" => "Arvioinnin lisäys onnistui."));
        } else {
            Redirect::to('/addgrade/' . $ilmo->id, array("errors" => $errors));
        }
    }

    public static function editCourse($id) {
        $course = Kurssi::find($id);
        $vastuuyksikot = Vastuuyksikko::all();
        if ($course) {
            View::make('editcourse.html', array("course" => $course, "vastuuyksikot" => $vastuuyksikot, "selected" => $course->arvosteluTyyppi));
            exit();
        }
        Redirect::to('/', array("errors" => array("Kurssia ei löydy")));
    }

}
