<?php

class KurssiIlmoittautuminen extends BaseModel {

    public $kurssiId, $kayttajaId;

    public function __construct($attributes = null) {
        parent::__construct($attributes);
    }

}
