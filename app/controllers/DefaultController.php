<?php

class DefaultController extends BaseController {

    /**
     * Etusivu.
     */
    public static function index() {
        View::make('index.html');
    }

    public static function sandbox() {
        // Testaa koodiasi täällä
        $kurssi = Kurssi::find(1);
        Kint::dump($kurssi);
        var_dump($kurssi);

        $testikurssi = new Kurssi(
                array(
            'nimi' => 'Testikurssi',
            'kuvaus' => 'Kurssin kuvaus',
            'aloitusPvm' => '2017-01-01',
            'lopetusPvm' => '2017-02-02',
            'vastuuYksikkoId' => '2'
                )
        );
        $testikurssi->save();
    }

}
