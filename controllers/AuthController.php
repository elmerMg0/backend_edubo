<?php
// controllers/AuthController.php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use Firebase\JWT\JWT;

class AuthController extends Controller
{
    // ... otros métodos y propiedades ...

    public function actionGenerateToken($user_id)
    {
        $key = 'tu_clave_secreta'; // Reemplaza con una clave secreta segura
        $token = [
            'user_id' => $user_id,
            'exp' => time() + 3600, // Expira en 1 hora (ajusta según tus necesidades)
        ];

        $jwt = JWT::encode($token, $key, 'HS256');

        return ['token' => $jwt];
    }

    public function actionVerifyToken($token)
    {
        $key = 'tu_clave_secreta'; // Debe coincidir con la clave utilizada para generar el token

        try {
            $decoded = JWT::decode($token, $key, null);
            // Token válido
            return ['user_id' => $decoded->user_id];
        } catch (\Exception $e) {
            // Token inválido
            return ['error' => 'Token inválido'];
        }
    }
}
