<?php
class Opetusaika extends BaseModel {
    public $viikonpaiva, $aloitusAika, $lopetusAika, $kurssiId, $tyyppi;

    public function __construct($arguments){
        parent::__construct($arguments);
    }

    public static function findByKurssiId($id){

    }

    public static function findByViikonpaiva($viikonpaiva){

    }

    public static function findByTyyppi($tyyppi){

    }
    /**
     * id SERIAL PRIMARY KEY,
 viikonpaiva INTEGER,
 aloitusAika varchar(5) NOT NULL,
 lopetusAika varchar(5) NOT NULL,
 kurssiId INTEGER REFERENCES Kurssi(id),
 tyyppi INTEGER DEFAULT 0
     */
}
