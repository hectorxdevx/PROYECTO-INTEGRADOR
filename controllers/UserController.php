<?php
class UserController {
    public function dashboard() {
        require_once 'views/user/dashboard.php';
    }

    public function perfil() {
        require_once 'views/user/perfil.php';
    }
}
