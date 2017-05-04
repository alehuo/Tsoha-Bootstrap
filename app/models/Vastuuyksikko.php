<?php

class Vastuuyksikko extends BaseModel {

    public $id, $nimi, $kurssiSuoritukset;

    public function __construct($attributes) {
        parent::__construct($attributes);
        $this->validators = array("validate_name");
    }

    public static function find($id) {
        $query = DB::connection()->prepare('SELECT * FROM Vastuuyksikko WHERE id = :id LIMIT 1');
        $query->execute(
                array(
                    'id' => $id
                )
        );

        $row = $query->fetch();

        if ($row) {
            return new Vastuuyksikko(array(
                'id' => $row['id'],
                'nimi' => $row['nimi']
            ));
        }

        return null;
    }

    public static function all() {
        $query = DB::connection()->prepare('SELECT * FROM Vastuuyksikko');
        $query->execute();

        $rows = $query->fetchAll();

        $vastuuyksikot = array();

        foreach ($rows as $row) {
            $vastuuyksikot[] = new Vastuuyksikko(array(
                'id' => $row['id'],
                'nimi' => $row['nimi']
            ));
        }

        return $vastuuyksikot;
    }

    public function save() {
        $query = DB::connection()->prepare('INSERT INTO Vastuuyksikko (nimi) VALUES (:nimi) RETURNING id');

        $query->execute(
                array(
                    'nimi' => $this->nimi
                )
        );

        $row = $query->fetch();

        $this->id = $row['id'];
    }

    public function update() {
        $q = "UPDATE Vastuuyksikko SET nimi = :nimi WHERE id = :id";
        $qry = DB::connection()->prepare($q);
        $qry->execute(array(
            "nimi" => $this->nimi,
            "id" => $this->id
        ));
    }

    public function validate_name() {
        $errors = array();

        $errors[] = parent::validateStringLength("Vastuuyksikön nimi", $this->nimi, 100);
        $errors[] = parent::validateStringNotNull("Vastuuyksikön nimi", $this->nimi);

        return $errors;
    }

    public function destroy() {
        $q = "DELETE FROM Vastuuyksikko WHERE id = :id";
        $qry = DB::connection()->prepare($q);
        return $qry->execute(array(
                    "id" => $this->id
        ));
    }

}
