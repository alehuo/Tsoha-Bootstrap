<?php

class Opetusaika extends BaseModel {

    public $viikonpaiva, $aloitusAika, $lopetusAika, $kurssiId, $tyyppi;

    public function __construct($arguments) {
        parent::__construct($arguments);
    }

    /**
     * Hakee kaikki opetusajat jotka vastaavat kurssi-id:tä ja tyyppiä.
     * @param int $id Kurssi-id
     * @param int $tyyppi Tyyppi (0 = luento, 1 = laskari)
     * @return \Opetusaika
     */
    public static function findByKurssiIdAndTyyppi($id, $tyyppi) {
        $query = DB::connection()->prepare('SELECT * FROM Opetusaika WHERE kurssiid = :kurssiId AND tyyppi = :tyyppi');
        $query->execute(
                array(
                    'kurssiId' => $id,
                    'tyyppi' => $tyyppi
                )
        );

        $rows = $query->fetchAll();

        $opetusajat = array();

        foreach ($rows as $row) {
            $opetusajat[] = new Opetusaika(array(
                'id' => $row['id'],
                'viikonpaiva' => $row['viikonpaiva'],
                'aloitusAika' => $row['aloitusaika'],
                'lopetusAika' => $row['lopetusaika'],
                'kurssiId' => $row['kurssiid'],
                'tyyppi' => $row['tyyppi']
            ));
        }

        return $opetusajat;
    }

    /**
     * Tallennus.
     */
    public function save() {
        $query = DB::connection()->prepare('INSERT INTO Opetusaika (viikonpaiva, aloitusaika, lopetusaika, kurssiid, tyyppi) VALUES (:viikonpaiva, :aloitusaika, :lopetusaika, :kurssiid, :tyyppi) RETURNING id');

        $query->execute(
                array(
                    'viikonpaiva' => $this->viikonpaiva,
                    'aloitusaika' => $this->aloitusAika,
                    'lopetusaika' => $this->lopetusAika,
                    'kurssiid' => $this->kurssiId,
                    'tyyppi' => $this->tyyppi
                )
        );

        $row = $query->fetch();

        $this->id = $row['id'];
    }

    public function getFormattedAloitusAika() {
        return floor($this->aloitusAika / 60) . ":" . $this->aloitusAika % 60;
    }

    public function getFormattedLopetusAika() {
        return floor($this->lopetusAika / 60) . ":" . $this->lopetusAika % 60;
    }

}
