<?php

class RegistrationController extends BaseController {

    public static function showRegistrations() {
        View::make('registrations.html');
    }

}
