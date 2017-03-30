<?php

class AdminController extends BaseController {

    public static function showAdminPage() {
        View::make('admin.html');
    }

}
