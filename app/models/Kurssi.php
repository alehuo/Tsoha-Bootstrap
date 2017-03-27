<?php

class Kurssi extends BaseModel {

	$id, $nimi, $kuvaus, $aloitusPvm, $lopetusPvm, $vastuuYksikkoId;

	public function __construct($arguments){
		parent::__construct($arguments);
	}

	public static function fetchAll(){
		$query = DB::connection()->prepare('SELECT * FROM Kurssi');
		$query->execute();
		$rows = $query->fetchAll();

		$kurssit = [];

		foreach($rows as $row){
			$kurssit[] = new Kurssi(array(
				'id' => $row['id'],
				'nimi' => $row['nimi'],
				'kuvaus' => $row['kuvaus'],
				'aloitusPvm' => $row['aloitusPvm'],
				'lopetusPvm' => $row['lopetusPvm'],
				'vastuuYksikkoId' => $row['vastuuYksikkoId']
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
			'aloitusPvm' => $row['aloitusPvm'],
			'lopetusPvm' => $row['lopetusPvm'],
			'vastuuYksikkoId' => $row['vastuuYksikkoId']
		));
		return $kurssi;
	}

	return null;
	}
}
