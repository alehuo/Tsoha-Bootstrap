<?php

class Kayttaja extends BaseModel {

    public $id, $tyyppi, $nimi, $salasana;

    public function __construct($attributes) {
        parent::__construct($attributes);
        $this->validators = array("validate_username", "validate_password", "validate_type", "validate_admin_privilenges");
    }

    public static function find($id) {
        try {
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
            } else {
                return null;
            }
        } catch (PDOException $ex) {
            return null;
        }
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

    public function update() {
        if ($this->salasana != null) {
            $q = "UPDATE Kayttaja SET "
                    . "nimi = :nimi,"
                    . "tyyppi = :tyyppi, salasana = :salasana WHERE id = :id";
            $query = DB::connection()->prepare($q);
            $query->execute(array(
                "nimi" => $this->nimi,
                "tyyppi" => $this->tyyppi,
                "id" => $this->id,
                "salasana" => $this->salasana
            ));
        } else {
            $q = "UPDATE Kayttaja SET "
                    . "nimi = :nimi,"
                    . "tyyppi = :tyyppi WHERE id = :id";
            $query = DB::connection()->prepare($q);
            $query->execute(array(
                "nimi" => $this->nimi,
                "tyyppi" => $this->tyyppi,
                "id" => $this->id
            ));
        }
    }

    public function destroy() {
        //1. poista kurssisuoritukset
        $kurssisuoritukset = KurssiSuoritus::findByUser($this->id);
        foreach ($kurssisuoritukset as $kurssisuoritus) {
            $kurssisuoritus->destroy();
        }
        //2. poista harjoitusryhmäilmoittautuminen
        $harjIlmot = HarjoitusRyhmaIlmoittautuminen::findByUser($this->id);
        foreach ($harjIlmot as $hilmo) {
            $hilmo->destroy();
        }
        //3. poista kurssi-ilmoittautumiset
        $kurssiIlmot = KurssiIlmoittautuminen::findByUser($this->id);
        foreach ($kurssiIlmot as $kilmo) {
            $kilmo->destroy();
        }
        //4. poista käyttäjä
        $q = "DELETE FROM Kayttaja WHERE id = :id";
        $qry = DB::connection()->prepare($q);
        $qry->execute(array(
            "id" => $this->id
        ));
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

    public function validate_username() {
        $errors = array();

        $errors[] = parent::validateStringLength("Käyttäjätunnus", $this->nimi, 100);
        $errors[] = parent::validateStringNotNull("Käyttäjätunnus", $this->nimi);

        return $errors;
    }

    public function validate_password() {
        $errors = array();

        $errors[] = parent::validateStringLength("Salasana", $this->salasana, 72);
        $errors[] = parent::validateStringNotNull("Salasana", $this->salasana);

        return $errors;
    }

    public function validate_type() {
        $errors = array();
        if (!in_array(intval($this->tyyppi), range(0, 1))) {
            $errors[] = "Virheellinen käyttäjätilin tyyppi!";
        }
        return $errors;
    }

    public function validate_admin_privilenges() {
        $errors = array();
        if (isset($this->id) && isset($this->tyyppi)) {
            if ($this->id == 1 && $this->tyyppi != 1) {
                $errors[] = "Oletuspääkäyttäjän tilin tyyppiä ei voi vaihtaa!";
            }
        }
        return $errors;
    }

    public static function findByUsername($username) {
        $query = DB::connection()->prepare("SELECT * FROM Kayttaja WHERE nimi = :username LIMIT 1");
        $query->execute(array(
            "username" => $username
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

    public static function fetchAll() {
        $query = DB::connection()->prepare('SELECT * FROM Kayttaja');
        $query->execute();
        $rows = $query->fetchAll();

        $kayttajat = array();

        foreach ($rows as $row) {

            $kayttajat[] = $kayttaja = new Kayttaja(array(
                "id" => $row["id"],
                "tyyppi" => $row["tyyppi"],
                "nimi" => $row["nimi"],
                "salasana" => $row["salasana"]
            ));
        }

        return $kayttajat;
    }

    public function averageGrade() {
        $query = DB::connection()->prepare("SELECT avg(arvosana) AS average FROM Kurssisuoritus WHERE arvosana != 6 AND arvosana != 0 AND kayttajaId = :id");
        $query->execute(array("id" => $this->id));

        $row = $query->fetch();

        if ($row) {
            return number_format((!$row["average"] ? 0 : $row["average"]), 2);
        }

        return 0;
    }

    public function totalNopat() {
        $query = DB::connection()->prepare("SELECT SUM(Kurssi.opintoPisteet) AS nopat FROM Kurssisuoritus INNER JOIN Kurssi ON Kurssisuoritus.kurssiId = Kurssi.id WHERE Kurssisuoritus.arvosana != 0 AND Kurssisuoritus.kayttajaId = :id");
        $query->execute(array("id" => $this->id));

        $row = $query->fetch();

        if ($row) {
            return (!$row["nopat"]) ? 0 : $row["nopat"];
        }

        return 0;
    }

    public function failedCourses() {
        $query = DB::connection()->prepare("SELECT COUNT(*) AS failedNum FROM Kurssisuoritus WHERE arvosana = 0 AND kayttajaId = :id");
        $query->execute(array("id" => $this->id));

        $row = $query->fetch();

        if ($row) {
            return (!$row["failednum"]) ? 0 : $row["failednum"];
        }

        return 0;
    }

}
