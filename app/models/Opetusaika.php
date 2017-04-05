<?php

class Opetusaika extends BaseModel {

    public $id, $huone, $viikonpaiva, $aloitusAika, $lopetusAika, $kurssiId, $tyyppi;

    public function __construct($arguments) {
        parent::__construct($arguments);
        $this->validators = array("validate_room", "validate_time", "validate_type", "validate_weekday");
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
                'huone' => $row['huone'],
                'viikonpaiva' => $row['viikonpaiva'],
                'aloitusAika' => $row['aloitusaika'],
                'lopetusAika' => $row['lopetusaika'],
                'kurssiId' => $row['kurssiid'],
                'tyyppi' => $row['tyyppi']
            ));
        }

        return $opetusajat;
    }

    public static function find($id) {
        $query = DB::connection()->prepare('SELECT * FROM Opetusaika WHERE id = :id LIMIT 1');
        $query->execute(
                array(
                    'id' => $id
                )
        );

        $row = $query->fetch();

        $opetusaika = null;

        if ($row) {
            $opetusaika = new Opetusaika(array(
                'id' => $row['id'],
                'huone' => $row['huone'],
                'viikonpaiva' => $row['viikonpaiva'],
                'aloitusAika' => $row['aloitusaika'],
                'lopetusAika' => $row['lopetusaika'],
                'kurssiId' => $row['kurssiid'],
                'tyyppi' => $row['tyyppi']
            ));
        }

        return $opetusaika;
    }

    public static function haeIdt($kurssiId) {
        $ids = array();
        $q = "SELECT id FROM Opetusaika WHERE kurssiid = :kurssiid";
        $qry = DB::connection()->prepare($q);
        $qry->execute(array("kurssiid" => $kurssiId));

        $rows = $qry->fetchAll();

        foreach ($rows as $row) {
            $ids[] = intval($row["id"]);
        }

        return $ids;
    }

    /**
     * Tallennus.
     */
    public function save() {
        $query = DB::connection()->prepare('INSERT INTO Opetusaika (huone, viikonpaiva, aloitusaika, lopetusaika, kurssiid, tyyppi) VALUES (:huone, :viikonpaiva, :aloitusaika, :lopetusaika, :kurssiid, :tyyppi) RETURNING id');

        $query->execute(
                array(
                    'huone' => $this->huone,
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

    public function destroy() {
        $q = "DELETE FROM Opetusaika WHERE id = :id";
        $qry = DB::connection()->prepare($q);
        return $qry->execute(array(
                    "id" => $this->id
        ));
    }

    public function update() {
        $q = "UPDATE Opetusaika SET "
                . "huone = :huone, "
                . "viikonpaiva = :viikonpaiva, "
                . "aloitusAika = :aloitusaika, "
                . "lopetusAika = :lopetusaika, "
                . "tyyppi = :tyyppi WHERE id = :id";
        $qry = DB::connection()->prepare($q);
        $qry->execute(array(
            "huone" => $this->huone,
            "viikonpaiva" => $this->viikonpaiva,
            "aloitusaika" => $this->aloitusAika,
            "lopetusaika" => $this->lopetusAika,
            "tyyppi" => $this->tyyppi,
            "id" => $this->id
        ));
    }

    public function getFormattedAloitusAika() {
        return str_pad(floor($this->aloitusAika / 60), 2, "0", STR_PAD_LEFT) . ":" . str_pad($this->aloitusAika % 60, 2, "0", STR_PAD_LEFT);
    }

    public function getFormattedLopetusAika() {
        return str_pad(floor($this->lopetusAika / 60), 2, "0", STR_PAD_LEFT) . ":" . str_pad($this->lopetusAika % 60, 2, "0", STR_PAD_LEFT);
    }

    public function getViikonPaivanNimi() {
        $viikonpaivat = array(
            0 => "Maanantai",
            1 => "Tiistai",
            2 => "Keskiviikko",
            3 => "Torstai",
            4 => "Perjantai"
        );

        return $viikonpaivat[$this->viikonpaiva];
    }

    public function validate_weekday() {
        $errors = array();

        if (!in_array($this->viikonpaiva, range(0, 4))) {
            $errors[] = "Viikonpäivä on virheellinen";
        }

        return $errors;
    }

    public function validate_time() {
        $errors = array();

        if ($this->aloitusAika > $this->lopetusAika) {
            $errors[] = "Opetusajan loitusaika ei voi olla lopetusajan jälkeen";
        }

        return $errors;
    }

    public function validate_type() {
        $errors = array();

        $errors[] = parent::validateRange("Opetusajan tyyppi", $this->tyyppi, 0, 1);

        return $errors;
    }

    public function validate_room() {
        $errors = array();

        $errors[] = parent::validateStringLength("Huone", $this->huone, 10);
        $errors[] = parent::validateStringNotNull("Huone", $this->huone);

        return $errors;
    }

}
