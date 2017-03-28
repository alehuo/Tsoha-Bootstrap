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

    public static function addCourse() {
        $ajat = array();
        $hours = 7;
        $minutes = 0;
        for ($i = 0; $i < 65; $i++) {
            $val = str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0", STR_PAD_LEFT);
            $ajat[] = array("id" => $i, "arvo" => $val);

            if ($minutes == 45) {
                $hours++;
                $minutes = 0;
            } else {
                $minutes += 15;
            }
        }

        View::make('addcourse.html', array("ajat" => $ajat));
    }

}
