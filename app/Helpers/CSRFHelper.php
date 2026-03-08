<?php
namespace App\Helpers;

class CsrfHelper
{
    /**
     * Vygeneruje a uloží CSRF token do session, pokud ještě neexistuje.
     * 
     * @return string Token
     */
    public static function generate()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Ověří platnost tokenu.
     * 
     * @param string|null $token Token odeslaný z formuláře
     * @return bool True pokud je token platný
     */
    public static function verify($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Vygeneruje skrytý input s tokenem pro vložení do formuláře.
     * 
     * @return string HTML input
     */
    public static function formInput()
    {
        $token = self::generate();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}
