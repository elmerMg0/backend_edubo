<?php

namespace app\models;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{


    public static function tableName()	
    {    	
        return 'usuario';	
    }
    

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return  null;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $user = User::findOne(['access_token' => $token]);     	
        if ($user) {         	
            if (!$user->verifyExpiredToken()) {             	
                $user->access_token = null;             	
                return new static($user);         	
                }     	
            }     	
        return null;  
    }


    private function verifyExpiredToken() {     	
        $key = 'example_key';
        $jwt = $this->access_token; 
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        return $decoded->exp > time();
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        /* foreach (self::$users as $user) {
            if (strcasecmp($user['username'], $username) === 0) {
                return new static($user);
            }
        } */

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }
}
