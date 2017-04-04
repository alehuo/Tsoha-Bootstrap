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

        $ilmo = null;

        if (isset($_SESSION["user"])) {
            $user = Kayttaja::find($_SESSION["user"]);
            $ilmo = KurssiIlmoittautuminen::findByUserAndCourse($user->id, $course->id);
        }

        View::make('course.html', array("course" => $course, "opetusajat" => $opetusajat, "harjoitusryhmat" => $harjoitusryhmat, "ilmo" => $ilmo));
    }

    public static function addCourseForm() {
        $ajat = luoAjat();

        $vastuuyksikot = Vastuuyksikko::all();

        View::make('addcourse.html', array("ajat" => $ajat, "vastuuyksikot" => $vastuuyksikot));
    }

    public static function luoAjat() {
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
        return $ajat;
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

            if (isset($p["arvosteluTyyppi"])) {
                $arvostelu = 1;
            } else {
                $arvostelu = 0;
            }


            $kuvaus = $postData["kuvaus"];

            $kurssi = new Kurssi(array(
                'nimi' => $kurssin_nimi,
                'kuvaus' => $kuvaus,
                'opintoPisteet' => $op,
                'arvosteluTyyppi' => $arvostelu,
                'aloitusPvm' => $alkamisPvm,
                'lopetusPvm' => $lopetusPvm,
                'vastuuYksikkoId' => $vastuuyksikko
            ));

            $errors = array_merge($errors, $kurssi->errors());

            if (!$errors) {
                $kurssi->save();
            } else {
                //Uudelleenohjaa kurssisivulle virheiden kera
                Redirect::to('/addcourse', array("errors" => $errors));
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
        $opetusajat = Opetusaika::findByKurssiIdAndTyyppi($course->id, 0);
        $harjoitusryhmat = Opetusaika::findByKurssiIdAndTyyppi($course->id, 1);
        $course->opetusajat = $opetusajat;
        $course->harjoitusryhmat = $harjoitusryhmat;

        $ajat = CourseController::luoAjat();

        $vastuuyksikot = Vastuuyksikko::all();

        if ($course) {
            View::make('editcourse.html', array("course" => $course, "vastuuyksikot" => $vastuuyksikot, "ajat" => $ajat));
            exit();
        }
        Redirect::to('/', array("errors" => array("Kurssia ei löydy")));
    }

    public static function listParticipants($courseId) {
        $kurssiIlmot = KurssiIlmoittautuminen::findByCourse($courseId);
        //Lisää mukaan harjoitusryhmä ja käyttäjä
        foreach ($kurssiIlmot as $kurssiIlmo) {
            $hji = HarjoitusRyhmaIlmoittautuminen::find($kurssiIlmo->id);
            $hji->opetusaika = Opetusaika::find($hji->opetusaikaId);

            $kurssiIlmo->harjoitusRyhma = $hji;
            $kurssiIlmo->kayttaja = Kayttaja::find($kurssiIlmo->kayttajaId);
        }

        View::make('listparticipants.html', array("ilmot" => $kurssiIlmot));
    }

    public static function handleCourseEdit($id) {
        $params = $_POST;
        $errors = array();

        $kurssinNimi = $params["nimi"];
        $alkamisPaivays = $params["startingDate"];
        $loppumisPaivays = $params["endingDate"];
        $op = $params["op"];

        if (isset($p["arvosteluTyyppi"])) {
            $arvostelu = 1;
        } else {
            $arvostelu = 0;
        }


        $kurssi = Kurssi::find($id);
        $kurssi->nimi = $kurssinNimi;
        $kurssi->aloitusPvm = strtotime($alkamisPaivays);
        $kurssi->lopetusPvm = strtotime($loppumisPaivays);
        $kurssi->opintoPisteet = intval($op);
        $kurssi->arvosteluTyyppi = $arvostelu;

        $errors = array_merge($errors, $kurssi->errors());

        $opetusaikaIdt = $params["opetusaikaId"];
        $opetusaikaHuoneet = $params["opetusaikaHuone"];
        $opetusaikaAloitusajat = $params["opetusaikaAloitusaika"];
        $opetusaikaKestot = $params["opetusaikaKesto"];
        $opetusaikaViikonpaivat = $params["opetusaikaViikonpaiva"];

        $opetusaikojenMaara = count($opetusaikaIdt);

        $opetusajat = array();


        //Alkuperäiset
        for ($i = 0; $i < $opetusaikojenMaara; $i++) {
            $id = $opetusaikaIdt[$i];
            $huone = $opetusaikaHuoneet[$i];
            $aloitusAika = $opetusaikaAloitusajat[$i];
            $kesto = $opetusaikaKestot[$i];
            $viikonpaiva = $opetusaikaViikonpaivat[$i];

            $opetusaika = Opetusaika::find($id);
            $opetusaika->huone = $huone;
            $opetusaika->aloitusAika = intval($aloitusAika);
            $opetusaika->lopetusAika = intval($aloitusAika) + 60 * intval($kesto);
            $opetusaika->viikonpaiva = $viikonpaiva;

            $errors = array_merge($errors, $opetusaika->errors());

            $opetusajat[] = $opetusaika;
        }

        $uudetopetusajat = array();

        $loput = count($opetusaikaViikonpaivat) - $opetusaikojenMaara;

        //Loput (eli uudet)
        for ($i = $opetusaikojenMaara; $i < $loput; $i++) {
            $huone = $opetusaikaHuoneet[$i];
            $aloitusAika = $opetusaikaAloitusajat[$i];
            $kesto = $opetusaikaKestot[$i];
            $viikonpaiva = $opetusaikaViikonpaivat[$i];

            $opetusaika = new Opetusaika(array(
                "huone" => $huone,
                "aloitusAika" => $aloitusAika,
                "lopetusAika" => intval($aloitusAika) + 60 * intval($kesto),
                "viikonpaiva" => $viikonpaiva,
                "tyyppi" => 0
            ));

            $errors = array_merge($errors, $opetusaika->errors());

            $uudetopetusajat[] = $opetusaika;
        }

        $harjoitusryhmaIdt = $params["harjoitusryhmaId"];
        $harjoitusryhmaHuoneet = $params["harjoitusryhmaHuone"];
        $harjoitusryhmaAloitusajat = $params["harjoitusryhmaAloitusaika"];
        $harjoitusryhmaKestot = $params["harjoitusryhmaKesto"];
        $harjoitusryhmaViikonpaivat = $params["harjoitusryhmaViikonpaiva"];

        $harjoitusryhmienMaara = count($harjoitusryhmaIdt);

        $harjoitusryhmat = array();

        for ($i = 0; $i < $harjoitusryhmienMaara; $i++) {
            $id = $harjoitusryhmaIdt[$i];
            $huone = $harjoitusryhmaHuoneet[$i];
            $aloitusAika = $harjoitusryhmaAloitusajat[$i];
            $kesto = $harjoitusryhmaKestot[$i];
            $viikonpaiva = $harjoitusryhmaViikonpaivat[$i];

            $harjoitusryhma = new Opetusaika(array(
                "id" => $id,
                "huone" => $huone,
                "aloitusAika" => $aloitusAika,
                "lopetusAIka" => intval($aloitusAika) + 60 * intval($kesto),
                "viikonpaiva" => $viikonpaiva,
                "tyyppi" => 1
            ));

            $errors = array_merge($errors, $harjoitusryhma->errors());

            $harjoitusryhmat[] = $harjoitusryhma;
        }

        $uudetharjoitusryhmat = array();

        $loput = count($harjoitusryhmaViikonpaivat) - $harjoitusryhmienMaara;

        //Loput (eli uudet)
        for ($i = $harjoitusryhmienMaara; $i < $loput; $i++) {
            $huone = $harjoitusryhmaHuoneet[$i];
            $aloitusAika = $harjoitusryhmaAloitusajat[$i];
            $kesto = $harjoitusryhmaKestot[$i];
            $viikonpaiva = $harjoitusryhmaViikonpaivat[$i];

            $harjoitusryhma = new Opetusaika(array(
                "huone" => $huone,
                "aloitusAika" => $aloitusAika,
                "lopetusAIka" => intval($aloitusAika) + 60 * intval($kesto),
                "viikonpaiva" => $viikonpaiva,
                "tyyppi" => 1
            ));

            $errors = array_merge($errors, $harjoitusryhma->errors());

            $uudetharjoitusryhmat[] = $harjoitusryhma;
        }

        if (!$errors) {
            $kurssi->update();
            foreach ($opetusajat as $opetusaika) {
                $opetusaika->update();
            }
            foreach ($uudetopetusajat as $uusiopetussaika) {
                $opetusaika->save();
            }
            foreach ($harjoitusryhmat as $harjoitusryhma) {
                $harjoitusryhma->update();
            }
            foreach ($uudetharjoitusryhmat as $harjoitusryhma) {
                $harjoitusryhma->save();
            }
        }
    }

}
