<?php

namespace app\controllers;

use app\models\Clase;
use app\models\Pregunta;
use Exception;
use Yii;
use yii\data\Pagination;

class ClaseController extends \yii\web\Controller
{
    public function behaviors(){
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => [ 'GET' ],
                'create'=>['POST'],
                'update'=>['POST'],
                'disable-road'=>['GET'],
            ]
         ];
        /*  $behaviors['authenticator'] = [
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

    public function actionIndex($name, $pageSize=5)
    {   
        if($name === 'undefined')$name = null;
        $query = Clase::find()
                    ->andFilterWhere(['LIKE', 'UPPER(nombre)',  strtoupper($name)]);

        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);

        $class = $query
            ->orderBy('id DESC')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $currentPage = $pagination->getPage() + 1;
        $totalPages = $pagination->getPageCount();
        $response = [
            'success' => true,
            'message' => 'lista de clases',
            'pageInfo' => [
                'next' => $currentPage == $totalPages ? null  : $currentPage + 1,
                'previus' => $currentPage == 1 ? null : $currentPage - 1,
                'count' => count($class),
                'page' => $currentPage,
                'start' => $pagination->getOffset(),
                'totalPages' => $totalPages,
                'classes' => $class
            ]
        ];
        return $response;
    }

    public function actionCreate(){
        $params = Yii::$app->getRequest()->getBodyParams();
        $class = new Clase();
        $class -> load($params,"");
        try{
            if($class->save()){
                //todo ok
                Yii::$app->getResponse()->setStatusCode(201);
                $response = [
                    "success" => true,
                    "message" => "Clase agreado exitosamente",
                    'cliente' => $class
                ];
            }else{
                //Cuando hay error en los tipos de datos ingresados 
                Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                $response = [
                    "success" => false,
                    "message" => "Existen parametros incorrectos",
                    'errors' => $class->errors
                ];
            }
        }catch(Exception $e){
        //cuando no se definen bien las reglas en el modelo ocurre este error, por ejemplo required no esta en modelo y en la base de datos si, 
        //existe incosistencia
            $response = [
                "success" => false,
                "message" => "ocurrio un error",
                'errors' => $e
            ];
        }
        return $response;
    }

    public function actionUpdate( $idClass ){
        $class = Clase::findOne($idClass);
        if($class){
            $params = Yii::$app->getRequest()->getBodyParams();
            $class -> load($params, '');
                try{

                    if($class->save()){
                        $response = [
                            'success' => true,
                            'message' => 'Clase actualizada correctamente',
                            'class' => $class
                        ];
                    }else{
                        Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                        $response = [
                            'success' => false,
                            'message' => 'Existe errores en los campos',
                            'error' => $class->errors
                        ];
                    }
                }catch(Exception $e){
                    $response = [
                        'success' => false,
                        'message' => $e -> getMessage(),
                        'error' => $e
                    ];
                }
        }else{
            $response = [
                'success' => false,
                'message' => 'Clase no encontrado',
            ];
        }
        return $response;
    }

    public function actionDisableClass( $idClass ){
        $class = Clase::findOne($idClass);

        if($class){
            try{
                $class->active = false;
                if($class -> save()){
                    $response = [
                        "success" => true,
                        "message" => "Clase desabilitado correctamente",
                        "class" => $class
                    ];
                }
            }catch(\Exception $e){
                Yii::$app->getResponse()->setStatusCode(422, "");
                $response = [
                    "success" => false,
                    "message" => $e->getMessage(),
                    "code" => $e->getCode()
                ];
            }
        }else{
            Yii::$app->getResponse()->setStatusCode(404);
            $response = [
                "success" => false,
                "message" => "Clase no encontrado"
            ];
        }
        return $response;
    }

    public function actionGetClassWithQuestions($name, $idClass, $pageSize = 5){
        if($name === 'undefined')$name = null;
        $query = Pregunta::find()
                    ->where(['clase_id' => $idClass])  
                    ->andFilterWhere(['LIKE', 'UPPER(nombre)',  strtoupper($name)]);

        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);

        $questions = $query
            ->orderBy('id DESC')
            ->asArray()
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $currentPage = $pagination->getPage() + 1;
        $totalPages = $pagination->getPageCount();
        $response = [
            'success' => true,
            'message' => 'lista de preguntas por curso',
            'pageInfo' => [
                'next' => $currentPage == $totalPages ? null  : $currentPage + 1,
                'previus' => $currentPage == 1 ? null : $currentPage - 1,
                'count' => count($questions),
                'page' => $currentPage,
                'start' => $pagination->getOffset(),
                'totalPages' => $totalPages,
            ],
            'questionss' => $questions
        ];
        return $response;
    }

}
