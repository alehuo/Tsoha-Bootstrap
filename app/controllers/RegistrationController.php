<?php

class RegistrationController extends BaseController {

    public static function showRegistrations() {
        View::make('registrations.html');
    }

    public static function addRegistration() {
        $params = $_POST;
        
    }

}
