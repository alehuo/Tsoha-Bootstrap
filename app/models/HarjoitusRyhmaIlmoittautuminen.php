<?php

class HarjoitusRyhmaIlmoittautuminen extends BaseModel {

    public $id, $kurssiIlmoId, $opetusaikaId, $opetusaika;

    public function __construct($attributes = null) {
        parent::__construct($attributes);
    }

    public static function find($ilmoid) {
        $q = "SELECT * FROM HarjoitusRyhmaIlmoittautuminen WHERE kurssiilmoid = :id LIMIT 1";
        $stmt = DB::connection()->prepare($q);
        $stmt->execute(array(
            "id" => $ilmoid
        ));

        $row = $stmt->fetch();
        if ($row) {


            $ilmo = new HarjoitusRyhmaIlmoittautuminen(array(
                "id" => $row["id"],
                "kurssiId" => $row["kurssiilmoid"],
                "opetusaikaId" => $row["opetusaikaid"]
            ));

            return $ilmo;
        }
        return null;
    }

    public static function findByUserAndCourse($userid, $courseid) {
        $q = "SELECT * FROM HarjoitusRyhmaIlmoittautuminen "
                . "INNER JOIN KurssiIlmoittautuminen ON HarjoitusRyhmaIlmoittautuminen.kurssiilmoid = KurssiIlmoittautuminen.id "
                . "WHERE KurssiIlmoittautuminen.kayttajaid = :kayttajaid AND KurssiIlmoittautuminen.kurssiid = :kurssiid LIMIT 1";

        $stmt = DB::connection()->prepare($q);
        $stmt->execute(array(
            "kayttajaid" => $userid,
            "kurssiid" => $courseid
        ));

        $row = $stmt->fetch();
        if ($row) {

            $ilmo = new HarjoitusRyhmaIlmoittautuminen(array(
                "id" => $row["id"],
                "kurssiIlmoId" => $row["kurssiilmoid"],
                "opetusaikaId" => $row["opetusaikaid"]
            ));

            return $ilmo;
        }
        return null;
    }

    public static function findByUser($kayttajaId) {
        $q = "SELECT * FROM HarjoitusRyhmaIlmoittautuminen "
                . "INNER JOIN KurssiIlmoittautuminen ON HarjoitusRyhmaIlmoittautuminen.kurssiilmoid = KurssiIlmoittautuminen.id "
                . "WHERE KurssiIlmoittautuminen.kayttajaid = :kayttajaid";

        $stmt = DB::connection()->prepare($q);
        $stmt->execute(array(
            "kayttajaid" => $kayttajaId
        ));

        $ilmot = array();

        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {

            $ilmot[] = new HarjoitusRyhmaIlmoittautuminen(array(
                "id" => $row["id"],
                "kurssiIlmoId" => $row["kurssiilmoid"],
                "opetusaikaId" => $row["opetusaikaid"]
            ));
        }
        return $ilmot;
    }

    public static function findByCourse($courseid) {
        $q = "SELECT * FROM HarjoitusRyhmaIlmoittautuminen "
                . "INNER JOIN KurssiIlmoittautuminen ON HarjoitusRyhmaIlmoittautuminen.kurssiilmoid = KurssiIlmoittautuminen.id "
                . "WHERE KurssiIlmoittautuminen.kurssiid = :kurssiid";

        $stmt = DB::connection()->prepare($q);
        $stmt->execute(array(
            "kurssiid" => $courseid
        ));

        $ilmot = array();

        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {

            $ilmot[] = new HarjoitusRyhmaIlmoittautuminen(array(
                "id" => $row["id"],
                "kurssiIlmoId" => $row["kurssiilmoid"],
                "opetusaikaId" => $row["opetusaikaid"]
            ));
        }
        return $ilmot;
    }

    public function save() {

        $query = DB::connection()->prepare('INSERT INTO HarjoitusRyhmaIlmoittautuminen (kurssiilmoid, opetusaikaid) VALUES (:kid, :oid) RETURNING id');
        $query->execute(
                array(
                    'kid' => $this->kurssiIlmoId,
                    'oid' => $this->opetusaikaId
                )
        );

        $row = $query->fetch();

        $this->id = $row['id'];
        return true;
    }

    public function destroy() {
        $query = DB::connection()->prepare("DELETE FROM HarjoitusRyhmaIlmoittautuminen WHERE kurssiilmoid = :id");
        return $query->execute(array("id" => $this->kurssiIlmoId));
    }

}
