<?php

class Vastuuyksikko extends BaseModel {

    public $id, $nimi;

    public function __construct($attributes) {
        parent::__construct($attributes);
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

}
