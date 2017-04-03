<?php

class Kurssi extends BaseModel {

    public $id, $nimi, $kuvaus, $opintoPisteet, $arvosteluTyyppi, $aloitusPvm, $lopetusPvm, $vastuuYksikkoId, $vastuuYksikko, $opetusajat, $harjoitusryhmat;

    public function __construct($arguments) {
        parent::__construct($arguments);
        $this->validators = array("validate_name", "validate_desc", "validate_dates");
    }

    public static function fetchAll() {
        $query = DB::connection()->prepare('SELECT Kurssi.id, Kurssi.kurssinimi, Kurssi.kuvaus, Kurssi.opintopisteet, Kurssi.arvostelutyyppi, Kurssi.aloituspvm, Kurssi.lopetuspvm, Kurssi.vastuuyksikkoid, Vastuuyksikko.nimi FROM Kurssi INNER JOIN Vastuuyksikko ON Kurssi.vastuuyksikkoid = Vastuuyksikko.id');
        $query->execute();
        $rows = $query->fetchAll();

        $kurssit = array();

        foreach ($rows as $row) {

            $kurssit[] = new Kurssi(array(
                'id' => $row['id'],
                'nimi' => $row['kurssinimi'],
                'kuvaus' => $row['kuvaus'],
                'opintoPisteet' => $row['opintopisteet'],
                'arvosteluTyyppi' => intval($row["arvostelutyyppi"]),
                'aloitusPvm' => $row['aloituspvm'],
                'lopetusPvm' => $row['lopetuspvm'],
                'vastuuYksikkoId' => $row['vastuuyksikkoid'],
                'vastuuYksikko' => $row['nimi']
            ));
        }

        return $kurssit;
    }

    public static function findAllByHakusana($hakusana) {
        $query = DB::connection()->prepare('SELECT Kurssi.id, Kurssi.kurssinimi, Kurssi.kuvaus, Kurssi.opintopisteet, Kurssi.arvostelutyyppi, Kurssi.aloituspvm, Kurssi.lopetuspvm, Kurssi.vastuuyksikkoid, Vastuuyksikko.nimi FROM Kurssi INNER JOIN Vastuuyksikko ON Kurssi.vastuuyksikkoid = Vastuuyksikko.id WHERE nimi LIKE :hakusana');
        $query->execute(array('hakusana' => $hakusana));
        $rows = $query->fetchAll();

        $kurssit = array();

        foreach ($rows as $row) {

            $kurssit[] = new Kurssi(array(
                'id' => $row['id'],
                'nimi' => $row['kurssinimi'],
                'kuvaus' => $row['kuvaus'],
                'opintoPisteet' => $row['opintopisteet'],
                'arvosteluTyyppi' => intval($row['arvostelutyyppi']),
                'aloitusPvm' => $row['aloituspvm'],
                'lopetusPvm' => $row['lopetuspvm'],
                'vastuuYksikkoId' => $row['vastuuyksikkoid'],
                'vastuuYksikko' => $row['nimi']
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
        $query = DB::connection()->prepare('SELECT Kurssi.id, Kurssi.kurssinimi, Kurssi.kuvaus, Kurssi.opintopisteet, Kurssi.arvostelutyyppi, Kurssi.aloituspvm, Kurssi.lopetuspvm, Kurssi.vastuuyksikkoid, Vastuuyksikko.nimi FROM Kurssi INNER JOIN Vastuuyksikko ON Kurssi.vastuuyksikkoid = Vastuuyksikko.id WHERE Kurssi.id = :id LIMIT 1');
        $query->execute(array('id' => $id));
        $row = $query->fetch();

        if ($row) {

            $kurssi = new Kurssi(array(
                'id' => $row['id'],
                'nimi' => $row['kurssinimi'],
                'kuvaus' => $row['kuvaus'],
                'opintoPisteet' => $row['opintopisteet'],
                'arvosteluTyyppi' => intval($row['arvostelutyyppi']),
                'aloitusPvm' => $row['aloituspvm'],
                'lopetusPvm' => $row['lopetuspvm'],
                'vastuuYksikkoId' => $row['vastuuyksikkoid'],
                'vastuuYksikko' => $row['nimi']
            ));
            return $kurssi;
        }

        return null;
    }

    /**
     * Tallentaa kurssin.
     */
    public function save() {
        $query = DB::connection()->prepare('INSERT INTO Kurssi (kurssinimi, kuvaus, opintopisteet, arvostelutyyppi, aloituspvm, lopetuspvm, vastuuyksikkoid) VALUES (:nimi, :kuvaus, :opintopisteet, :arvostelutyyppi, :aloituspvm, :lopetuspvm, :vastuuyksikkoid) RETURNING id');
        $query->execute(
                array(
                    'nimi' => $this->nimi,
                    'kuvaus' => $this->kuvaus,
                    'opintopisteet' => $this->opintoPisteet,
                    'arvostelutyyppi' => $this->arvosteluTyyppi,
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

    public function getFormAloitusPvm() {
        return date("d.m.Y", $this->aloitusPvm);
    }

    public function getFormLopetusPvm() {
        return date("d.m.Y", $this->lopetusPvm);
    }

    public function validate_name() {
        $nameMaxLen = 50;
        $errors = array();

        if (empty($this->nimi) || strlen($this->nimi) > $nameMaxLen) {
            $errors[] = "Kurssin nimi ei saa olla tyhjä tai yli " . $nameMaxLen . " merkkiä pitkä";
        }

        return $errors;
    }

    public function validate_desc() {
        $descMaxLen = 255;
        $errors = array();
        if (empty($this->kuvaus) || strlen($this->kuvaus) > $descMaxLen) {
            $errors[] = "Kurssin kuvaus ei saa olla tyhjä tai yli " . $descMaxLen . " merkkiä pitkä";
        }
        return $errors;
    }

    public function validate_dates() {
        $errors = array();

        if ($this->aloitusPvm > $this->lopetusPvm) {
            $errors[] = "Kurssin aloitusaika ei voi olla myöhemmin kuin lopetusaika";
        }

        return $errors;
    }

    public function validate_vy() {
        $errors = array();

        if ($this->vastuuYksikko == -1) {
            $errors[] = "Vastuuyksikkö on virheellinen";
        }

        return $errors;
    }

}
