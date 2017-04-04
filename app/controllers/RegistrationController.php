<?php

class RegistrationController extends BaseController {

    public static function showRegistrations() {
        BaseController::check_logged_in();
        $user = BaseController::get_user_logged_in();
        $registrations = KurssiIlmoittautuminen::findByUser($user->id);
        foreach ($registrations as $registration) {
            $registration->harjoitusryhma = HarjoitusRyhmaIlmoittautuminen::find($registration->id);
            $registration->kayttaja = Kayttaja::find($registration->kayttajaId);
            $registration->kurssi = Kurssi::find($registration->kurssiId);
        }

        View::make('registrations.html', array("registrations" => $registrations));
    }

    public static function addRegistration() {
        BaseController::check_logged_in();

        $params = $_POST;
        $user = BaseController::get_user_logged_in();
        $course = Kurssi::find($params["courseId"]);

        $ilmoittautuminen = KurssiIlmoittautuminen::findByUserAndCourse($user->id, $course->id);

        if ($ilmoittautuminen == null && $user && $course) {
            $ilmo = new KurssiIlmoittautuminen(array("kurssiId" => $course->id, "kayttajaId" => $user->id));
            $errors = $ilmo->errors();
            if (!$errors) {
                $ilmo->save();

                if (isset($params["harjoitusRyhma"])) {
                    $harjoitusryhmaIlmo = new HarjoitusRyhmaIlmoittautuminen(array("kurssiIlmoId" => $ilmo->id, "opetusaikaId" => $params["harjoitusRyhma"]));
                    $harjoitusryhmaIlmo->save();
                }

                Redirect::to('/course/' . $course->id, array("success" => "Kurssi-ilmoittautuminen tallennettu."));
            } else {
                Redirect::to('/course/' . $course->id, array("errors" => array("Kurssi-ilmoittautuminen epäonnistui.")));
            }
        } else {
            //Kurssille on jo ilmoittauduttu
        }
    }

    public static function cancelRegistration() {
        $params = $_POST;
        $user = BaseController::get_user_logged_in();
        $course = Kurssi::find($params["courseId"]);
        var_dump($course);

        $harjRyhma = HarjoitusRyhmaIlmoittautuminen::findByUserAndCourse($user->id, $course->id);

        if ($harjRyhma) {
//            $harjRyhma->opetusaika = Opetusaika::find($harjRyhma->opetusaikaId);
            $harjRyhma->destroy();
        }

        $ilmo = KurssiIlmoittautuminen::findByUserAndCourse($user->id, $course->id);
//        $ilmo->harjoitusryhma = HarjoitusRyhmaIlmoittautuminen::find($ilmo->id);
//        $ilmo->harjoitusryhma->opetusaika = Opetusaika::find($ilmo->harjoitusryhma->opetusaikaId);

        if ($ilmo) {
            if ($ilmo->destroy()) {
                Redirect::to('/course/' . $course->id, array("success" => "Ilmoittautuminen poistettu."));
            } else {
                Redirect::to('/course/' . $course->id, array("errors" => array("Ilmoittautumisen poisto epäonnistui.")));
            }
        }
    }

}
