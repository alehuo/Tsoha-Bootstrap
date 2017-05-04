<?php

class CourseController extends BaseController {

    /**
     * Hakusivu
     */
    public static function searchPage() {
        View::make("courses.html");
    }

    /**
     * Haku
     */
    public static function search() {
        $params = $_POST;
        $hakusana = $params['hakusana'];

        if (strlen(trim($hakusana)) == 3 && trim($hakusana) === '*.*') {
            $courses = Kurssi::fetchAll();
        } else {
            $courses = Kurssi::findAllByHakusana('%' . $hakusana . '%');
        }

        View::make('courses.html', array("courses" => $courses, "lkm" => count($courses), "searchTerm" => $hakusana));
    }

    /**
     * Hakutulokset JSON-muodossa
     */
    public static function searchResults() {
        Header("Content-type: application/json");
        $params = $_POST;
        $searchTerm = $params['searchTerm'];
        $results = array();
        if (strlen(trim($searchTerm)) == 3 && trim($searchTerm) === '*.*') {
            $res = Kurssi::fetchAll();
        } else {
            $res = Kurssi::findAllByHakusana('%' . strtolower(trim($searchTerm)) . '%');
        }
        foreach ($res as $result) {
            $results[] = array(
                "id" => $result->id,
                "nimi" => $result->nimi,
                "vastuuyksikko" => Vastuuyksikko::find($result->vastuuYksikkoId)->nimi,
                "aloituspvm" => $result->aloitusPvm,
                "lopetuspvm" => $result->lopetusPvm,
                "nopat" => $result->opintoPisteet
            );
        }
        echo json_encode($results);
    }

    /**
     * Näytä kurssi
     * @param int $id Kurssin id
     */
    public static function viewCourse($id) {
        $id = intval($id);
        $course = Kurssi::find($id);
        if (!$course) {
            View::make("blank.html", array("errors" => array("Kurssia ei löydy!")));
        }
        $opetusajat = Opetusaika::findByKurssiIdAndTyyppi($id, '0');
        $harjoitusryhmat = Opetusaika::findByKurssiIdAndTyyppi($id, '1');

        $ilmo = null;

        $user = self::get_user_logged_in();
        if ($user) {
            $ilmo = KurssiIlmoittautuminen::findByUserAndCourse($user->id, $course->id);
        }
        if ($ilmo) {
            $harjIlmo = HarjoitusRyhmaIlmoittautuminen::find($ilmo->id);
            if ($harjIlmo) {
                $opaika = Opetusaika::find($harjIlmo->opetusaikaId);
                $harjIlmo->opetusaika = $opaika;
            }
            $ilmo->harjoitusryhma = $harjIlmo;
        }

        View::make('course.html', array("course" => $course, "opetusajat" => $opetusajat, "harjoitusryhmat" => $harjoitusryhmat, "ilmo" => $ilmo));
    }

    /**
     * Renderöi kurssin lisäyslomake
     */
    public static function addCourseForm() {
        $ajat = CourseController::luoAjat();

        $vastuuyksikot = Vastuuyksikko::all();

        View::make('addcourse.html', array("ajat" => $ajat, "vastuuyksikot" => $vastuuyksikot));
    }

    /**
     * Luo ajat
     * @return int
     */
    public static function luoAjat() {
        $ajat = array();
        $hours = 8;
        $minutes = 0;
        $start = $hours * 60;
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

        $postData = $_POST;

        $ajat = array();

        for ($i = 1; $i < count($postData["opetusaikaAloitusaika"]); $i++) {

            $loppuaika = intval($postData["opetusaikaAloitusaika"][$i]) + 60 * intval($postData["opetusaikaKesto"][$i]);

            $opetusaika = new Opetusaika(array(
                'huone' => $postData['opetusaikaHuone'][$i],
                'viikonpaiva' => intval($postData["opetusaikaViikonpaiva"][$i]),
                'aloitusAika' => intval($postData["opetusaikaAloitusaika"][$i]),
                'lopetusAika' => $loppuaika,
                'kurssiId' => -1,
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
                'kurssiId' => -1,
                'tyyppi' => 1
            ));

            $ajat[] = $harjoitusryhma;
        }

        try {

            $kurssin_nimi = $postData["nimi"];

            if (empty($postData["uusiVastuuYksikko"])) {
                $vastuuyksikko = $postData["vastuuyksikkoSelect"];
                $vy = Vastuuyksikko::find($vastuuyksikko);

                if (!$vy) {
                    $errors = array_merge($errors, array("Sinun on valittava vastuuyksikkö listasta tai luotava uusi."));
                }
            } else {
                //Luo uusi vastuuyksikkö
                $uusiVastuuyksikko = new Vastuuyksikko(array("nimi" => $postData["uusiVastuuYksikko"]));
                $errors = array_merge($errors, $uusiVastuuyksikko->errors());
            }



            $alkamisPvm = strtotime($postData["startingDate"]);

            $lopetusPvm = strtotime($postData["endingDate"]);

            $op = $postData["op"];

            if (isset($postData["arvosteluTyyppi"])) {
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
                'vastuuYksikkoId' => -1
            ));

            $errors = array_merge($errors, $kurssi->errors());


            foreach ($ajat as $opetusaika) {
                $errors = array_merge($errors, $opetusaika->errors());
            }


            if (!$errors) {
                if (isset($uusiVastuuyksikko)) {
                    $uusiVastuuyksikko->save();
                    $vastuuyksikko = $uusiVastuuyksikko->id;
                }
                $kurssi->vastuuYksikkoId = intval($vastuuyksikko);
                $kurssi->save();
                //Tallenna opetusajat
                foreach ($ajat as $opetusaika) {
                    $opetusaika->kurssiId = $kurssi->id;
                    $opetusaika->save();
                }
                $db->commit();
                Redirect::to("/", array("success" => "Kurssi lisätty onnistuneesti"));
                exit();
            } else {
                //Rollback ja vie takaisin lisäyssivuille virheiden kera
                $db->rollBack();
                Redirect::to('/addcourse', array("params" => $postData, "errors" => $errors, "opetusajat" => $ajat));
                exit();
            }
        } catch (PDOException $ex) {
            $db->rollBack();
            Redirect::to('/addcourse', array("params" => $postData, "errors" => array("Kurssin lisäys epäonnistui!"), "opetusajat" => $ajat));
        }
    }

    /**
     * Lisää kurssisuoritus
     * @param KurssiIlmoittautuminen $ilmo Kurssi-ilmo
     */
    public static function addGrade(KurssiIlmoittautuminen $ilmo) {
        $params = $_POST;
        $user = Kayttaja::find($ilmo->kayttajaId);
        $kurssi = Kurssi::find($ilmo->kurssiId);
        $ilmo = KurssiIlmoittautuminen::findByUserAndCourse($user->id, $kurssi->id);
        $harjIlmo = HarjoitusRyhmaIlmoittautuminen::findByUserAndCourse($user->id, $kurssi->id);
        $errors = array();

        $suoritus = new KurssiSuoritus(array("kurssiId" => $kurssi->id, "kayttajaId" => $user->id, "arvosana" => $params["arvosana"], "paivays" => BaseController::get_current_timestamp()));
        $errors = array_merge($errors, $suoritus->errors());
        if (!$errors) {
            $suoritus->save();
            if ($harjIlmo) {
                $harjIlmo->destroy();
            }
            $ilmo->destroy();
            Redirect::to('/course/' . $kurssi->id, array("success" => "Arvioinnin lisäys onnistui."));
        } else {
            Redirect::to('/addgrade/' . $ilmo->id, array("errors" => $errors));
        }
    }

    /**
     * Kurssin muokkaus
     * @param int $id Kurssin id
     */
    public static function editCourse($id) {
        $id = intval($id);
        $course = Kurssi::find($id);
        if (!$course) {
            View::make('blank.html', array("errors" => array("Kurssia ei löydy!")));
        }
        $opetusajat = Opetusaika::findByKurssiIdAndTyyppi($course->id, 0);
        $harjoitusryhmat = Opetusaika::findByKurssiIdAndTyyppi($course->id, 1);
        $course->opetusajat = $opetusajat;
        $course->harjoitusryhmat = $harjoitusryhmat;

        $ajat = CourseController::luoAjat();

        $vastuuyksikot = Vastuuyksikko::all();

        if ($course) {
            View::make('editcourse.html', array("course" => $course, "vastuuyksikot" => $vastuuyksikot, "ajat" => $ajat, "harjoitusryhmat" => $harjoitusryhmat, "opetusajat" => $opetusajat));
            exit();
        }
    }

    /**
     * Listaa osallistujat
     * @param int $courseId Kurssin id
     */
    public static function listParticipants($courseId) {
        $courseId = intval($courseId);
        $course = Kurssi::find($courseId);
        if (!$course) {
            View::make('blank.html', array("errors" => array("Kurssia ei löydy!")));
        }
        $kurssiIlmot = KurssiIlmoittautuminen::findByCourse($courseId);
        //Lisää mukaan harjoitusryhmä ja käyttäjä
        foreach ($kurssiIlmot as $kurssiIlmo) {
            $hji = HarjoitusRyhmaIlmoittautuminen::find($kurssiIlmo->id);
            if ($hji) {
                $hji->opetusaika = Opetusaika::find($hji->opetusaikaId);
            }


            $kurssiIlmo->harjoitusRyhma = $hji;
            $kurssiIlmo->kayttaja = Kayttaja::find($kurssiIlmo->kayttajaId);
        }

        View::make('listparticipants.html', array("ilmot" => $kurssiIlmot));
    }

    /**
     * Kurssin muokkaus
     * @param int $kurssiId Kurssin id
     */
    public static function handleCourseEdit($kurssiId) {


        $db = DB::connection();
        $db->beginTransaction();

        try {
            $params = $_POST;

            $errors = array();

            $idt = Opetusaika::haeIdt($kurssiId);

            $kurssinNimi = $params["nimi"];
            $vyId = intval($params["vastuuyksikkoSelect"]);
            $vy = Vastuuyksikko::find($vyId);
            if (!$vy) {
                $errors[] = "Vastuuyksikköä ei löydy kyseisellä ID:llä";
            }
            $alkamisPaivays = $params["startingDate"];
            $loppumisPaivays = $params["endingDate"];
            $op = $params["op"];

            if (isset($p["arvosteluTyyppi"])) {
                $arvostelu = 1;
            } else {
                $arvostelu = 0;
            }


            $kurssi = Kurssi::find($kurssiId);
            $kurssi->nimi = $kurssinNimi;
            $kurssi->aloitusPvm = strtotime($alkamisPaivays);
            $kurssi->lopetusPvm = strtotime($loppumisPaivays);
            $kurssi->opintoPisteet = intval($op);
            $kurssi->arvosteluTyyppi = $arvostelu;
            $kurssi->vastuuYksikkoId = $vyId;

            $errors = array_merge($errors, $kurssi->errors());



            $opetusajat = array();
            $uudetopetusajat = array();

            //Vanhat&uudet opetusajat
            if (isset($params["opetusaikaId"], $params["opetusaikaHuone"], $params["opetusaikaAloitusaika"], $params["opetusaikaKesto"], $params["opetusaikaViikonpaiva"])) {
                $opetusaikaIdt = $params["opetusaikaId"];
                $opetusaikaHuoneet = $params["opetusaikaHuone"];
                $opetusaikaAloitusajat = $params["opetusaikaAloitusaika"];
                $opetusaikaKestot = $params["opetusaikaKesto"];
                $opetusaikaViikonpaivat = $params["opetusaikaViikonpaiva"];


                $opetusaikojenMaara = count($opetusaikaIdt);

                //Alkuperäiset
                for ($i = 0; $i < $opetusaikojenMaara; $i++) {

                    $id = $opetusaikaIdt[$i];

                    //Poistetaan id kaikkien id:ien seasta, jotta voidaan tarkistaa onko jokin opetusaika poistettu
                    $idt = array_diff($idt, array($id));

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

                $loput = count($opetusaikaViikonpaivat);

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
                        "kurssiId" => $kurssiId,
                        "tyyppi" => 0
                    ));

                    $errors = array_merge($errors, $opetusaika->errors());

                    $uudetopetusajat[] = $opetusaika;
                }
            } else if (isset($params["opetusaikaHuone"], $params["opetusaikaAloitusaika"], $params["opetusaikaAloitusaika"], $params["opetusaikaKesto"], $params["opetusaikaViikonpaiva"])) {

                //Tähän uusien lisäys
                //Loput (eli uudet)
                $loput = count($params["opetusaikaHuone"]);

                $opetusaikaHuoneet = $params["opetusaikaHuone"];
                $opetusaikaAloitusajat = $params["opetusaikaAloitusaika"];
                $opetusaikaKestot = $params["opetusaikaKesto"];
                $opetusaikaViikonpaivat = $params["opetusaikaViikonpaiva"];

                for ($i = 0; $i < $loput; $i++) {
                    $huone = $opetusaikaHuoneet[$i];
                    $aloitusAika = $opetusaikaAloitusajat[$i];
                    $kesto = $opetusaikaKestot[$i];
                    $viikonpaiva = $opetusaikaViikonpaivat[$i];

                    $opetusaika = new Opetusaika(array(
                        "huone" => $huone,
                        "aloitusAika" => $aloitusAika,
                        "lopetusAika" => intval($aloitusAika) + 60 * intval($kesto),
                        "viikonpaiva" => $viikonpaiva,
                        "kurssiId" => $kurssiId,
                        "tyyppi" => 0
                    ));

                    $errors = array_merge($errors, $opetusaika->errors());

                    $uudetopetusajat[] = $opetusaika;
                }
            }


            $harjoitusryhmat = array();
            $uudetharjoitusryhmat = array();

            if (isset($params["harjoitusryhmaId"], $params["harjoitusryhmaHuone"], $params["harjoitusryhmaAloitusaika"], $params["harjoitusryhmaAloitusaika"], $params["harjoitusryhmaKesto"], $params["harjoitusryhmaViikonpaiva"])) {

                $harjoitusryhmaIdt = $params["harjoitusryhmaId"];
                $harjoitusryhmaHuoneet = $params["harjoitusryhmaHuone"];
                $harjoitusryhmaAloitusajat = $params["harjoitusryhmaAloitusaika"];
                $harjoitusryhmaKestot = $params["harjoitusryhmaKesto"];
                $harjoitusryhmaViikonpaivat = $params["harjoitusryhmaViikonpaiva"];

                $harjoitusryhmienMaara = count($harjoitusryhmaIdt);



                for ($i = 0; $i < $harjoitusryhmienMaara; $i++) {

                    $id = $harjoitusryhmaIdt[$i];

                    $idt = array_diff($idt, array($id));

                    $huone = $harjoitusryhmaHuoneet[$i];
                    $aloitusAika = $harjoitusryhmaAloitusajat[$i];
                    $kesto = $harjoitusryhmaKestot[$i];
                    $viikonpaiva = $harjoitusryhmaViikonpaivat[$i];

                    $harjoitusryhma = new Opetusaika(array(
                        "id" => $id,
                        "huone" => $huone,
                        "aloitusAika" => $aloitusAika,
                        "lopetusAika" => intval($aloitusAika) + 60 * intval($kesto),
                        "viikonpaiva" => $viikonpaiva,
                        "kurssiId" => $kurssiId,
                        "tyyppi" => 1
                    ));

                    $errors = array_merge($errors, $harjoitusryhma->errors());

                    $harjoitusryhmat[] = $harjoitusryhma;
                }

                $loput = count($harjoitusryhmaViikonpaivat);

                //Loput (eli uudet)
                for ($i = $harjoitusryhmienMaara; $i < $loput; $i++) {
                    $huone = $harjoitusryhmaHuoneet[$i];
                    $aloitusAika = $harjoitusryhmaAloitusajat[$i];
                    $kesto = $harjoitusryhmaKestot[$i];
                    $viikonpaiva = $harjoitusryhmaViikonpaivat[$i];

                    $harjoitusryhma = new Opetusaika(array(
                        "huone" => $huone,
                        "aloitusAika" => $aloitusAika,
                        "lopetusAika" => intval($aloitusAika) + 60 * intval($kesto),
                        "viikonpaiva" => $viikonpaiva,
                        "kurssiId" => $kurssiId,
                        "tyyppi" => 1
                    ));

                    $errors = array_merge($errors, $harjoitusryhma->errors());

                    $uudetharjoitusryhmat[] = $harjoitusryhma;
                }
            } else if (isset($params["harjoitusryhmaHuone"], $params["harjoitusryhmaAloitusaika"], $params["harjoitusryhmaAloitusaika"], $params["harjoitusryhmaKesto"], $params["harjoitusryhmaViikonpaiva"])) {

                $harjoitusryhmaHuoneet = $params["harjoitusryhmaHuone"];
                $harjoitusryhmaAloitusajat = $params["harjoitusryhmaAloitusaika"];
                $harjoitusryhmaKestot = $params["harjoitusryhmaKesto"];
                $harjoitusryhmaViikonpaivat = $params["harjoitusryhmaViikonpaiva"];

                $loput = count($params["harjoitusryhmaHuone"]);

                //Loput (eli uudet)
                for ($i = 0; $i < $loput; $i++) {
                    $huone = $harjoitusryhmaHuoneet[$i];
                    $aloitusAika = $harjoitusryhmaAloitusajat[$i];
                    $kesto = $harjoitusryhmaKestot[$i];
                    $viikonpaiva = $harjoitusryhmaViikonpaivat[$i];

                    $harjoitusryhma = new Opetusaika(array(
                        "huone" => $huone,
                        "aloitusAika" => $aloitusAika,
                        "lopetusAika" => intval($aloitusAika) + 60 * intval($kesto),
                        "viikonpaiva" => $viikonpaiva,
                        "kurssiId" => $kurssiId,
                        "tyyppi" => 1
                    ));

                    $errors = array_merge($errors, $harjoitusryhma->errors());

                    $uudetharjoitusryhmat[] = $harjoitusryhma;
                }
            }

            //Poistetaan poistettavaksi merkityt opetus- ja harjoitusryhmät (Samalla poistuvat ilmoittautumiset harjoitusryhmiin)
            if (!empty($idt)) {
                foreach ($idt as $opetusaikaId) {
                    $opetusaika = Opetusaika::find($opetusaikaId);
                    $kurssiIlmot = KurssiIlmoittautuminen::findByOpetusaikaId($opetusaika->id);
                    foreach ($kurssiIlmot as $kurssiIlmo) {
                        $harjoitusryhmaIlmo = HarjoitusRyhmaIlmoittautuminen::findByUserAndCourse($kurssiIlmo->kayttajaId, $kurssiIlmo->kurssiId);
                        $harjoitusryhmaIlmo->destroy();

                        $kurssiIlmo->destroy();
                    }
                    $opetusaika->destroy();
                }
            }

            if (!$errors) {
                $kurssi->update();
                foreach ($opetusajat as $opetusaika) {
                    $opetusaika->update();
                }
                foreach ($uudetopetusajat as $uusiopetusaika) {
                    $uusiopetusaika->save();
                }
                foreach ($harjoitusryhmat as $harjoitusryhma) {
                    $harjoitusryhma->update();
                }
                foreach ($uudetharjoitusryhmat as $harjoitusryhma) {
                    $harjoitusryhma->save();
                }
                $db->commit();
                //Redirect
                Redirect::to('/course/' . $kurssiId, array("success" => "Kurssia muokattu onnistuneesti."));
            } else {
                $db->rollBack();
                $opajat = array_merge($opetusajat, $uudetopetusajat);
                $hryhmat = array_merge($harjoitusryhmat, $uudetharjoitusryhmat);
                Redirect::to('/editcourse/' . $kurssiId, array("errors" => $errors, "opetusajat" => $opajat, "harjoitusryhmat" => $hryhmat));
            }
        } catch (PDOException $ex) {
            $db->rollBack();
        }
    }

    /**
     * Kurssin poisto
     * @param int $courseId Kurssin id
     */
    public static function deleteCourse($courseId) {
        $courseId = intval($courseId);
        if (!Kurssi::find($courseId)) {
            View::make('blank.html', array("errors" => array("Kurssia ei löydy!")));
            exit();
        }
        $db = DB::connection();
        $db->beginTransaction();

        try {

            $kurssisuoritukset = array();
            $harjIlmot = array();
            $kurssiIlmot = array();
            $opetusajat = array();
            $harjoitusryhmat = array();

            //1. poista kurssisuoritukset
            $kurssisuoritukset = KurssiSuoritus::findByCourse($courseId);
            foreach ($kurssisuoritukset as $kurssisuoritus) {
                $kurssisuoritus->destroy();
            }
            //2. poista harjoitusryhmäilmoittautumiset
            $harjIlmot = HarjoitusRyhmaIlmoittautuminen::findByCourse($courseId);
            foreach ($harjIlmot as $harjIlmo) {
                $harjIlmo->destroy();
            }
            //3. poista kurssi-ilmoittautumiset
            $kurssiIlmot = KurssiIlmoittautuminen::findByCourse($courseId);
            foreach ($kurssiIlmot as $kurssiIlmo) {
                $kurssiIlmo->destroy();
            }
            //4. poista opetusajat ja harjoitusryhmät
            $opetusajat = Opetusaika::findByKurssiIdAndTyyppi($courseId, 0);
            foreach ($opetusajat as $opetusaika) {
                $opetusaika->destroy();
            }
            $harjoitusryhmat = Opetusaika::findByKurssiIdAndTyyppi($courseId, 1);
            foreach ($harjoitusryhmat as $harjoitusryhma) {
                $harjoitusryhma->destroy();
            }
            //5. poista kurssi
            $kurssi = Kurssi::find($courseId);
            if ($kurssi) {
                $kurssi->destroy();
            }
            $db->commit();
            Redirect::to("/courses", array("success" => "Kurssi poistettu onnistuneesti."));
        } catch (PDOException $ex) {
            $db->rollBack();
            Redirect::to("/course/" . $courseId, array("errors" => array("Kurssin poisto epäonnistui!")));
        }
    }

}
