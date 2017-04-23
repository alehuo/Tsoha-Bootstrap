<?php

namespace Alehuo;

class Course {

    private $name;
    private $classroom = null;
    private $from = null;
    private $to = null;
    private $color = "#37464f";
    private $lectures = array();

    /**
     * Constructor
     * @param String $name
     */
    public function __construct($name) {
        $this->name = htmlentities(trim($name));
    }

    /**
     * Set the classroom
     * @param String $room
     * @return \Alehuo\Course
     */
    public function setClassroom($room) {
        $this->classroom = htmlentities(trim($room));
        return $this;
    }

    /**
     * Set the color code for the course in hexadecimal format
     * @param int $color
     */
    public function setColor($color) {
        $this->color = $color;
        return $this;
    }

    /**
     * Starting date
     * @param \Alehuo\Date2 $start
     * @return \Alehuo\Course
     */
    public function startDate($start) {
        $this->start = $start;
        return $this;
    }

    /**
     * Ending date
     * @param \Alehuo\Date2 $end
     * @return \Alehuo\Course
     */
    public function endDate($end) {
        $this->end = $end;
        return $this;
    }

    /**
     * Add a lecture
     * @param int $weekday
     * @param \Alehuo\Time2 $start_time
     * @param \Alehuo\Time2 $end_time
     * @return \Alehuo\Course
     */
    public function addLecture($weekday, $start_time, $end_time) {
        $start_minutes = $start_time->formatInMinutes();
        $end_minutes = $end_time->formatInMinutes();
        $timeRange = range($start_minutes, $end_minutes, 15);
        foreach ($timeRange as $minutes) {
            if ($minutes !== $end_minutes) {
                $this->lectures[$weekday][$minutes] = array(
                    "endingTime" => $end_minutes,
                    "name" => $this->name,
                    "class" => $this->classroom,
                    "color" => $this->color
                );
                $tmpCheck = $minutes - $start_minutes;
                if ($tmpCheck === 15) {
                    $this->lectures[$weekday][$minutes]["isSecond"] = true;
                }
                //If the lecture index is the same as starting minutes
                if ($minutes === $start_minutes) {
                    $this->lectures[$weekday][$start_minutes]["isParent"] = true;
                }
            }
        }


        return $this;
    }

    /**
     * Return the array of lectures
     * @return Array
     */
    public function getArray() {
        return $this->lectures;
    }

    /**
     * toString
     * @return String
     */
    public function __toString() {
        return $this->name . " - " . $this->classroom . "<br/>" . $this->start . " - " . $this->end;
    }

}
