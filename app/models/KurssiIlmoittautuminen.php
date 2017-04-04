<?php

class KurssiIlmoittautuminen extends BaseModel {

    public $id, $kurssiId, $kayttajaId;

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

    public function save() {
        $query = DB::connection()->prepare('INSERT INTO KurssiIlmoittautuminen (kurssiid, kayttajaid) VALUES (:kurssiid, :kayttajaid) RETURNING id');
        $query->execute(
                array(
                    'kurssiid' => $this->kurssiId,
                    'kayttajaid' => $this->kayttajaId
                )
        );

        $row = $query->fetch();

        $this->id = $row['id'];

        return true;
    }

    public function validate_ids() {
        $errors = array();

        $errors[] = parent::validateStringNotNull("Käyttäjän id", $this->kayttajaId);
        $errors[] = parent::validateStringNotNull("Kurssin id", $this->kurssiId);

        return $errors;
    }

}
