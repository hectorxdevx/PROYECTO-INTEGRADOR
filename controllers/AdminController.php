<?php
class AdminController {
    public function dashboard() {
        require_once 'views/admin/dashboard.php';
    }

    public function usuarios() {
        require_once 'views/admin/usuarios.php';
    }
}
