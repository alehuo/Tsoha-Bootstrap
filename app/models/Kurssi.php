<?php

class Kurssi extends BaseModel {

    public $id, $nimi, $kuvaus, $opintoPisteet, $aloitusPvm, $lopetusPvm, $vastuuYksikkoId;

    public function __construct($arguments) {
        parent::__construct($arguments);
    }

    public static function fetchAll() {
        $query = DB::connection()->prepare('SELECT Kurssi.id, Kurssi.nimi, Kurssi.kuvaus, Kurssi.opintopisteet, Kurssi.aloituspvm, Kurssi.lopetuspvm, Kurssi.vastuuyksikkoid, Vastuuyksikko.nimi FROM Kurssi INNER JOIN Vastuuyksikko ON Kurssi.vastuuyksikkoid = Vastuuyksikko.id');
        $query->execute();
        $rows = $query->fetchAll();

        $kurssit = array();

        foreach ($rows as $row) {
            $kurssit[] = new Kurssi(array(
                'id' => $row['kurssi.id'],
                'nimi' => $row['kurssi.nimi'],
                'kuvaus' => $row['kurssi.kuvaus'],
                'opintoPisteet' => $row['kurssi.opintopisteet'],
                'aloitusPvm' => $row['kurssi.aloituspvm'],
                'lopetusPvm' => $row['kurssi.lopetuspvm'],
                'vastuuYksikkoId' => $row['kurssi.vastuuyksikkoid'],
                'vastuuYksikko' => $row['vastuuyksikko.nimi']
            ));
        }

        return $kurssit;
    }

    public static function findAllByHakusana($hakusana) {
        $query = DB::connection()->prepare('SELECT Kurssi.id, Kurssi.nimi, Kurssi.kuvaus, Kurssi.opintopisteet, Kurssi.aloituspvm, Kurssi.lopetuspvm, Kurssi.vastuuyksikkoid, Vastuuyksikko.nimi FROM Kurssi INNER JOIN Vastuuyksikko ON Kurssi.vastuuyksikkoid = Vastuuyksikko.id WHERE nimi LIKE :hakusana');
        $query->execute(array('hakusana' => $hakusana));
        $rows = $query->fetchAll();

        $kurssit = array();

        foreach ($rows as $row) {
            $kurssit[] = new Kurssi(array(
                'id' => $row['kurssi.id'],
                'nimi' => $row['kurssi.nimi'],
                'kuvaus' => $row['kurssi.kuvaus'],
                'opintoPisteet' => $row['kurssi.opintopisteet'],
                'aloitusPvm' => $row['kurssi.aloituspvm'],
                'lopetusPvm' => $row['kurssi.lopetuspvm'],
                'vastuuYksikkoId' => $row['kurssi.vastuuyksikkoid'],
                'vastuuYksikko' => $row['vastuuyksikko.nimi']
            ));
        }

        return $kurssit;
    }

    /**
     * Etsi yksi kurssi ID:n perusteella.
     * @param int $id Kurssin id
     * @return \Kurssi
     */
    public static function find($id) {
        $query = DB::connection()->prepare('SELECT Kurssi.id, Kurssi.nimi, Kurssi.kuvaus, Kurssi.opintopisteet, Kurssi.aloituspvm, Kurssi.lopetuspvm, Kurssi.vastuuyksikkoid, Vastuuyksikko.nimi FROM Kurssi INNER JOIN Vastuuyksikko ON Kurssi.vastuuyksikkoid = Vastuuyksikko.id WHERE id = :id LIMIT 1');
        $query->execute(array('id' => $id));
        $row = $query->fetch();

        if ($row) {
            $kurssi = new Kurssi(array(
                'id' => $row['kurssi.id'],
                'nimi' => $row['kurssi.nimi'],
                'kuvaus' => $row['kurssi.kuvaus'],
                'opintoPisteet' => $row['kurssi.opintopisteet'],
                'aloitusPvm' => $row['kurssi.aloituspvm'],
                'lopetusPvm' => $row['kurssi.lopetuspvm'],
                'vastuuYksikkoId' => $row['kurssi.vastuuyksikkoid'],
                'vastuuYksikko' => $row['vastuuyksikko.nimi']
            ));
            return $kurssi;
        }

        return null;
    }

    /**
     * Tallentaa kurssin.
     */
    public function save() {
        $query = DB::connection()->prepare('INSERT INTO Kurssi (nimi, kuvaus, opintopisteet, aloituspvm, lopetuspvm, vastuuyksikkoid) VALUES (:nimi, :kuvaus, :opintopisteet, :aloituspvm, :lopetuspvm, :vastuuyksikkoid) RETURNING id');
        $query->execute(
                array(
                    'nimi' => $this->nimi,
                    'kuvaus' => $this->kuvaus,
                    'opintopisteet' => $this->opintoPisteet,
                    'aloituspvm' => $this->aloitusPvm,
                    'lopetuspvm' => $this->lopetusPvm,
                    'vastuuyksikkoid' => $this->vastuuYksikkoId
                )
        );

        $row = $query->fetch();

        $this->id = $row['id'];
    }

    public function getFormattedAloitusPvm() {
        return date("j.n.Y", $this->aloitusPvm);
    }

    public function getFormattedLopetusPvm() {
        return date("j.n.Y", $this->lopetusPvm);
    }

}
