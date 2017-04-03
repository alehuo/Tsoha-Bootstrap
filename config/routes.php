<?php

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
$routes->get('/addcourse', function() {
    CourseController::addCourseForm();
});

/**
 * Kurssin lisäämislomakkeen käsittely.
 */
$routes->post('/addcourse', function() {
    CourseController::addCourse();
});

$routes->get('/registrations', function() {
    RegistrationController::showRegistrations();
});

$routes->get('/admin', function() {
    AdminController::showAdminPage();
});

$routes->get('/adduser', function() {
    View::make('unauthorized.html');
});

$routes->get('/login', function() {
    View::make('login.html');
});

$routes->post('/login', function() {
    UserController::handleLogin();
});

$routes->get('/grades', function() {
    View::make('grades.html');
});

$routes->get('/addgrade/:reservationId', function($reservationId) {
    $ilmo = KurssiIlmoittautuminen::find($reservationId);
    if ($ilmo) {
        $user = Kayttaja::find($ilmo->kayttajaId);
        $kurssi = Kurssi::find($ilmo->kurssiId);
        View::make('addgrade.html', array("person" => $user, "course" => $kurssi, "ilmo" => $ilmo));
    } else {
        Redirect::to('/', error("Kurssi-ilmoittautumista ei löydy"));
        exit();
    }
});

$routes->post('/addgrade/:reservationId', function($reservationId) {
    $ilmo = KurssiIlmoittautuminen::find($reservationId);
    if ($ilmo) {
        CourseController::addGrade($ilmo);
    } else {
        Redirect::to('/', error("Kurssi-ilmoittautumista ei löydy"));
        exit();
    }
});

$routes->get('/editcourse/:id', function($id) {
    CourseController::editCourse($id);
});

$routes->post('/editcourse/:id', function($id) {
    CourseController::handleCourseEdit($id);
});
