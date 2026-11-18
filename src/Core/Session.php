<?php
declare(strict_types=1);

namespace Core;

final class Session
{
    // Tiempo máximo de inactividad. Se reserva para la etapa 6.
    private const INACTIVIDAD_MAX = 60;

    // ---------- Etapa 1 y 4: arranque ----------
    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Evita aceptar identificadores de sesión no inicializados por el servidor.
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');

        // En XAMPP con http (localhost) secure será false.
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    // ---------- Etapa 1: métodos básicos ----------
    public static function get(string $clave, mixed $defecto = null): mixed
    {
        return $_SESSION[$clave] ?? $defecto;
    }

    public static function set(string $clave, mixed $valor): void
    {
        $_SESSION[$clave] = $valor;
    }

    public static function has(string $clave): bool
    {
        return isset($_SESSION[$clave]);
    }

    public static function remove(string $clave): void
    {
        unset($_SESSION[$clave]);
    }

    // ---------- Etapa 1 y 2: cierre de sesión ----------
    public static function destruir(): void
    {
        // 1. Vaciar los datos de la sesión en la petición actual.
        $_SESSION = [];

        // 2. Caducar la cookie que identifica la sesión en el navegador.
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        // 3. Destruir los datos de sesión almacenados en el servidor.
        session_destroy();
    }

    // ---------- Etapa 4: regeneración del ID ----------
    public static function regenerar(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    // ---------- Etapa 5: mensajes flash ----------
    public static function setFlash(string $clave, string $mensaje): void
    {
        $_SESSION['_flash'][$clave] = $mensaje;
    }

    public static function getFlash(string $clave): ?string
    {
        $mensaje = $_SESSION['_flash'][$clave] ?? null;

        if (isset($_SESSION['_flash'][$clave])) {
            unset($_SESSION['_flash'][$clave]);
        }

        if (empty($_SESSION['_flash'])) {
            unset($_SESSION['_flash']);
        }

        return is_string($mensaje) ? $mensaje : null;
    }

    // ---------- Etapa 6: caducidad por inactividad (pendiente) ----------
    public static function comprobarCaducidad(): void
    {
        // Se implementará en la etapa 6.
    }
}
