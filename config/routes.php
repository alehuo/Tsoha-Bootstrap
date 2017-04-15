<?php

function check_logged_in() {
    BaseController::check_logged_in();
}

$routes->get('/', function() {
    DefaultController::index();
});

$routes->get('/sandbox', function() {
    DefaultController::sandbox();
});

/**
 * Kurssien hakusivu.
 */
$routes->get('/courses', function() {
    CourseController::searchPage();
});

/**
 * Kurssihaun käsittely.
 */
$routes->post('/courses', function() {
    CourseController::search();
});

/**
 * Yksittäisen kurssin selaaminen.
 */
$routes->get('/course/:id', function($id) {
    CourseController::viewCourse($id);
});

/**
 * Kurssin lisääminen.
 */
$routes->get('/addcourse', 'check_logged_in', function() {
    CourseController::addCourseForm();
});

/**
 * Kurssin lisäämislomakkeen käsittely.
 */
$routes->post('/addcourse', 'check_logged_in', function() {
    CourseController::addCourse();
});

$routes->get('/registrations', 'check_logged_in', function() {
    RegistrationController::showRegistrations();
});

$routes->get('/admin', 'check_logged_in', function() {
    AdminController::showAdminPage();
});

$routes->get('/adduser', 'check_logged_in', function() {
    View::make('adduser.html');
});

$routes->get('/login', function() {
    View::make('login.html');
});

$routes->post('/login', function() {
    UserController::handleLogin();
});

$routes->get('/grades', 'check_logged_in', function() {
    View::make('grades.html');
});

$routes->get('/addgrade/:reservationId', 'check_logged_in', function($reservationId) {
    $ilmo = KurssiIlmoittautuminen::find($reservationId);
    $ilmo->harjoitusryhma = HarjoitusRyhmaIlmoittautuminen::find($ilmo->id);
    if ($ilmo) {
        $user = Kayttaja::find($ilmo->kayttajaId);
        $kurssi = Kurssi::find($ilmo->kurssiId);
        View::make('addgrade.html', array("person" => $user, "course" => $kurssi, "ilmo" => $ilmo));
    } else {
        Redirect::to('/', error("Kurssi-ilmoittautumista ei löydy"));
        exit();
    }
});

$routes->post('/addgrade/:reservationId', 'check_logged_in', function($reservationId) {
    $ilmo = KurssiIlmoittautuminen::find($reservationId);
    $ilmo->harjoitusryhma = HarjoitusRyhmaIlmoittautuminen::find($ilmo->id);

    if ($ilmo) {
        CourseController::addGrade($ilmo);
    } else {
        Redirect::to('/', error("Kurssi-ilmoittautumista ei löydy"));
        exit();
    }
});

$routes->get('/editcourse/:id', 'check_logged_in', function($id) {
    CourseController::editCourse($id);
});

$routes->post('/editcourse/:id', 'check_logged_in', function($id) {
    CourseController::handleCourseEdit($id);
});

$routes->post('/addregistration', 'check_logged_in', function() {
    RegistrationController::addRegistration();
});

$routes->post('/cancelregistration', 'check_logged_in', function() {
    RegistrationController::cancelRegistration();
});

$routes->get('/listparticipants/:courseId', 'check_logged_in', function($courseId) {
    CourseController::listParticipants($courseId);
});

$routes->post('/deletecourse/:id', 'check_logged_in', function($id) {
    CourseController::deleteCourse($id);
});
