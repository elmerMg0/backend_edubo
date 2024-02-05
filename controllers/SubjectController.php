<?php

namespace app\controllers;

use app\models\Avance;
use app\models\Clase;
use app\models\Pregunta;
use app\models\Recurso;
use app\models\Subject;
use app\models\SubjectLikes;
use Exception;
use Yii;

class SubjectController extends \yii\web\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'create' => ['POST'],
                'update-likes' => ['POST'],
                'get-subject' => ['GET'],
                'quiz' => ['GET'],
                'subjects' => ['GET'],
            ]
        ];
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['options']
        ];

        $behaviors['access'] = [
            'class' => \yii\filters\AccessControl::class,
            'only' => ['get-subject', 'create', 'update', 'update-likes', 'quiz', 'subjects'], // acciones a las que se aplicará el control
            'except' => [''],    // acciones a las que no se aplicará el control
            'rules' => [
                [
                    'allow' => true, // permitido o no permitido
                    'actions' => ['get-subject', 'create', 'update', 'update-likes', 'quiz', 'subjects'], // acciones que siguen esta regla
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
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionGetSubject($nroClass, $slugSubject, $idCourse)
    {
        $subject = Subject::find()
            ->select(['subject.*'])
            ->innerJoin('clase', 'clase.id = subject.clase_id')
            ->innerJoin('curso', 'curso.id = clase.curso_id')
            ->where(['curso.id' => $idCourse, 'numero_clase' => $nroClass, 'subject.slug' => $slugSubject])
            ->one();

        $resource = Recurso::find()
            ->select(['recurso.descripcion', 'recurso.id'])
            ->where(['subject_id' => $subject['id']])
            ->all();
        $views = 0;
        $likes = 0;
        if ($subject) {
            $views = Avance::find()
                ->where(['subject_id' => $subject['id']])
                ->count();

            $likes = SubjectLikes::find()
                ->where(['subject_id' => $subject['id']])
                ->count();
        }

        if ($subject) {
            $response = [
                'success' => true,
                'message' => 'Lista de Cursos',
                'data' => [
                    'subject' =>  $subject,
                    'views' => $views,
                    'likes' => $likes,
                    'resources' => $resource,
                ]
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Clase no encontrada'
            ];
        }

        return $response;
    }

    public function actionQuiz($idCourse, $nroClass)
    {
        $class = Clase::find()
            ->innerJoin('curso', 'curso.id = clase.curso_id')
            ->where(['curso.id' => $idCourse, 'numero_clase' => $nroClass])
            ->one();

        $questions = Pregunta::find()
            ->where(['clase_id' => $class->id])
            ->with(['responses' => function ($query) {
                $query
                    ->select(['response.description', 'response.id', 'response.pregunta_id', 'response.slug'])
                    ->orderBy(['response.slug' => SORT_ASC]);
            }])
            ->asArray()
            ->all();
        $response = [
            'success' => true,
            'message' => 'Lista de Cursos',
            'data' => [
                'classe' =>  $class,
                'questions' => $questions,
            ]
        ];

        return $response;
    }


    public function actionUpdateLikes($idSubject, $idStudent)
    {
        $record = SubjectLikes::find()->where(['subject_id' => $idSubject, 'usuario_id' => $idStudent])->one();
        if ($record) {
            try {
                $record->delete();
                $response = [
                    'success' => true,
                    'message' => 'Deleted register'
                ];
            } catch (Exception $e) {
                $response = [
                    'success' => true,
                    'message' => 'Ocurrio un error'
                ];
            }
        } else {
            $record = new SubjectLikes();
            $record->subject_id = $idSubject;
            $record->usuario_id = $idStudent;
            $record->save();
            $response = [
                'success' => true,
                'message' => 'Register created'
            ];
        }
        return $response;
    }

    /* get subject by id clase */

    public function actionSubjects($idClass)
    {
        $subjects = Subject::find()
            ->select(['subject.*'])
            ->innerJoin('clase', 'clase.id = subject.clase_id')
            ->where(['clase.id' => $idClass])
            ->orderBy(['slug' => SORT_ASC])
            ->all();

        $response = [
            'success' => true,
            'message' => 'Lista de Cursos',
            'data' => [
                'subjects' =>  $subjects
            ]
        ];
        return $response;
    }

    public function actionCreate()
    {
        $params = Yii::$app->getRequest()->getBodyParams();
        $subject = new Subject();
        $subject->load($params, "");
        try {
            if ($subject->save()) {
                //todo ok
                Yii::$app->getResponse()->setStatusCode(201);
                $response = [
                    "success" => true,
                    "message" => "Subclase agreado exitosamente",
                    'cliente' => $subject
                ];
            } else {
                //Cuando hay error en los tipos de datos ingresados 
                Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                $response = [
                    "success" => false,
                    "message" => "Existen parametros incorrectos",
                    'errors' => $subject->errors
                ];
            }
        } catch (Exception $e) {
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

    public function actionUpdate($idSubject)
    {
        $subject = Subject::findOne($idSubject);
        if ($subject) {
            $params = Yii::$app->getRequest()->getBodyParams();
            $subject->load($params, '');
            try {

                if ($subject->save()) {
                    $response = [
                        'success' => true,
                        'message' => 'Subclase actualizada correctamente',
                        'subject' => $subject
                    ];
                } else {
                    Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                    $response = [
                        'success' => false,
                        'message' => 'Existe errores en los campos',
                        'error' => $subject->errors
                    ];
                }
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error' => $e
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Clase no encontrado',
            ];
        }
        return $response;
    }
}
