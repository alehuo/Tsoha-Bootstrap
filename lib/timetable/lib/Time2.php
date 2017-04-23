<?php

namespace Alehuo;

class Time2 {

    private $hours, $minutes;

    public function __construct($hour, $minute = 0) {
        $this->hours = intval($hour);
        $this->minutes = intval($minute);
    }

    public function getHours() {
        return $this->hours;
    }

    public function getMinutes() {
        return $this->minutes;
    }

    public function formatInMinutes() {
        return 60 * intval($this->hours) + intval($this->minutes);
    }

    public function __toString() {
        $date = new \DateTime;
        $date->setTime($this->hours, $this->minutes);
        return $date->format("G:i");
    }

}
