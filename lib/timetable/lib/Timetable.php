<?php

namespace Alehuo;

class Timetable {

    private $title;
    private $tmp = null;
    private $courses = array();
    private $startingTime = null;
    private $endingTime = null;
    private $startingDate;
    private $endingDate;
    private $showSubTimes = false;

    /**
     * Constructor
     * @param String $title
     */
    public function __construct($title) {
        $this->title = $title;
        //Set starting time
        $this->startingTime = new \Alehuo\Time2(8);
        //Set ending time
        $this->endingTime = new \Alehuo\Time2(18);
    }

    public function setStartingTime(\Alehuo\Time2 $time) {
        $this->startingTime = $time;
        return $this;
    }

    public function setEndingTime(\Alehuo\Time2 $time) {
        $this->endingTime = $time;
        return $this;
    }

    public function setStartingDate(\Alehuo\Date2 $date) {
        $this->startingDate = $date;
        return $this;
    }

    public function setEndingDate(\Alehuo\date2 $date) {
        $this->endingDate = $date;
        return $this;
    }

    /**
     * Add a course
     * @param \Alehuo\Course $course
     */
    public function addCourse(\Alehuo\Course $course) {
        $this->courses = \array_replace_recursive($this->courses, $course->getArray());
        return $this;
    }

    /**
     * Convert minutes to a representable string
     * @param type $minutes
     * @return type
     */
    public function getDateString($minutes) {
        return date('G:i', mktime(0, $minutes));
    }

    /**
     * Return an array with all courses
     * @return Array
     */
    public function returnArray() {
        return $this->courses;
    }

    /**
     * Render the timetable
     */
    public function render($return = false) {

        //Table start
        $this->tmp .= "<table class='timetable fivemargin'>\n";
        //First column for weekday display
        //$curr_hrs = date();
        //Number of the day
        $daynum = (int) date("N");
        //The current hour
        $curr_hr = (int) date("H");
        //The current minute
        $curr_min = (int) date("i");
        //Title
        $this->tmp .= "<!--title row--><tr class='titlerow bordertop'><td colspan='6'>{$this->title} ({$this->startingDate} - {$this->endingDate})</td></tr>";
        //Title row
        $this->tmp .= "<!--start table row 1--><tr class='centerfont'><td class='td width200'></td><td class='td width200'><b>Maanantai</b></td><td class='td width200'><b>Tiistai</b></td><td class='td width200'><b>Keskiviikko</b></td><td class='td width200'><b>Torstai</b></td><td class='td width200'><b>Perjantai</b></td></tr>\n";
        //The next for loop will loop from the starting time to ending time, currently configured from 8:00 to 18:00, in 15 minute intervals. It will add additional 45 minutes to make the table look better.
        for ($time = 60 * $this->startingTime->getHours() + $this->startingTime->getMinutes(); $time < 60 * $this->endingTime->getHours() + $this->endingTime->getMinutes(); $time += 15) {
            //This loop now loops through weekdays. From monday to friday. The first column will include time indexes. One <tr> is equivalent for 15 minutes.
            //Start row
            $this->tmp .= "<tr>";
            for ($weekday = 0; $weekday <= 5; $weekday++) {

                switch ($weekday) {
                    case 0;
                        //8:00,9:00,10:00,...
                        if ($time % 60 == 0) {
                            $this->tmp .= "<td class='bordertop borderright'>" . $this->getDateString($time) . "</td>";
                        } else if ($time % 60 !== 0 && $this->showSubTimes) {
                            $this->tmp .= "<td class='borderright'><span class='subtime'>" . $this->getDateString($time) . "</span></td>";
                        } else {
                            $this->tmp .= "<td class='borderright'></td>";
                        }
                        break;
                    default;
                        $tmpClass = null;
                        if ($time % 60 == 0) {
                            $tmpClass = " bordertop";
                        }
                        //Process the course-array per weekday
                        if (isset($this->courses[$weekday])) {
                            //Temporary array
                            $tempArray = $this->courses[$weekday];
                            //If an array key is found for current starting time
                            if (array_key_exists($time, $tempArray)) {
                                if (isset($tempArray[$time]["isParent"])) {

                                    $this->tmp .= "<td class='data{$tmpClass} borderright' style='background-color: {$tempArray[$time]["color"]}; color: white;'><b>{$this->getDateString($time)} - {$this->getDateString($tempArray[$time]["endingTime"])}&nbsp;&nbsp;&nbsp;&nbsp;({$tempArray[$time]["class"]})</b></td>";
                                } else {
                                    if (isset($tempArray[$time]["isSecond"])) {
                                        $this->tmp .= "<td class='data borderright' style='background-color: {$tempArray[$time]["color"]}; color: white;'>{$tempArray[$time]["name"]}</td>";
                                    } else {
                                        $this->tmp .= "<td class='borderright' style='background-color: {$tempArray[$time]["color"]}; color: white;'></td>";
                                    }
                                }
                            } else {
                                if ($time % 60 == 0) {
                                    $this->tmp .= "<td class='data{$tmpClass} borderright'></td>";
                                } else {
                                    $this->tmp .= "<td class='data{$tmpClass} borderright'></td>";
                                }
                            }
                        } else {
                            if ($time % 60 == 0) {
                                $this->tmp .= "<td class='bordertop borderright'></td>";
                            } else {
                                $this->tmp .= "<td class='borderright'></td>";
                            }
                        }
                }
            }
            $this->tmp .= "</tr>\n";
        }
        $this->tmp .= "</table>\n";
        //Table footer
        if (!$return) {
            echo $this->tmp;
        } else {
            return $this->tmp;
        }
    }

}
