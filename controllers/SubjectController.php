<?php

namespace app\controllers;

use app\models\Avance;
use app\models\Curso;
use app\models\Professor;
use app\models\Subject;
use app\models\SubjectLikes;
use Exception;
use Yii;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class SubjectController extends \yii\web\Controller
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
    public function actionIndex()
    {
        return $this->render('index');
    }


    public function actionGetSubject($nroClass , $slugSubject, $idCourse){
        $subject = Subject::find()  
                            ->select(['subject.*'])
                            ->innerJoin('clase', 'clase.id = subject.clase_id')
                            ->innerJoin('curso', 'curso.id = clase.curso_id')
                            ->where(['curso.id' => $idCourse, 'numero_clase' => $nroClass, 'subject.slug' => $slugSubject])
                            ->one();
     
        $views = 0;
        $likes = 0;
        if($subject){
            $views = Avance::find()
            ->where(['subject_id' => $subject['id']])
            ->count();
        
            $likes = SubjectLikes::find()
                    ->where(['subject_id' => $subject['id']])
                    ->count();
        }
            

        if($subject){
            $response = [
                'success' => true,
                'message' => 'Lista de Cursos',
                'data' => [
                    'subject' =>  $subject,
                    'views' => $views,
                    'likes' => $likes,
                ]
            ];
        }else{
            $response = [
                'success' => false,
                'message' => 'Clase no encontrada'
            ];
        }
        
        return $response;   
    }

    public function actionUpdateLikes($idSubject, $idStudent){
        $record = SubjectLikes::find()->where(['subject_id' => $idSubject, 'usuario_id' => $idStudent])->one(); 
        if($record){
            try{
                $record -> delete();
                $response = [
                    'success' => true,
                    'message' => 'Deleted register'
                ];
            }catch( Exception $e){
                $response = [
                    'success' => true,
                    'message' => 'Ocurrio un error'
                ];
            } 
        }else{
            $record = new SubjectLikes();
            $record -> subject_id = $idSubject;
            $record -> usuario_id = $idStudent;
            $record -> save();
            $response = [
                'success' => true,
                'message' => 'Register created'
            ];
        }
        return $response;
    }
}
