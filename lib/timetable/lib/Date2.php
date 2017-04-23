<?php

namespace Alehuo;

class Date2 {

    private $day, $month, $year;

    public function __construct($day, $month, $year) {
        $this->day = intval($day);
        $this->month = intval($month);
        $this->year = intval($year);
    }

    public function getDay() {
        return $this->year;
    }

    public function getMonth() {
        return $this->month;
    }

    public function getYear() {
        return $this->year;
    }

    public function __toString() {
        $date = new \DateTime;
        $date->setDate($this->year, $this->month, $this->day);
        return $date->format("j.n.Y");
    }

}
