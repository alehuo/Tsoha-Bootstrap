<?php

class KurssiIlmoittautuminen extends BaseModel {

    public $id, $kurssiId, $kayttajaId, $harjoitusRyhmaId;

    public function __construct($attributes = null) {
        parent::__construct($attributes);
        $this->validators = array("validate_ids");
    }

    public static function find($id) {
        $q = "SELECT * FROM KurssiIlmoittautuminen WHERE id = :id LIMIT 1";
        $stmt = DB::connection()->prepare($q);
        $stmt->execute(array(
            "id" => $id
        ));

        $row = $stmt->fetch();
        if ($row) {
            $ilmo = new KurssiIlmoittautuminen(array(
                "id" => $row["id"],
                "kurssiId" => $row["kurssiid"],
                "kayttajaId" => $row["kayttajaid"],
                "harjoitusRyhmaId" => $row["harjoitusryhmaid"]
            ));

            return $ilmo;
        }
        return null;
    }

    public function validate_ids() {
        $errors = array();
        if (empty($this->id) || empty($this->kayttajaId) || empty($this->kurssiId) || empty($this->harjoitusRyhmaId)) {
            $errors[] = "Kurssi-ilmoittautumisen teko epäonnistui.";
        }
        return $errors;
    }

}
