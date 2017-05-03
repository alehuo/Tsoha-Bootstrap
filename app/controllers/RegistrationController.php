<?php

class RegistrationController extends BaseController {

    /**
     * Näytä kurssi-ilmoittautumiset
     */
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

    /**
     * Lisää kurssi-ilmoittautuminen
     */
    public static function addRegistration() {
        $db = DB::connection();
        $db->beginTransaction();
        BaseController::check_logged_in();

        $params = $_POST;
        $user = BaseController::get_user_logged_in();
        $courseId = intval($params["courseId"]);
        $course = Kurssi::find($courseId);

        if (!$course) {
            Redirect::to('/courses', array("errors" => array('Kurssia ei löydy!')));
        }

        $ilmoittautuminen = KurssiIlmoittautuminen::findByUserAndCourse($user->id, $course->id);

        if ($ilmoittautuminen == null && $user && $course) {
            $ilmo = new KurssiIlmoittautuminen(array("kurssiId" => $course->id, "kayttajaId" => $user->id));
            $errors = $ilmo->errors();
            if (!$errors) {

                $ilmo->save();

                if (isset($params["harjoitusRyhma"])) {
                    $opetusaika = Opetusaika::find($params["harjoitusRyhma"]);
                    if (!$opetusaika) {
                        $db->rollBack();
                        Redirect::to('/course/' . $course->id, array("errors" => array("Opetusaikaa ei löydy.")));
                    }
                    $harjoitusryhmaIlmo = new HarjoitusRyhmaIlmoittautuminen(array("kurssiIlmoId" => $ilmo->id, "opetusaikaId" => $params["harjoitusRyhma"]));
                    $harjoitusryhmaIlmo->save();
                }

                $db->commit();


                Redirect::to('/course/' . $course->id, array("success" => "Kurssi-ilmoittautuminen tallennettu."));
            } else {
                $db->rollBack();
                Redirect::to('/course/' . $course->id, array("errors" => $errors));
            }
        } else {
            //Kurssille on jo ilmoittauduttu
        }
    }

    /**
     * Peru kurssi-ilmoittautuminen
     */
    public static function cancelRegistration() {
        try {
            $db = DB::connection();
            $db->beginTransaction();

            $params = $_POST;
            $user = BaseController::get_user_logged_in();
            $courseId = intval($params["courseId"]);
            $course = Kurssi::find($courseId);

            if (!$course) {
                Redirect::to('/courses', array("errors" => array("Ilmoittautumisen poisto epäonnistui!")));
            }

            $harjRyhma = HarjoitusRyhmaIlmoittautuminen::findByUserAndCourse($user->id, $course->id);

            if ($harjRyhma) {
                $harjRyhma->destroy();
            }

            $ilmo = KurssiIlmoittautuminen::findByUserAndCourse($user->id, $course->id);

            if (!$ilmo) {
                Redirect::to('/courses', array("errors" => array("Kurssi-ilmoittautumista ei löydy!")));
            }


            if ($ilmo) {
                if ($ilmo->destroy()) {
                    $db->commit();
                    Redirect::to('/course/' . $course->id, array("success" => "Ilmoittautuminen poistettu."));
                } else {
                    $db->rollBack();
                    Redirect::to('/course/' . $course->id, array("errors" => array("Ilmoittautumisen poisto epäonnistui!")));
                }
            }
        } catch (Exception $ex) {
            $db->rollBack();
            Redirect::to('/courses', array("errors" => array("Ilmoittautumisen poisto epäonnistui!")));
        }
    }

}
