<?php

namespace app\controllers;

use app\models\Usuario;
use Exception;
use Yii;
use yii\data\Pagination;
use yii\helpers\Json;
use yii\web\UploadedFile;

class UsuarioController extends \yii\web\Controller
{
    public function behaviors(){
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'login' => [ 'POST' ],
                'create-user'=>['POST'],
                'edit-user'=>['POST'],
                'get-all-users'=>['GET']

            ]
         ];

       /*   $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['options']
        ];

        $behaviors['access'] = [
            'class' => \yii\filters\AccessControl::class,
            'only' => ['index', 'create-user', 'edit-user', 'get-all-users'], // acciones a las que se aplicará el control
            'except' => [''],    // acciones a las que no se aplicará el control
            'rules' => [
                [
                    'allow' => true, // permitido o no permitido
                    'actions' => ['index', 'create-user', 'edit-user', 'get-all-users'], // acciones que siguen esta regla
                    'roles' => ['administrador'] // control por roles  permisos
                ],
                [
                    'allow' => true, // permitido o no permitido
                    'actions' => ['get-all-users'], // acciones que siguen esta regla
                    'roles' => ['cajero'] // control por roles  permisos
                ],
            ],
        ]; */


        return $behaviors;
    }

    public function beforeAction( $action ) {
        if (Yii::$app->getRequest()->getMethod() === 'OPTIONS') {         	
            Yii::$app->getResponse()->getHeaders()->set('Allow', 'POST GET PUT');         	
            Yii::$app->end();     	
        }     
        $this->enableCsrfValidation = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

   
  /*   public function actionCreateUser(){
        //$params = Yii::$app->getRequest()->getBodyParams();
        $user = new Usuario();
        $file = UploadedFile::getInstanceByName('file');
        $data = Json::decode(Yii::$app->request->post('data'));

        // $data = Json::decode(Yii::$app->request->post('data'));
        if($file){
            $fileName = uniqid() . '.' . $file->getExtension();
            $file->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
            $user->url_image = $fileName;
        }
        try{
  
            $user->nombre = $data["name"];
            $user->apellido = $data["lastName"];
            $user->email = $data["email"];
            $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash($data["password"]);
            $user->access_token = Yii::$app->security->generateRandomString();
            $user->tipo = $data["type"];
            $user->active = $data["active"];

            if($user->save()){
                $auth = Yii::$app->authManager;
                $role = $auth->getRole($data['tipo']);
                $auth -> assign($role, $user -> id);
                Yii::$app->getResponse()->getStatusCode(201);
                $response = [
                    'success'=> true,
                    'message'=> 'Usuario registrado con exito',
                    'usuario'=>$user
                ];
                
            }else{
                Yii::$app->getResponse()->setStatusCode(422,'Data Validation Failed.');
                $response = [
                    'success' => false,
                    'message' => 'Parametros incorrectos',
                    'usuario' => $user->errors,
                ];
            }
        } catch(Exception $e){
            Yii::$app->getResponse()->getStatusCode(500);
            $response = [
                'success'=>false,
                'message'=> 'Error registrando el usuario',
                'errors'=> $e->getMessage()
            ];
        }
        
        return $response;
    } */
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
    public function actionEditUser($id){
        $user = Usuario::findOne($id);
        $auth = Yii::$app->authManager;
        if ($user) {
            $data = JSON::decode(Yii::$app->request->post('data'));
            $user->load($data, '');
            $roleAssigment = $auth -> getRolesByUser($id);
            foreach($roleAssigment as $role){
                $auth -> revoke($role, $id);
            }
            $newRole = $auth->getRole($data['tipo']);
            $auth -> assign($newRole, $id);
            
           /*  if(isset($data["password"])){
                $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash($data["password"]);
            }            
            $
            $user->access_token = Yii::$app->security->generateRandomString(); */
            $image = UploadedFile::getInstanceByName('file');
            if ($image) {
                $url_image = $user->url_image;
                $imageOld = Yii::getAlias('@app/web/upload/' . $url_image);
                if(file_exists($imageOld) && $url_image){
                    unlink($imageOld);
                    /* Eliminar */
                }
                $fileName = uniqid().'.'.$image->getExtension();
                $image->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
                $imageNew = Yii::getAlias('@app/web/upload/' . $fileName);
                if(file_exists($imageNew)){
                    $user -> url_image = $fileName;
                }else{
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
    public function actionIndex($name, $pageSize = 5){
        if($name === 'undefined')$name = null;
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
            'previus' => $currentPage == 1 ? null: $currentPage - 1,
            'count' => count($users),
            'page' => $currentPage,
            'start' => $pagination->getOffset(),
            'totalPages' => $totalPages,
            'users' => $users
            ]
        ];
        return $response;
    }
    public function actionGetAllUsers(){
        $users = Usuario::find()
                        ->select(['usuario.id', 'usuario.nombres', 'usuario.tipo', 'usuario.url_image', 'usuario.estado'])
                        ->all();
        if($users){
            $response = [
                'success'=>true,
                'users' => $users
            ];
        }else{
            $response = [
                'success'=>false,
                'message'=>'no hay usuarios'
            ];
        }
        return $response;
    }
    public function actionLogin(){
        $params = Yii::$app->getRequest()->getBodyParams();

        $infomationUser = $this -> getInfoUser($params['access_token']);

        $user = Usuario::find()->where(['email' => $infomationUser['email']]) -> one();

        if(!$user){
            $response = $this -> actionCreateUserWithExternalService($infomationUser);
        }else{
            /*The user is register  */
            $password = $infomationUser['id'];
            if(Yii::$app->security->validatePassword($password, $user->password_hash)){
                $response = [
                    'success' => true,
                    'message' => 'Login exitoso',
                    'infoUser' => [
                        'accessToken' => $user -> access_token,
                        'id' => $user -> id
                    ]
                ];
            }else{
                $response = [
                    'success' => false,
                    'message' => 'Contrasena incorrecta',
                ];
            }
        }
        return $response;
    }


    public function actionCreateUserWithExternalService($data){
        //$params = Yii::$app->getRequest()->getBodyParams();
            $user = new Usuario();
            $user->nombre = $data["given_name"];
            $user->apellido = $data["family_name"];
            $user->email = $data["email"];
            $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash($data["id"]);
            $user->access_token = Yii::$app->security->generateRandomString();

            if($user->save()){
                Yii::$app->getResponse()->getStatusCode(201);
                $response = [
                    'success' => true,
                    'message' => 'Login exitoso',
                    'infoUser' => [
                        'accessToken' => $user -> access_token,
                        'id' => $user -> id
                    ]
                ];
                
            }else{
                Yii::$app->getResponse()->setStatusCode(422,'Data Validation Failed.');
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
    public function actionDisableUser( $idUser ){
        $user = Usuario::findOne($idUser);
        if($user){
            $user -> estado = 'Inactivo';
            if($user -> save()){
                $response = [
                    'success' => true,
                    'message' => 'Usuario Innhabilitado',
                    'user' => $user
                ];
            }
        }else{
            $response = [
                'success' => false,
                'message' => 'No existe usuario',
                'user' => []
            ];
        }
        return $response;
    }


    private function verifyExpiredToken() {     	

        // Obtiene el token del usuario
        $token = $this->access_token;     
        
            // Obtiene las 3 partes del token en un array
        $tokenParts = explode(".", $token);    
        
        // Verifica si en token consta de 3 partes (si es un jwt) 	
        if(count($tokenParts)==3){         
        // Desencripta la parte del payload	
        $tokenPayload = base64_decode($tokenParts[1]);  
        // Obtenemos el payload como objeto       	
        $jwtPayload = json_decode($tokenPayload);         
        // Verificamos la vigencia del token	
        return time() > $jwtPayload->exp;     	
        } else {         	
        return true;     	
        } 	
    }

    public function actionGetTokenJwt()
    {
        $jwt = Yii::$app->jwt;
		$signer = $jwt->getSigner('HS256');
		$key = $jwt->getKey();
		$time = time();

		$jwtParams = Yii::$app->params['jwt'];

		return $jwt->getBuilder()
			->issuedBy($jwtParams['issuer'])
			->permittedFor($jwtParams['audience'])
			->identifiedBy($jwtParams['id'], true)
			->issuedAt($time)
			->expiresAt($time + $jwtParams['expire'])
			//->withClaim('uid', $user->userID)
			->getToken($signer, $key);
    
    }

    public function actionTest($token){
        return $this -> getInfoUser($token);
    }

    /* Login
       1. Search el correo  
        1.1. Si ya existe el correo validar contrasenia, devolver el token y name del servicio google

       2. Si no existe el correo crear, correo y id crear token.
        2.1 Devolver token y name del servicio

    */
    private static function getInfoUser($token){
        $uri = Yii::$app->params['apiGoogle'];
        $url = $uri.'?access_token='. $token;
        $crl = curl_init($url);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($crl, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . $token,
            'Content-Type: application/json',
        ));
        $res = curl_exec($crl);
        curl_close($crl);  
        if(!$res){
            die('Error');
        }
        $response = json_decode($res);
        return $response;
    }
}
