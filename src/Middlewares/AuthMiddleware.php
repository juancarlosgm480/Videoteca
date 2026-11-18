<?php
declare(strict_types=1);

namespace Middlewares;

use Core\Session;

final class AuthMiddleware
{
    /** Envuelve una acción con el middleware. */
    public static function proteger(array $accion): callable
    {
        return function (...$params) use ($accion) {
            (new self())->handle();
            $accion[0] = new $accion[0]();
            return call_user_func_array($accion, $params);
        };
    }

    public function handle(): void
    {
        if (!Session::has('user_id')) {
            Session::setFlash('error', 'Debes iniciar sesión para ver esta página');
            redirect('login');
        }
    }
}
