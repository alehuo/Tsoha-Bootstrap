<?php

class HarjoitusRyhmaIlmoittautuminen extends BaseModel {

    public $id, $kurssiIlmoId, $opetusaikaId;

    public function __construct($attributes = null) {
        parent::__construct($attributes);
    }

    public static function find($id) {
        
    }

    public function save() {

        $query = DB::connection()->prepare('INSERT INTO HarjoitusRyhmaIlmoittautuminen (kurssiilmoid, opetusaikaid) VALUES (:kid, :oid) RETURNING id');
        $query->execute(
                array(
                    'kid' => $this->kurssiIlmoId,
                    'oid' => $this->opetusaikaId
                )
        );
        e;

        $row = $query->fetch();

        $this->id = $row['id'];
        return true;
    }

}
