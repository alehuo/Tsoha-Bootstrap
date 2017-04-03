<?php

class Kayttaja extends BaseModel {

    public $id, $tyyppi, $nimi, $salasana;

    public function __construct($attributes) {
        parent::__construct($attributes);
        $this->validators = array("validate_username", "validate_password", "validate_dates");
    }

    public static function find($id) {
        $query = DB::connection()->prepare("SELECT * FROM Kayttaja WHERE id = :id LIMIT 1");
        $query->execute(array(
            "id" => $id
        ));

        $row = $query->fetch();

        if ($row) {
            $kayttaja = new Kayttaja(array(
                "id" => $row["id"],
                "tyyppi" => $row["tyyppi"],
                "nimi" => $row["nimi"],
                "salasana" => $row["salasana"]
            ));

            return $kayttaja;
        }

        return null;
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

    public function destroy() {
        
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
     * Luo salasanatiivisteen Blowfish-salauksen avulla.
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

    private static function passwordMatches($password, $hash) {
        //hash_equals ei toimi tällä PHP-versiolla.
        if ($hash === crypt($password, $hash)) {
            return true;
        }
        return false;
    }

    /**
     * Käyttäjän autentikointi
     * @param String $username Käyttäjätunnus
     * @param String $password Salasana
     * @return boolean
     */
    public static function authenticate($username, $password) {
        $q = "SELECT id, salasana FROM Kayttaja WHERE nimi = :nimi LIMIT 1";
        $stmt = DB::connection()->prepare($q);
        $stmt->execute(array(
            "nimi" => $username
        ));

        $row = $stmt->fetch();

        if ($row) {
            $password_hash = $row["salasana"];
            if (Kayttaja::passwordMatches($password, $password_hash)) {
                return Kayttaja::find($row["id"]);
            } else {
                return false;
            }
        }

        return false;
    }

    public function validate_dates() {
        $errors = array();

        if ($this->aloitusPvm > $this->lopetusPvm) {
            $errors[] = "Kurssin aloitusaika ei voi olla myöhemmin kuin lopetusaika";
        }

        return $errors;
    }

    public function validate_username() {
        $errors = array();

        $nameMaxLen = 100;
        if (empty($this->nimi) || strlen($this->nimi) > $nameMaxLen) {
            $errors[] = "Käyttäjän nimi ei saa olla tyhjä tai yli " . $nameMaxLen . " merkkiä pitkä.";
        }
        return $errors;
    }

    public function validate_password() {
        $errors = array();

        $passwdMaxLen = 72;

        if (empty($this->salasana) || strlen($this->salasana) > $passwdMaxLen) {
            $errors[] = "Käyttäjän salasana ei saa olla tyhjä tai yli " . $passwdMaxLen . " merkkiä pitkä.";
        }

        return $errors;
    }

}
