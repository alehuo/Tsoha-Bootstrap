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
