<?php

  $routes->get('/', function() {
    DefaultController::index();
  });

  $routes->get('/sandbox', function(){
    DefaultController::sandbox();
  });
