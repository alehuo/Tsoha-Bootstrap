<?php

class KurssiSuoritus extends BaseModel {

    public $id, $kurssiId, $kayttajaId, $arvosana, $paivays;

    public function __construct($attributes = null) {
        parent::__construct($attributes);
        $this->validators = array("validate_grade");
    }

    public static function find($id) {
        $query = DB::connection()->prepare('SELECT * FROM KurssiSuoritus WHERE id = :id LIMIT 1');
        $query->execute(
                array(
                    'id' => $id
                )
        );

        $row = $query->fetch();

        if ($row) {
            return new KurssiSuoritus(array(
                'id' => $row['id'],
                'kurssiId' => $row['kurssiid'],
                'kayttajaId' => $row['kayttajaid'],
                'arvosana' => $row['arvosana'],
                'paivays' => $row['paivays']
            ));
        }

        return null;
    }

    public static function findByCourse($courseId) {
        $query = DB::connection()->prepare('SELECT * FROM KurssiSuoritus WHERE kurssiid = :id');
        $query->execute(
                array(
                    'id' => $courseId
                )
        );

        $suoritukset = array();

        $rows = $query->fetchAll();

        foreach ($rows as $row) {
            $suoritukset[] = new KurssiSuoritus(array(
                'id' => $row['id'],
                'kurssiId' => $row['kurssiid'],
                'kayttajaId' => $row['kayttajaid'],
                'arvosana' => $row['arvosana'],
                'paivays' => $row['paivays']
            ));
        }

        return $suoritukset;
    }

    public function save() {
        $query = DB::connection()->prepare('INSERT INTO KurssiSuoritus (kurssiid, kayttajaid, arvosana, paivays) VALUES (:kurssiid, :kayttajaid, :arvosana, :paivays) RETURNING id');
        $query->execute(
                array(
                    'kurssiid' => $this->kurssiId,
                    'kayttajaid' => $this->kayttajaId,
                    'arvosana' => $this->arvosana,
                    'paivays' => $this->paivays
                )
        );

        $row = $query->fetch();

        $this->id = $row['id'];

        return true;
    }

    public function destroy() {
        $q = "DELETE FROM KurssiSuoritus WHERE id = :id";
        $qry = DB::connection()->prepare($q);
        return $qry->execute(array(
                    "id" => $this->id
        ));
    }

    public function validate_grade() {
        $errors = array();

        $errors[] = parent::validateRange("Arvosana", $this->arvosana, 0, 6);

        return $errors;
    }

}
