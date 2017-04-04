<?php

class BaseModel {

    // "protected"-attribuutti on käytössä vain luokan ja sen perivien luokkien sisällä
    protected $validators;

    public function __construct($attributes = null) {
        // Käydään assosiaatiolistan avaimet läpi
        foreach ($attributes as $attribute => $value) {
            // Jos avaimen niminen attribuutti on olemassa...
            if (property_exists($this, $attribute)) {
                // ... lisätään avaimen nimiseen attribuuttin siihen liittyvä arvo
                $this->{$attribute} = $value;
            }
        }
    }

    public function errors() {
        // Lisätään $errors muuttujaan kaikki virheilmoitukset taulukkona
        $errors = array();

        foreach ($this->validators as $validator) {
            // Kutsu validointimetodia tässä ja lisää sen palauttamat virheet errors-taulukkoon
            if (!empty($this->{$validator}())) {
                $errors = array_merge($errors, $this->{$validator}());
            }
        }

        return $errors;
    }

    public static function validateStringLength($errors, $name, $string, $length) {
        if (strlen($string) > $length) {
            $errors[] = $name . " ei saa olla yli " . $length . " merkkiä pitkä.";
        }
    }

    public static function validateStringNotNull($errors, $name, $string) {
        $string = trim($string);
        if (empty($string) || strlen($string) == 0) {
            $errors[] = $name . " ei saa olla tyhjä.";
        }
    }

    public static function validateRange($errors, $name, $num, $start, $end) {
        if (!in_array($num, range($start, $end))) {
            $errors[] = $name . " täytyy olla väliltä " . $start . " ja " . $end;
        }
    }

}
