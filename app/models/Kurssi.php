<?php

class Kurssi extends BaseModel {

    public $id, $nimi, $kuvaus, $aloitusPvm, $lopetusPvm, $vastuuYksikkoId;

    public function __construct($arguments){
        parent::__construct($arguments);
    }

    public static function fetchAll(){
        $query = DB::connection()->prepare('SELECT * FROM Kurssi');
        $query->execute();
        $rows = $query->fetchAll();

        $kurssit = array();

        foreach($rows as $row){
            $kurssit[] = new Kurssi(array(
                'id' => $row['id'],
                'nimi' => $row['nimi'],
                'kuvaus' => $row['kuvaus'],
                'aloitusPvm' => $row['aloituspvm'],
                'lopetusPvm' => $row['lopetuspvm'],
                'vastuuYksikkoId' => $row['vastuuyksikkoid']
            ));
        }

    return $kurssit;
    }

    public static function find($id){
    $query = DB::connection()->prepare('SELECT * FROM Kurssi WHERE id = :id LIMIT 1');
    $query->execute(array('id' => $id));
    $row = $query->fetch();

    if($row){
        $kurssi = new Kurssi(array(
            'id' => $row['id'],
            'nimi' => $row['nimi'],
            'kuvaus' => $row['kuvaus'],
            'aloitusPvm' => $row['aloituspvm'],
            'lopetusPvm' => $row['lopetuspvm'],
            'vastuuYksikkoId' => $row['vastuuyksikkoid']
        ));
        return $kurssi;
    }

    return null;
    }

    public function save(){
    $query = DB::connection()->prepare('INSERT INTO Kurssi (nimi, kuvaus, aloituspvm, lopetuspvm, vastuuyksikkoid) VALUES (:nimi, :kuvaus, :aloituspvm, :lopetuspvm, :vastuuyksikkoid) RETURNING id');

    $query->execute(
        array(
            'nimi' => $this->nimi,
            'kuvaus' => $this->kuvaus,
            'aloituspvm' => $this->aloitusPvm,
            'lopetuspvm' => $this->lopetusPvm,
            'vastuuyksikkoid' => $this->vastuuYksikkoId
            )
    );

    $row = $query->fetch();

    $this->id = $row['id'];
    }
}
