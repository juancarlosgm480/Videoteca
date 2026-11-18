<?php
declare(strict_types=1);

namespace Controllers;

use Core\Pages;
use Core\Session;
use Models\UserModel;

/** Autenticación: comprueba credenciales y abre/cierra la sesión. */
final class AuthController
{
    // Etapa 5: los mensajes flash se leen aquí y se envían a la vista.
    public function showLogin(): void
    {
        $error = Session::getFlash('error');
        $info = Session::getFlash('info');

        (new Pages())->render('auth/login', [
            'error' => $error,
            'info' => $info,
        ]);
    }

    public function login(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $usuario = (new UserModel())->findByEmail($email);

        // Se muestra el mismo error si el correo no existe o la contraseña falla.
        if ($usuario === null || !password_verify($password, $usuario['hash'])) {
            Session::setFlash('error', 'Correo o contraseña incorrectos');
            redirect('login');
        }

        // Cambiar el identificador evita la fijación de sesión (etapa 4).
        Session::regenerar();
        Session::set('user_id', $usuario['id']);

        redirect('perfil');
    }

    public function logout(): void
    {
        Session::destruir();
        redirect('login');
    }
}
