<?php

namespace app\controllers;

use app\models\Usuario;
use Exception;
use Firebase\JWT\JWT;
use Yii;
use yii\data\Pagination;
use yii\helpers\Json;
use yii\web\UploadedFile;

class UsuarioController extends \yii\web\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => ['POST'],
                'login' => ['POST'],
                'create-user' => ['POST'],
                'edit-user' => ['POST'],
                'get-all-users' => ['GET'],
                'login-user' => ['POST'],
                'register' => ['POST']
            ]
        ];

        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['options', 'login-user', 'get-role', 'login', 'register']
        ];

        $behaviors['access'] = [
            'class' => \yii\filters\AccessControl::class,
            'only' => ['index', 'create-user', 'edit-user', 'get-all-users'], // acciones a las que se aplicará el control
            'except' => ['login-user', 'login', 'register'],    // acciones a las que no se aplicará el control
            'rules' => [
                [
                    'allow' => true, // permitido o no permitido
                    'actions' => ['index', 'create-user'], // acciones que siguen esta regla
                    'roles' => ['manager'] // control por roles  permisos
                ],
            ],
        ];

        return $behaviors;
    }

    public function beforeAction($action)
    {
        if (Yii::$app->getRequest()->getMethod() === 'OPTIONS') {
            Yii::$app->getResponse()->getHeaders()->set('Allow', 'POST GET PUT');
            Yii::$app->end();
        }
        $this->enableCsrfValidation = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }


    public function actionCreateUser()
    {
        $user = new Usuario();
        $file = UploadedFile::getInstanceByName('file');
        $data = Json::decode(Yii::$app->request->post('data'));

        // $data = Json::decode(Yii::$app->request->post('data'));
        if ($file) {
            $fileName = uniqid() . '.' . $file->getExtension();
            $file->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
            $user->url_image = $fileName;
        }
        try {
            $user->nombre = $data["nombre"];
            $user->apellido = $data["apellido"];
            $user->email = $data["email"];
            $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash($data["password"]);
            //$user->access_token = Yii::$app->security->generateRandomString();
            $user->type = $data["type"];
            $user->plan_id = $data["planId"];
            $user->puntos = $data["puntos"];

            if ($user->save()) {
                $auth = Yii::$app->authManager;
                $role = $auth->getRole($data['type']);
                $auth->assign($role, $user->id);
                Yii::$app->getResponse()->getStatusCode(201);
                $response = [
                    'success' => true,
                    'message' => 'Usuario registrado con exito',
                    'usuario' => $user
                ];
            } else {
                Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
                $response = [
                    'success' => false,
                    'message' => 'Parametros incorrectos',
                    'usuario' => $user->errors,
                ];
            }
        } catch (Exception $e) {
            Yii::$app->getResponse()->getStatusCode(500);
            $response = [
                'success' => false,
                'message' => 'Error registrando el usuario',
                'errors' => $e->getMessage()
            ];
        }

        return $response;
    }
    /*  public function actionDeleteUser($id){
        $params= Usuario::findOne($id);
        if($params){
            try{
                $url_image = $params->url_image;
                $params->delete();
                $pathFile = Yii::getAlias('@webroot/upload/'.$url_image);
                unlink($pathFile);
                $response = [
                    'success'=>true,
                    'message'=>'User deleted'
                ];
            }catch(Exception $e){
                Yii::$app->getResponse()->getStatusCode(409);
                $response = [
                    'success'=> false,
                    'message'=>'Elimination failed',
                    'code'=>$e->getCode()
                ];
            }catch(Exception $e){
                Yii::$app->getResponse()->setStatusCode(422,'Data validation failed');
                $response = [
                    'success' => false,
                    'message'=>$e->getMessage(),
                    'code' => $e->getCode()
                ];
        }
        }else{
            Yii::$app->getResponse()->getStatusCode(404);
            $response = [
                'success' => false,
                'message' => 'user not found',
                
            ];
        }
        return $response;
    } */
    public function actionEditUser($id)
    {
        return;
        $user = Usuario::findOne($id);
        $auth = Yii::$app->authManager;
        if ($user) {
            $data = JSON::decode(Yii::$app->request->post('data'));
            $user->load($data, '');
            $roleAssigment = $auth->getRolesByUser($id);
            foreach ($roleAssigment as $role) {
                $auth->revoke($role, $id);
            }
            $newRole = $auth->getRole($data['tipo']);
            $auth->assign($newRole, $id);

            /*  if(isset($data["password"])){
                $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash($data["password"]);
            }            
            $
            $user->access_token = Yii::$app->security->generateRandomString(); */
            $image = UploadedFile::getInstanceByName('file');
            if ($image) {
                $url_image = $user->url_image;
                $imageOld = Yii::getAlias('@app/web/upload/' . $url_image);
                if (file_exists($imageOld) && $url_image) {
                    unlink($imageOld);
                    /* Eliminar */
                }
                $fileName = uniqid() . '.' . $image->getExtension();
                $image->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
                $imageNew = Yii::getAlias('@app/web/upload/' . $fileName);
                if (file_exists($imageNew)) {
                    $user->url_image = $fileName;
                } else {
                    return $response = [
                        'success' => false,
                        'message' => 'Ocurrio un error!',
                    ];
                }
            }
            try {

                if ($user->save()) {

                    $response = [
                        'success' => true,
                        'message' => 'Usuario Actualizado',
                        'user' => $user
                    ];
                } else {
                    Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                    $response = [
                        'success' => false,
                        'message' => 'Existe errores en los campos',
                        'error' => $user->errors
                    ];
                }
            } catch (Exception $e) {
                Yii::$app->getResponse()->setStatusCode(500);
                $response = [
                    'success' => false,
                    'message' => 'Error de codigo',
                    'error' => $e->getMessage()
                ];
            }
        } else {
            Yii::$app->getResponse()->setStatusCode(404);
            $response = [
                'success' => false,
                'message' => 'Usuario no encontrado',
            ];
        }
        return $response;
    }
    public function actionIndex($name, $pageSize = 5)
    {
        return;
        if ($name === 'undefined') $name = null;
        $query = Usuario::find()
            ->select(['usuario.id', 'usuario.nombres', 'usuario.tipo', 'usuario.url_image', 'usuario.estado', 'usuario.username'])
            ->andFilterWhere(['LIKE', 'UPPER(nombres)',  strtoupper($name)]);

        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);

        $users = $query
            ->orderBy('id DESC')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $currentPage = $pagination->getPage() + 1;
        $totalPages = $pagination->getPageCount();
        $response = [
            'success' => true,
            'message' => 'lista de clientes',
            'pageInfo' => [
                'next' => $currentPage == $totalPages ? null  : $currentPage + 1,
                'previus' => $currentPage == 1 ? null : $currentPage - 1,
                'count' => count($users),
                'page' => $currentPage,
                'start' => $pagination->getOffset(),
                'totalPages' => $totalPages,
                'users' => $users
            ]
        ];
        return $response;
    }
    public function actionGetAllUsers()
    {
        return
            $users = Usuario::find()
            ->select(['usuario.id', 'usuario.nombres', 'usuario.tipo', 'usuario.url_image', 'usuario.estado'])
            ->all();
        if ($users) {
            $response = [
                'success' => true,
                'users' => $users
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'no hay usuarios'
            ];
        }
        return $response;
    }
    public function actionLogin()
    {
        $params = Yii::$app->getRequest()->getBodyParams();

        $infomationUser = $this->getInfoUser($params['access_token']);

        $user = Usuario::find()->where(['email' => $infomationUser->email])->one();

        if (!$user) {
            $response = $this->actionCreateUserWithExternalService($infomationUser);
        } else {
            /*The user is register  */
            $password = $infomationUser->id;
            if (Yii::$app->security->validatePassword($password, $user->password_hash)) {
                $user->access_token = $this->actionGetTokenJwt($user, $user -> nombre, $user -> apellido);
                $user -> save();

                $role = Yii::$app->authManager->getRolesByUser($user->id);
                $response = [
                    'success' => true,
                    'message' => 'Login exitoso',
                    'data' => [
                        'accessToken' => $user->access_token,
                        'id' => $user->id,
                        'subscribed' => count($role) === 2 ? true : false
                    ]
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Contrasena incorrecta',
                ];
            }
        }
        return $response;
    }

    public function actionLoginUser()
    {
        $params = Yii::$app->getRequest()->getBodyParams();
        $email = $params['email'];
        $user = Usuario::find()->where(['email' => $email])->one();
        $auth = Yii::$app->authManager;
        if ($user) {
            $password = $params['password'];
            if (Yii::$app->security->validatePassword($password, $user->password_hash)) {
                $role = $auth->getRolesByUser($user->id);

                $keyuser = Yii::$app->params['keyuser'];
                $currentTimestamp = time();
                $expirationTimestamp = $currentTimestamp + (2 * 24 * 60 * 60);
                $payload = [
                    'iss' => 'https://jevesoftd.tech/',
                    'aud' => 'https://dimasresprodbeta.netlify.app/',
                    'iat' => $currentTimestamp,
                    'nbf' => $currentTimestamp,
                    'exp' => $expirationTimestamp,
                    'id' => $user->id
                ];

                $jwt = JWT::encode($payload, $keyuser, 'HS256');
                $user->access_token = $jwt;
                $user->save();
                $role = $auth->getRolesByUser($user->id);
                $response = [
                    'success' => true,
                    'message' => 'Inicio de sesion correcto',
                    'accessToken' => $jwt,
                    'role' => $role,
                    'id' => $user->id
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Usuario o contraseñia incorrectos!',
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Usuario o contraseñia incorrectos!'
            ];
        }
        return $response;
    }

    private function actionCreateUserWithExternalService($data)
    {
        //$params = Yii::$app->getRequest()->getBodyParams();
        $user = new Usuario();
        $user->nombre = $data->given_name;
        $user->apellido = $data->family_name;
        $user->email = $data->email;
        $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash($data->id);
        $user->access_token = $this->actionGetTokenJwt($data , $data -> given_name, $data -> family_name);
        $user->plan_id = 1;
        $user->puntos = 20;
        if ($user->save()) {
            Yii::$app->getResponse()->getStatusCode(201);
            $auth = Yii::$app->authManager;
            $role = $auth->getRole($data['tipo']);
            $auth -> assign($role, $user -> id);
            $response = [
                'success' => true,
                'message' => 'Login exitoso',
                'data' => [
                    'accessToken' => $user->access_token,
                    'id' => $user->id
                ]
            ];
        } else {
            Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
            $response = [
                'success' => false,
                'message' => 'Parametros incorrectos',
                'usuario' => $user->errors,
            ];
        }
        return $response;
    }
    /* public function actionGetUsers( $pageSize = 5){
        $query = Usuario::find()
                        ->select(['usuario.id', 'usuario.nombres', 'usuario.tipo', 'usuario.url_image']);
        $pagination = new Pagination([
            'defaultPageSize'=> $pageSize,
            'totalCount' => $query->count()
        ]);
        $users = $query
                    ->offset($pagination->offset)
                    ->limit($pagination -> limit)
                    ->all();
        if($users){
            $currentPage = $pagination->getPage() + 1;
            $totalPages = $pagination->getPageCount();
            $response = [
            'success' => true,
            'message' => 'lista de clientes',
            'pageInfo' => [
                'next' => $currentPage == $totalPages ? null  : $currentPage + 1,
                'previus' => $currentPage == 1 ? null: $currentPage - 1,
                'count' => count($users),
                'page' => $currentPage,
                'start' => $pagination->getOffset(),
                'totalPages' => $totalPages,
            ],
            'users' => $users
            ];
        }else{
            $response = [
                'success' => false,
                'message' => 'No existen usuarios',
                'users' => $users
            ];
        }
        return $response;
    } */
    public function actionDisableUser($idUser)
    {
        return;
        $user = Usuario::findOne($idUser);
        if ($user) {
            $user->estado = 'Inactivo';
            if ($user->save()) {
                $response = [
                    'success' => true,
                    'message' => 'Usuario Innhabilitado',
                    'user' => $user
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'No existe usuario',
                'user' => []
            ];
        }
        return $response;
    }


    public function actionGetTokenJwt($data, $name, $lastname)
    {
        $key = Yii::$app->params['keyuser'];
        $payload = [
           /*  'id' => $data->id, */
            'name' => $name,
            'last_name' => $lastname,
            'email' => $data->email,
            'exp' => time() + 43200
        ];
        $jwt = JWT::encode($payload, $key, 'HS256');

        $cookie_name = 'jwt_token';
        $cookie_value = $jwt;
        $cookie_expiration = time() + 43200;
        $cookie_secure = isset($_SERVER['HTTPS']);
        $cookie_httponly = true;

        // Configurar la cookie
        setcookie(
            $cookie_name,
            $cookie_value,
            $cookie_expiration,
            '/',
            '', // Dominio (dejar en blanco para el dominio actual)
            $cookie_secure,
            $cookie_httponly
        );
        return $jwt;
    }

    private static function getInfoUser($token)
    {
        $uri = Yii::$app->params['apiGoogle'];
        $url = $uri . '?access_token=' . $token;
        $crl = curl_init($url);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($crl, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . $token,
            'Content-Type: application/json',
        ));
        $res = curl_exec($crl);
        curl_close($crl);
        if (!$res) {
            die('Error');
        }
        $response = json_decode($res);
        return $response;
    }

    public function actionRegister(){
        $data = Yii::$app -> getRequest() -> getBodyParams();
        $data = json_decode(json_encode($data));
        $user = new Usuario();
        $user->nombre = $data->firstName;
        $user->apellido = $data->lastName;
        $user->email = $data->email;
        $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash($data->password);
        $user->access_token = $this->actionGetTokenJwt($data, $data -> firstName, $data -> lastName );
        $user->plan_id = 1;
        $user->puntos = 20;
        if ($user->save()) {
               /* AGREGAR ROLE USER */ 
               $auth = Yii::$app->authManager;
               $role = $auth->getRole($data['tipo']);
               $auth -> assign($role, $user -> id);
               Yii::$app->getResponse()->getStatusCode(201);
            $response = [
                'success' => true,
                'message' => 'Login exitoso',
                'data' => [
                    'accessToken' => $user->access_token,
                    'id' => $user->id,
                    'subscribed' => false
                ]
            ];
        } else {
            Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
            $response = [
                'success' => false,
                'message' => 'Parametros incorrectos',
                'usuario' => $user->errors,
            ];
        }
        return $response;
    }
}
