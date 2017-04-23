<?php

class KurssiIlmoittautuminen extends BaseModel {

    public $id, $kurssiId, $kayttajaId, $harjoitusryhma, $kayttaja, $kurssi;

    public function __construct($attributes = null) {
        parent::__construct($attributes);
        $this->validators = array("validate_ids");
    }

    public static function find($id) {
        $q = "SELECT * FROM KurssiIlmoittautuminen WHERE id = :id LIMIT 1";
        $stmt = DB::connection()->prepare($q);
        $stmt->execute(array(
            "id" => $id
        ));

        $row = $stmt->fetch();
        if ($row) {

            $ilmo = new KurssiIlmoittautuminen(array(
                "id" => $row["id"],
                "kurssiId" => $row["kurssiid"],
                "kayttajaId" => $row["kayttajaid"]
            ));

            return $ilmo;
        }
        return null;
    }

    public static function findByUserAndCourse($kayttajaid, $kurssiid) {
        $q = "SELECT * FROM KurssiIlmoittautuminen WHERE kayttajaid = :kayttajaid AND kurssiid = :kurssiid LIMIT 1";
        $stmt = DB::connection()->prepare($q);
        $stmt->execute(array(
            "kayttajaid" => $kayttajaid,
            "kurssiid" => $kurssiid
        ));

        $row = $stmt->fetch();
        if ($row) {

            $ilmo = new KurssiIlmoittautuminen(array(
                "id" => $row["id"],
                "kurssiId" => $row["kurssiid"],
                "kayttajaId" => $row["kayttajaid"]
            ));

            return $ilmo;
        }
        return null;
    }

    public static function findByCourse($kurssiid) {
        $q = "SELECT * FROM KurssiIlmoittautuminen WHERE kurssiid = :kurssiid";
        $stmt = DB::connection()->prepare($q);
        $stmt->execute(array(
            "kurssiid" => $kurssiid
        ));

        $rows = $stmt->fetchAll();

        $ilmot = array();

        foreach ($rows as $row) {
            $ilmot[] = new KurssiIlmoittautuminen(array(
                "id" => $row["id"],
                "kurssiId" => $row["kurssiid"],
                "kayttajaId" => $row["kayttajaid"]
            ));
        }

        return $ilmot;
    }

    public static function findByUser($userid) {
        $q = "SELECT * FROM KurssiIlmoittautuminen WHERE kayttajaid = :kayttajaid";
        $stmt = DB::connection()->prepare($q);
        $stmt->execute(array(
            "kayttajaid" => $userid
        ));

        $rows = $stmt->fetchAll();

        $ilmot = array();

        foreach ($rows as $row) {


            $ilmot[] = new KurssiIlmoittautuminen(array(
                "id" => $row["id"],
                "kurssiId" => $row["kurssiid"],
                "kayttajaId" => $row["kayttajaid"]
            ));
        }

        return $ilmot;
    }

    public static function findByUserAndBetweenDates($userid, $startingDate, $endingDate) {
        $q = "SELECT * FROM KurssiIlmoittautuminen INNER JOIN Kurssi ON KurssiIlmoittautuminen.kurssiid = Kurssi.id WHERE kayttajaid = :kayttajaid AND Kurssi.aloitusPvm < :startingDate AND Kurssi.lopetusPvm > :endingDate";
        $stmt = DB::connection()->prepare($q);
        $stmt->execute(array(
            "kayttajaid" => $userid,
            "startingDate" => $startingDate,
            "endingDate" => $endingDate
        ));
        
        $rows = $stmt->fetchAll();

        $ilmot = array();

        foreach ($rows as $row) {


            $ilmot[] = new KurssiIlmoittautuminen(array(
                "id" => $row["id"],
                "kurssiId" => $row["kurssiid"],
                "kayttajaId" => $row["kayttajaid"]
            ));
        }

        return $ilmot;
    }

    public static function findByOpetusaikaId($opetusaikaId) {
        $q = "SELECT kurssiilmoittautuminen.id, kurssiilmoittautuminen.kurssiid, kurssiilmoittautuminen.kayttajaid FROM harjoitusryhmailmoittautuminen INNER JOIN kurssiilmoittautuminen ON harjoitusryhmailmoittautuminen.kurssiilmoid = kurssiilmoittautuminen.id WHERE harjoitusryhmailmoittautuminen.opetusaikaid = :opetusaikaid";

        $qry = DB::connection()->prepare($q);
        $qry->execute(array(
            "opetusaikaid" => $opetusaikaId
        ));

        $rows = $qry->fetchAll();

        $kurssiIlmot = array();

        foreach ($rows as $row) {
            $kurssiIlmot[] = new KurssiIlmoittautuminen(array(
                "id" => $row["id"],
                "kurssiId" => $row["kurssiid"],
                "kayttajaId" => $row["kayttajaid"]
            ));
        }

        return $kurssiIlmot;
    }

    public function save() {
        $query = DB::connection()->prepare('INSERT INTO KurssiIlmoittautuminen (kurssiid, kayttajaid) VALUES (:kurssiid, :kayttajaid) RETURNING id');
        $query->execute(
                array(
                    'kurssiid' => $this->kurssiId,
                    'kayttajaid' => $this->kayttajaId
                )
        );

        $row = $query->fetch();

        $this->id = $row['id'];

        return true;
    }

    public function destroy() {
        $query = DB::connection()->prepare("DELETE FROM KurssiIlmoittautuminen WHERE id = :id");
        return $query->execute(array("id" => $this->id));
    }

    public function validate_ids() {
        $errors = array();

        $errors[] = parent::validateStringNotNull("Käyttäjän id", $this->kayttajaId);
        $errors[] = parent::validateStringNotNull("Kurssin id", $this->kurssiId);

        return $errors;
    }

}
