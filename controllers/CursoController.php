<?php

namespace app\controllers;

use app\models\Curso;
use Exception;
use Yii;
use yii\data\Pagination;

class CursoController extends \yii\web\Controller
{
    public function behaviors(){
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => [ 'GET' ],
                'create'=>['POST'],
                'update'=>['POST'],
                'disable-course'=>['GET'],
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
        $query = Curso::find()
                    ->andFilterWhere(['LIKE', 'UPPER(nombre)',  strtoupper($name)]);

        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);

        $course = $query
            ->orderBy('id DESC')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $currentPage = $pagination->getPage() + 1;
        $totalPages = $pagination->getPageCount();
        $response = [
            'success' => true,
            'message' => 'lista de cursos',
            'pageInfo' => [
                'next' => $currentPage == $totalPages ? null  : $currentPage + 1,
                'previus' => $currentPage == 1 ? null : $currentPage - 1,
                'count' => count($course),
                'page' => $currentPage,
                'start' => $pagination->getOffset(),
                'totalPages' => $totalPages,
            ],
            'courses' => $course
        ];
        return $response;
    }

    public function actionCreate(){
        $params = Yii::$app->getRequest()->getBodyParams();
        $course = new Curso();
        $course -> load($params,"");
        try{
            if($course->save()){
                //todo ok
                Yii::$app->getResponse()->setStatusCode(201);
                $response = [
                    "success" => true,
                    "message" => "Curso agreado exitosamente",
                    'cliente' => $course
                ];
            }else{
                //Cuando hay error en los tipos de datos ingresados 
                Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                $response = [
                    "success" => false,
                    "message" => "Existen parametros incorrectos",
                    'errors' => $course->errors
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

    public function actionUpdate( $idCourse ){
        $course = Curso::findOne($idCourse);
        if($course){
            $params = Yii::$app->getRequest()->getBodyParams();
            $course -> load($params, '');
                try{

                    if($course->save()){
                        $response = [
                            'success' => true,
                            'message' => 'Curso actualizada correctamente',
                            'course' => $course
                        ];
                    }else{
                        Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                        $response = [
                            'success' => false,
                            'message' => 'Existe errores en los campos',
                            'error' => $course->errors
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
                'message' => 'Curso no encontrado',
            ];
        }
        return $response;
    }

    public function actionDisableCourse( $idCourse ){
        $course = Curso::findOne($idCourse);

        if($course){
            try{
                $course->active = false;
                if($course -> save()){
                    $response = [
                        "success" => true,
                        "message" => "Curso desabilitado correctamente",
                        "road" => $course
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
                "message" => "Curso no encontrado"
            ];
        }
        return $response;
    }
}
