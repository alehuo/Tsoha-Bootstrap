<?php

class HarjoitusRyhmaIlmoittautuminen extends BaseModel {

    public $id, $kurssiIlmoId, $opetusaikaId;

    public function __construct($attributes = null) {
        parent::__construct($attributes);
    }

    public static function find($id) {
        
    }

    public function save() {
        
    }

}
