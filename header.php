<?php
session_start();

class Session {
    public static function setFlash($name, $message) {
        $_SESSION['flash'][$name] = $message;
    }

    public static function flash($name) {
        if (isset($_SESSION['flash'][$name])) {
            $msg = $_SESSION['flash'][$name];
            unset($_SESSION['flash'][$name]);
            return "<div class='alert'>{$msg}</div>";
        }
    }
}