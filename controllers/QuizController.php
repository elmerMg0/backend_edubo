<?php

namespace app\controllers;

use app\models\Quiz;
use Exception;
use Yii;
use yii\helpers\Json;
use yii\web\UploadedFile;

class QuizController extends \yii\web\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'quizzes' => ['GET'],
                'create' => ['POST'],
                'update' => ['POST'],
            ]
        ];
         $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['options']
        ];

        /* $behaviors['access'] = [
            'class' => \yii\filters\AccessControl::class,
            'only' => ['classes', 'create', 'disable-class', 'update', 'get-class-with-questions', 'get-class-with-resources', 'get-class-progress'], // acciones a las que se aplicará el control
            'except' => [''],    // acciones a las que no se aplicará el control
            'rules' => [
                [
                    'allow' => true, // permitido o no permitido
                    'actions' => ['classes', 'create', 'disable-class', 'update', 'get-class-with-questions', 'get-class-with-resources', 'get-class-progress'], // acciones que siguen esta regla
                    'roles' => ['manager'] // control por roles  permisos
                ],
            ],
        ]; */


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
    
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionQuizzes($id, $type){
        $quizzes = Quiz::find();

        if ($type === 'course') {
            $quizzes = $quizzes->where(['curso_id' => $id]);
        }
        $quizzes = $quizzes->all();

        $response = [
            'success' => true,
            'data' => [
                'quizzes' => $quizzes
            ]
        ];
        return $response;
    }

    public function actionCreate()
    {

        $question = new Quiz();
        $file = UploadedFile::getInstanceByName('file');
        $data = Json::decode(Yii::$app->request->post('data'));

        // $data = Json::decode(Yii::$app->request->post('data'));
        if ($file) {
            $fileName = uniqid() . '.' . $file->getExtension();
            $file->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
            $question->url_image = $fileName;
        }
        try {
            $question->load($data, '');
            if ($question->save()) {
                Yii::$app->getResponse()->setStatusCode(201);
                $response = [
                    'success' => true,
                    'message' => 'Pregunta creada exitosamente',
                    'fileName' => $question
                ];
            } else {
                Yii::$app->getResponse()->setStatusCode(422, "Data Validation Failed.");
                $response = [
                    'success' => false,
                    'message' => 'Existen errores en los campos',
                    'errors' => $question->errors
                ];
            }
        } catch (Exception $e) {
            Yii::$app->getResponse()->setStatusCode(500);
            $response = [
                'success' => false,
                'message' => 'ocurrio un error',
                'fileName' => $e->getMessage()
            ];
        }

        return $response;
    }


    public function actionUpdate($idQuiz)
    {
        $question = Quiz::findOne($idQuiz);
        if ($question) {
            $data = JSON::decode(Yii::$app->request->post('data'));
            $question->load($data, '');

            $image = UploadedFile::getInstanceByName('file');
            if ($image) {
                $url_image = $question->url_image;
                $imageOld = Yii::getAlias('@app/web/upload/' . $url_image);
                if (file_exists($imageOld) && $url_image) {
                    unlink($imageOld);
                    /* Eliminar */
                }
                $fileName = uniqid() . '.' . $image->getExtension();
                $image->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
                $imageNew = Yii::getAlias('@app/web/upload/' . $fileName);
                if (file_exists($imageNew)) {
                    $question->url_image = $fileName;
                } else {
                    return $response = [
                        'success' => false,
                        'message' => 'Ocurrio un error!',
                    ];
                }
            }

            try {

                if ($question->save()) {

                    $response = [
                        'success' => true,
                        'message' => 'Pregunta actualizado correctamente',
                        'question' => $question
                    ];
                } else {
                    Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                    $response = [
                        'success' => false,
                        'message' => 'Existe errores en los campos',
                        'error' => $question->errors
                    ];
                }
            } catch (Exception $e) {
                Yii::$app->getResponse()->setStatusCode(500);
                $response = [
                    'success' => false,
                    'message' => 'Pregunta no encontrado',
                    'error' => $e->getMessage()
                ];
            }
        } else {
            Yii::$app->getResponse()->setStatusCode(404);
            $response = [
                'success' => false,
                'message' => 'Pregunta no encontrado',
            ];
        }
        return $response;
    }

    public function actionQuizzesById($id, $type){
        if($type === 'course'){
            $quizzes = Quiz::find()
                            ->where(['curso_id' => $id])
                            ->all();
        }else{

        }

        $response = [
            'success' => true,
            'data' => [
                'quizzes' => $quizzes
            ]
        ];
    }
}
