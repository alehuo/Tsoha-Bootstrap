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
    CourseController::addCourseForm();
});

$routes->post('/addcourse', function() {
    CourseController::addCourse();
});


