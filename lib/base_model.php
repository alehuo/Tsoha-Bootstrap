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
            $errors = array_merge($errors, $this->{$validator}());
        }

        return $errors;
    }

    public static function validateStringLength($name, $string, $length) {
        if (strlen($string) > $length) {
            return $name . " ei saa olla yli " . $length . " merkkiä pitkä.";
        }
        return null;
    }

    public static function validateStringNotNull($name, $string) {
        $string = trim($string);
        if (empty($string) || strlen($string) == 0) {
            return $name . " ei saa olla tyhjä.";
        }
        return null;
    }

    public static function validateRange($name, $num, $start, $end) {
        if (!in_array($num, range($start, $end))) {
            return $name . " täytyy olla väliltä " . $start . " ja " . $end;
        }

        return null;
    }

}
