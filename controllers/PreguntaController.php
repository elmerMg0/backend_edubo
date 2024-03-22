<?php

namespace app\controllers;

use app\models\Clase;
use app\models\Pregunta;
use Exception;
use Yii;
use yii\data\Pagination;
use yii\helpers\Json;
use yii\web\UploadedFile;

class PreguntaController extends \yii\web\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors["verbs"] = [
            "class" => \yii\filters\VerbFilter::class,
            "actions" => [
                'index' => ['get'],
                'create' => ['post'],
                'update' => ['put', 'post'],
                'delete' => ['delete'],
                'get-questions' => ['get'],
                'get-questions-by-class' => ['get'],
                'questions' => ['get'],
            ]
        ];
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['options']
        ];
        $behaviors['access'] = [
            'class' => \yii\filters\AccessControl::class,
            'only' => ['index', 'get-questions', 'create', 'update', 'get-questions-by-class', 'questions'], // acciones a las que se aplicará el control
            'except' => [''],    // acciones a las que no se aplicará el control
            'rules' => [
                [
                    'allow' => true, // permitido o no permitido
                    'actions' => ['index', 'get-questions', 'create', 'update', 'get-questions-by-class', 'questions'], // acciones que siguen esta regla
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

    public function actionIndex($name, $pageSize = 5)
    {
        if ($name === 'undefined') $name = null;
        $query = Pregunta::find()
            ->andFilterWhere(['LIKE', 'UPPER(nombre)',  strtoupper($name)]);

        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);

        $questions = $query
            ->orderBy('id DESC')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $currentPage = $pagination->getPage() + 1;
        $totalPages = $pagination->getPageCount();
        $response = [
            'success' => true,
            'message' => 'lista de preguntas',
            'pageInfo' => [
                'next' => $currentPage == $totalPages ? null  : $currentPage + 1,
                'previus' => $currentPage == 1 ? null : $currentPage - 1,
                'count' => count($questions),
                'page' => $currentPage,
                'start' => $pagination->getOffset(),
                'totalPages' => $totalPages,
            ],
            'questions' => $questions
        ];
        return $response;
    }

    public function actionGetQuestions()
    {
        $questions = Pregunta::find()
            ->where(['estado' => 'Activo'])
            ->orderBy(['id' => 'SORT_ASC'])
            ->all();

        if ($questions) {
            $response = [
                'success' => true,
                'message' => 'Lista de preguntas',
                'questions' => $questions,
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No existen preguntas',
                'questions' => [],
            ];
        }

        return $response;
    }

    public function actionCreate()
    {

        $question = new Pregunta();
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


    public function actionUpdate($idQuestion)
    {
        $question = Pregunta::findOne($idQuestion);
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

    public function actionGetQuestionsByClass($idClass)
    {
        $class = Clase::findOne($idClass);
        if ($class) {
            $questions = Pregunta::find()
                ->where(['estado' => true, 'clase_id' => $idClass])
                ->all();
            $response = [
                "success" => true,
                "message" => "Lista de pregutnas por clase",
                "category" => $class,
                "products" => $questions
            ];
        } else {
            Yii::$app->getResponse()->setStatusCode(404);
            $response = [
                "success" => false,
                "message" => "Categoria no encontrada",
            ];
        }
        return $response;
    }

    public function actionQuestions($idClass)
    {
        $questions = Pregunta::find()
            ->where(['clase_id' => $idClass])
            ->orderBy(['id' => SORT_DESC])
            ->all();
        $response = [
            "success" => true,
            "message" => "Lista de pregutnas por clase",
            "data" => [
                "questions" => $questions
            ]
        ];
        return $response;
    }

    public function actionQuestionsByCourse($idCourse){
        $questions = Pregunta::find()
            ->where(['curso_id' => $idCourse])
            ->orderBy(['id' => SORT_DESC])
            ->all();
        $response = [
            "success" => true,
            "message" => "Lista de pregutnas por clase",
            "data" => [
                "questions" => $questions
            ]
        ];
        return $response;
    }
}
