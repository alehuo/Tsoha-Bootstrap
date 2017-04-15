<?php

function check_logged_in() {
    BaseController::check_logged_in();
}

function check_admin() {
    BaseController::is_user_admin();
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
$routes->get('/courses', 'check_logged_in', function() {
    CourseController::searchPage();
});

/**
 * Kurssihaun käsittely.
 */
$routes->post('/courses', 'check_logged_in', function() {
    CourseController::search();
});

/**
 * Yksittäisen kurssin selaaminen.
 */
$routes->get('/course/:id', 'check_logged_in', function($id) {
    CourseController::viewCourse($id);
});

/**
 * Kurssin lisääminen.
 */
$routes->get('/addcourse', 'check_logged_in', 'is_user_admin', function() {
    CourseController::addCourseForm();
});

/**
 * Kurssin lisäämislomakkeen käsittely.
 */
$routes->post('/addcourse', 'check_logged_in', 'is_user_admin', function() {
    CourseController::addCourse();
});
/**
 * Näytä ilmoittautumiset.
 */
$routes->get('/registrations', 'check_logged_in', function() {
    RegistrationController::showRegistrations();
});
/**
 * Pääkäyttäjäsivu.
 */
$routes->get('/admin', 'check_logged_in', 'is_user_admin', function() {
    AdminController::showAdminPage();
});
/**
 * Käyttäjän lisääminen.
 */
$routes->get('/adduser', 'check_logged_in', 'is_user_admin', function() {
    View::make('adduser.html');
});

/**
 * Sisäänkirjautuminen.
 */
$routes->get('/login', function() {
    View::make('login.html');
});

/**
 * Sisäänkirjautuminen.
 */
$routes->post('/login', function() {
    UserController::handleLogin();
});

/**
 * Arvosanojen tarkastelu.
 */
$routes->get('/grades', 'check_logged_in', function() {
    View::make('grades.html');
});

/**
 * Lisää kurssisuoritus.
 */
$routes->get('/addgrade/:reservationId', 'check_logged_in', 'is_user_admin', function($reservationId) {
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

/**
 * Lisää kurssisuoritus.
 */
$routes->post('/addgrade/:reservationId', 'check_logged_in', 'is_user_admin', function($reservationId) {
    $ilmo = KurssiIlmoittautuminen::find($reservationId);
    $ilmo->harjoitusryhma = HarjoitusRyhmaIlmoittautuminen::find($ilmo->id);

    if ($ilmo) {
        CourseController::addGrade($ilmo);
    } else {
        Redirect::to('/', error("Kurssi-ilmoittautumista ei löydy"));
        exit();
    }
});

/**
 * Muokkaa kurssia.
 */
$routes->get('/editcourse/:id', 'check_logged_in', 'is_user_admin', function($id) {
    CourseController::editCourse($id);
});

/**
 * Muokkaa kurssia.
 */
$routes->post('/editcourse/:id', 'check_logged_in', 'is_user_admin', function($id) {
    CourseController::handleCourseEdit($id);
});

/**
 * Lisää kurssi-ilmo.
 */
$routes->post('/addregistration', 'check_logged_in', function() {
    RegistrationController::addRegistration();
});

/**
 * Peru kurssi-ilmo.
 */
$routes->post('/cancelregistration', 'check_logged_in', function() {
    RegistrationController::cancelRegistration();
});

/**
 * Listaa kurssin osallistujat.
 */
$routes->get('/listparticipants/:courseId', 'check_logged_in', 'is_user_admin', function($courseId) {
    CourseController::listParticipants($courseId);
});

/**
 * Poista kurssi.
 */
$routes->post('/deletecourse/:id', 'check_logged_in', 'is_user_admin', function($id) {
    CourseController::deleteCourse($id);
});
$routes->get('/unauthorized', function() {
    DefaultController::unauthorizedPage();
});
