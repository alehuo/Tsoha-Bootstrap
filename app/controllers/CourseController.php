<?php

class CourseController extends BaseController {

    public static function searchPage() {
        View::make("courses.html");
    }

    public static function search() {
        $params = $_POST;
        $hakusana = $params['hakusana'];

        $courses = Kurssi::findAllByHakusana('%' . $hakusana . '%');

        View::make('courses.html', array("courses" => $courses, "lkm" => count($courses), "searchTerm" => htmlentities($hakusana, ENT_QUOTES)));
    }

    public static function viewCourse($id) {
        $course = Kurssi::find($id);

        View::make('course.html', array("course" => $course));
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

    public static function addCourse() {
        $db = DB::connection();
        $db->beginTransaction();
        try {
            $postData = $_POST;

            $kurssin_nimi = $postData["nimi"];

            if (empty($postData["uusiVastuuYksikko"])) {
                $vastuuyksikko = $postData["vastuuyksikkoSelect"];
            } else {
                //Luo uusi vastuuyksikkö ja palauta id
                //....
                $uusiVastuuyksikko = new Vastuuyksikko(array("nimi" => $postData["uusiVastuuYksikko"]));
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

            $kurssi->save();

            $ajat = array();

            for ($i = 1; $i < count($postData["opetusaikaHuone"]); $i++) {

                $loppuaika = (int) $postData["opetusaikaAloitusaika"][$i] + 60 * (int) $postData["opetusaikaKesto"];

                $opetusaika = new Opetusaika(array(
                    'viikonpaiva' => (int) $postData["opetusaikaViikonpaiva"][$i],
                    'aloitusAika' => (int) $postData["opetusaikaAloitusaika"][$i],
                    'lopetusAika' => $loppuaika,
                    'kurssiId' => $kurssi->id,
                    'tyyppi' => 0
                ));

                $ajat[] = $opetusaika;
            }

            for ($i = 1; $i < count((int) $postData["harjoitusryhmaHuone"]); $i++) {
                $loppuaika = (int) $postData["harjoitusryhmaAloitusaika"][$i] + 60 * (int) $postData["harjoitusryhmaKesto"];

                $harjoitusryhma = new Opetusaika(array(
                    'viikonpaiva' => (int) $postData["harjoitusryhmaViikonpaiva"][$i],
                    'aloitusAika' => (int) $postData["harjoitusryhmaAloitusaika"][$i],
                    'lopetusAika' => $loppuaika,
                    'kurssiId' => $kurssi->id,
                    'tyyppi' => 1
                ));

                $ajat[] = $harjoitusryhma;
            }

            foreach ($ajat as $key => $opetusaika) {
                $opetusaika->save();
            }

            $db->commit();

            Redirect::to("/");
        } catch (PDOException $ex) {
            $db->rollBack();
        }
    }

}
