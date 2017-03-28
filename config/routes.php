<?php

$routes->get('/', function() {
    DefaultController::index();
});

$routes->get('/sandbox', function() {
    DefaultController::sandbox();
});

$routes->get('/courses', function() {
    CourseController::searchPage();
});

$routes->post('/courses', function() {
    CourseController::search();
});

$routes->get('/course/:id', function($id) {
    CourseController::viewCourse($id);
});

$routes->get('/addcourse', function() {
    CourseController::addCourse();
});

$routes->post('/addcourse', function() {
    echo "<pre>";
    var_dump($_POST);
    echo "</pre>";
});


