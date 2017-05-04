<?php

class FacultyController extends BaseController {

    public static function showEdit($id) {
        $id = intval($id);
        $vy = Vastuuyksikko::find($id);
        if (!$vy) {
            $errors[] = "Vastuuyksikköä ei löydy.";
            Redirect::to('/listfacultys', array("errors" => $errors));
        }
        View::make('editfaculty.html', array("faculty" => $vy));
    }

    public static function showAdd() {
        View::make('addfaculty.html');
    }

    public static function handleAdd() {
        $params = $_POST;
        $errors = array();
        if (isset($params["faculty"])) {
            $vy = new Vastuuyksikko(array("nimi" => $params["faculty"]));
            $errors = array_merge($errors, $vy->errors());
            if (!$errors) {
                $vy->save();
                Redirect::to('/listfacultys', array("success" => "Vastuuyksikkö lisätty onnistuneesti."));
            } else {
                Redirect::to('/addfaculty', array("errors" => $errors, "faculty" => $vy));
            }
        } else {
            $errors[] = "Vastuuyksikkö ei saa olla tyhjä.";
            Redirect::to('/addfaculty', array("errors" => $errors));
        }
    }

    public static function handleEdit($id) {
        $id = intval($id);
        $params = $_POST;
        $errors = array();
        $vy = Vastuuyksikko::find($id);

        if (!isset($params["faculty"])) {
            Redirect::to('/admin', array("errors" => $errors));
        }
        if (!$vy) {
            $errors[] = "Vastuuyksikköä ei löydy.";
            Redirect::to('/admin', array("errors" => $errors));
        }
        $vy->nimi = $params["faculty"];
        $errors = array_merge($errors, $vy->errors());
        if (!$errors) {
            $vy->update();
            Redirect::to('/editfaculty/' . $vy->id, array("success" => "Vastuuyksikköä muokattu onnistuneesti."));
        } else {
            Redirect::to('/editfaculty/' . $vy->id, array("errors" => $errors));
        }
    }

    public static function listAll() {
        $facultys = Vastuuyksikko::all();
        View::make('listfacultys.html', array('facultys' => $facultys));
    }

    public static function delete($id) {
        $id = intval($id);
        $faculty = Vastuuyksikko::find($id);
        if ($faculty && $id != 1) {
            $courses = Kurssi::findAllByVastuuyksikko($faculty->id);
            foreach ($courses as $course) {
                $course->vastuuYksikkoId = 1;
                $course->update();
            }
            $faculty->destroy();
            Redirect::to('/listfacultys', array("success" => "Vastuuyksikkö poistettu. Kurssit, joilla oli kyseinen vastuuyksikkö, on muutettu oletusarvoonsa."));
        } else {
            View::make('blank.html', array("errors" => array('Virhe vastuuyksikön poistamisessa: vastuuyksikköä ei löydy tai yritit poistaa oletusyksikön.')));
        }
    }

}
