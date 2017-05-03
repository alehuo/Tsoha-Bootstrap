<?php

class AdminController extends BaseController {

    /**
     * Pääkäyttäjäsivu
     */
    public static function showAdminPage() {
        View::make('admin.html');
    }

}
