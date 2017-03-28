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
        for ($i = 0; $i < 65; $i++) {
            $val = str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0", STR_PAD_LEFT);
            $ajat[] = array("id" => $val, "arvo" => $val);

            if ($minutes == 45) {
                $hours++;
                $minutes = 0;
            } else {
                $minutes += 15;
            }
        }

        View::make('addcourse.html', array("ajat" => $ajat));
    }

    public static function addCourse() {
        $p = $_POST;

        $kurssin_nimi = $p["nimi"];

        if (empty($p["uusiVastuuYksikko"])) {
            $vastuuYksikko = $p["vastuuyksikkoSelect"];
        } else {
            //Luo uusi vastuuyksikkö ja palauta id
            //....
        }

        $alkamisPvm = $p["startingDate"];

        $lopetusPvm = $p["endingDate"];

        $op = $p["op"];

//        if (isset($p["arvosteluTyyppi"])) {
//            $arvostelu = $p["arvosteluTyyppi"];
//        }


        $kuvaus = $p["kuvaus"];

        $kurssi = new Kurssi(array(
            'nimi' => $kurssin_nimi,
            'kuvaus' => $kuvaus,
            'opintoPisteet' => $op,
            'aloitusPvm' => $alkamisPvm,
            'lopetusPvm' => $lopetusPvm,
            'vastuuYksikkoId' => $vastuuYksikko
        ));

        $kurssi->save();

        //Lisää opetusajat..
        //Poista ensin placeholderit
//        unset($p["opetusaikaHuone"][0]);
//        unset($p["opetusaikaAloitusaika"][0]);
//        unset($p["opetusaikaKesto"][0]);
//        unset($p["opetusaikaViikonpaiva"][0]);
//
//        unset($p["harjoitusryhmaHuone"][0]);
//        unset($p["harjoitusryhmaAloitusaika"][0]);
//        unset($p["harjoitusryhmaKesto"][0]);
//        unset($p["harjoitusryhmaViikonpaiva"][0]);

        $length = count($p["opetusaikaHuone"]);

        $ajat = array();

        for ($i = 1; $i < $length; $i++) {

            $loppuaika = date("H:m", (strtotime($p["opetusaikaAloitusaika"][$i]) + 60 * 60 * (int) $p["opetusaikaKesto"]));

            $opetusaika = new Opetusaika(array(
                'viikonpaiva' => $p["opetusaikaViikonpaiva"][$i],
                'aloitusAika' => $p["opetusaikaAloitusaika"][$i],
                'lopetusAika' => $loppuaika,
                'kurssiId' => $kurssi->id,
                'tyyppi' => 0
            ));

            $ajat[] = $opetusaika;
        }

        $length = count($p["harjoitusryhmaHuone"]);

        for ($i = 1; $i < $length; $i++) {
            $loppuaika = date("d.m.Y", strtotime($p["harjoitusryhmaAloitusaika"][$i]) + 60 * 60 * (int) $p["harjoitusryhmaKesto"]);

            $harjoitusryhma = new Opetusaika(array(
                'viikonpaiva' => $p["harjoitusryhmaViikonpaiva"][$i],
                'aloitusAika' => $p["harjoitusryhmaAloitusaika"][$i],
                'lopetusAika' => $loppuaika,
                'kurssiId' => $kurssi->id,
                'tyyppi' => 1
            ));

            $ajat[] = $harjoitusryhma;
        }

        foreach ($ajat as $key => $opetusaika) {
            $opetusaika->save();
        }

        echo "<pre>";
        var_dump($ajat);
        echo "</pre>";
    }

}
