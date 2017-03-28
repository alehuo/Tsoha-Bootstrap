<?php

class Kayttaja extends BaseModel {

    public $id, $tyyppi, $nimi, $salasana;

    public function __construct($attributes) {
        parent::__construct($attributes);
    }

    /**
     * Tallentaa käyttäjän.
     * @return boolean Onnistuiko käyttäjän luonti.
     */
    public function save() {
        if (!Kayttaja::userExists($this->nimi)) {
            $salasanaTiiviste = Kayttaja::createPassword($this->salasana);
            $query = DB::connection()->prepare('INSERT INTO Kayttaja (tyyppi, nimi, salasana) VALUES (:tyyppi, :nimi, :salasana) RETURNING id');
            $query->execute(
                    array(
                        'tyyppi' => $this->tyyppi,
                        'nimi' => $this->nimi,
                        'salasana' => $salasanaTiiviste
                    )
            );

            $this->salasana = $salasanaTiiviste;

            $row = $query->fetch();

            $this->id = $row['id'];
            return true;
        }
        return false;
    }

    /**
     * Palauttaa, onko käyttäjä jo tietokannassa.
     * @param String $name Käyttäjätunnus
     * @return boolean Onko käyttäjä jo tietokannassa.
     */
    public static function userExists($name) {
        $query = DB::connection()->prepare('SELECT id FROM Kayttaja WHERE nimi = :nimi LIMIT 1');
        $query->execute(array('nimi' => $name));
        $row = $query->fetch();

        if ($row) {
            return true;
        }

        return false;
    }

    /**
     * Luo salasanatiivisteen BlowFishin avulla.
     * @param String $merkkijono Salasana selkokielisenä
     * @param int $it Vaativuus
     * @return String Salasanatiiviste
     */
    public static function createPassword($merkkijono, $it = 7) {
        $suola = "";
        $merkit = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
        for ($i = 0; $i < 22; $i++) {
            $suola .= $merkit[array_rand($merkit)];
        }
        return crypt($merkkijono, sprintf('$2a$%02d$', $it) . $suola);
    }

}
