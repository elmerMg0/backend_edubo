<?php

namespace app\controllers;

use app\models\Avance;
use app\models\Curso;
use app\models\Inscripcion;
use app\models\Recurso;
use app\models\RoadUser;
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
            'only' => ['', 'create', 'update', 'update-likes', 'quiz', ''], // acciones a las que se aplicará el control
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
        /* Validacion ver si tiene alguna suscripcion, si la tiene validar que esa suscripcion sea activa y que la clase del curso este en esa suscripcion */
        $role = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        
        $subject = Subject::find()
            ->select(['subject.id', 'subject.slug', 'subject.is_public', 'subject.title', 'subject.clase_id', 'subject.duration', 'subject.views', 'subject.type', 'subject.video_url', 'subject.type'])
            ->innerJoin('clase', 'clase.id = subject.clase_id')
            ->innerJoin('curso', 'curso.id = clase.curso_id')
            ->where(['curso.id' => $idCourse, 'numero_clase' => $nroClass, 'subject.slug' => $slugSubject])
            ->one();

        if(!$subject -> is_public){
            if($role && count($role) > 1 && $role['studentpro']){
                /* Tiene suscripcion si, validar que la clase del curso este en esa suscripcion */
                    $course = Curso::findOne($idCourse);
                    $isEnrolledPath = RoadUser::find()
                        ->where(['ruta_aprendizaje_id' => $course -> ruta_aprendizaje_id,'finished' => false ,'usuario_id' => Yii::$app->user->getId()])
                        ->exists();
                    
                    $isEnrolledCourse = Inscripcion::find()
                        ->where(['curso_id' => $idCourse,'finished' => false ,'usuario_id' => Yii::$app->user->getId()])
                        ->exists();

                    if($isEnrolledPath || $isEnrolledCourse){
                    }else{
                        $subject -> video_url = null;
                    }
            }else{
                $subject -> video_url = null;
            }
        }

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

    private function extractYouTubeVideoId($videoUrl) {
        $regExp = '/^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
        preg_match($regExp, $videoUrl, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    private function getThumbnailUrl($urlVideo) {
            $videoId = $this -> extractYouTubeVideoId($urlVideo);
            $key = Yii::$app->params['keygoogle'];
            $url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet&id=' . $videoId . '&key=' . $key;
            $crl = curl_init($url);
            curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($crl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
            ));
            $res = curl_exec($crl);
            curl_close($crl);
            if (!$res) {
                die('Error');
            }
            $response = json_decode($res);
        return $response->items[0]->snippet->thumbnails->medium->url;
    }
    
    public function actionCreate()
    {
        $params = Yii::$app->getRequest()->getBodyParams();
        $subject = new Subject();
        $subject->load($params, "");
        try {
            $subject -> thumbnailurl = $this -> getThumbnailUrl($subject->video_url);
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
                $subject -> thumbnailurl = $this -> getThumbnailUrl($subject->video_url);   
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
