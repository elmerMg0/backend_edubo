<?php

namespace app\controllers;

use app\models\Avance;
use app\models\Clase;
use app\models\Comment;
use app\models\CommentLikes;
use app\models\Curso;
use app\models\Professor;
use app\models\RutaAprendizaje;
use app\models\SubjectLikes;
use Exception;
use Yii;

class ApiController extends \yii\web\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'classes' => ['GET'],
                'create' => ['POST'],
                'update' => ['POST'],
                'disable-class' => ['GET'],
                'get-class-with-questions' => ['GET'],
                'get-class-with-resources' => ['GET'],
                'get-class-progress' => ['GET'],
            ]
        ];
       /*  $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['options']
        ];

        $behaviors['access'] = [
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

    public function actionLearningPaths(){
        $learningPaths = RutaAprendizaje::find()->all();

        $response = [
            'success' => true,
            'message' => 'Lista de Rutas de Aprendizaje',
            'data' => [
                'roads' => $learningPaths
            ]
        ];
        return $response;
    }

    public function actionGetRoads($idRoad)
    {
        $idRoad = isset($idRoad) ? $idRoad : null;
        $courses = RutaAprendizaje::find()
            ->andFilterWhere(['id' => $idRoad])
            ->with('cursos')
            ->asArray()
            ->all();
        $response = [
            'success' => true,
            'message' => 'Lista de cursos por ruta de aprendizaje',
            'data' => [
                'courses' => $courses
            ]
        ];

        return $response;
    }

    public function actionGetRoadsWithCourses($idRoad)
    {
        $idRoad = isset($idRoad) ? $idRoad : null;
        $courses = Curso::find()
            ->select(['curso.*', 'professor.nickname as professor'])
            ->innerJoin('professor', 'curso.professor_id = professor.id')
            ->where(['ruta_aprendizaje_id' => $idRoad, 'active' => true])
            ->asArray()
            ->orderBy(['id' => SORT_DESC])
            ->all();
        $path = RutaAprendizaje::findOne($idRoad);
        $response = [
            'success' => true,
            'message' => 'Lista de cursos por ruta de aprendizaje',
            'data' => [
                'courses' => $courses,
                'pathInfo' => $path
            ]
        ];
        return $response;
    }

    public function actionCourse($idCourse)
    {
        $course = Curso::find()
            ->select(['curso.*'])
            ->where(['id' => $idCourse])
            ->one();
        if ($course) {

            $classes = Clase::find()
                ->where(['curso_id' => $idCourse, 'active' => true])
                ->with([
                    'subjects' => function ($query) {
                        $query->select(['subject.title', 'subject.slug', 'subject.id', 'subject.clase_id', 'subject.duration'])
                            ->orderBy(['slug' => SORT_ASC]); 
                    }
                ])
                ->asArray()
                ->orderBy(['numero_clase' => SORT_ASC])
                ->all();

            //$course = Curso::findOne($idCourse);
            $teacher = Professor::findOne($course->professor_id);

            $response = [
                'success' => true,
                'message' => 'Lista de Cursos',
                'data' => [
                    'course' => $course,
                    'classes' => $classes,
                    'professor' => $teacher
                ]
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Cursos no encontrados'
            ];
        }
        return $response;
    }
    
    public function actionUpdateProgress($idSubject, $idStudent)
    {
        $newProgress = new Avance();
        $newProgress->subject_id = $idSubject;
        $newProgress->usuario_id = $idStudent;
        try {
            if ($newProgress->save()) {
                return $newProgress;
            } else {
                return $newProgress->errors;
            }
        } catch (Exception $e) {
            return $e;
        }
        return $newProgress;
    }   

    public function actionGetClassProgress($idCourse, $idStudent, $slugSubject, $nroClase)
    {
        $progress = Avance::find()
            ->select(['subject_id'])
            ->innerJoin('subject', 'subject.id = avance.subject_id')
            ->innerJoin('clase', 'clase.id = subject.clase_id')
            ->innerJoin('curso', 'curso.id = clase.curso_id')
            ->where(['curso.id' => $idCourse, 'usuario_id' => $idStudent])
            ->all();

        $subjectLike = SubjectLikes::find()
            ->select(['subject_id'])
            ->innerJoin('subject', 'subject.id = subject_likes.subject_id')
            ->innerJoin('clase', 'clase.id = subject.clase_id')
            ->innerJoin('curso', 'curso.id = clase.curso_id')
            ->where([
                'curso.id' => $idCourse, 'usuario_id' => $idStudent, 'subject.slug' => $slugSubject,
                'clase.numero_clase' => $nroClase
            ])
            ->one();
        $isLiked = ($subjectLike) ? true : false;
        $response = [
            'success' => true,
            'message' => 'Informacion',
            'data' => [
                'progress' => $progress,
                'isLiked' => $isLiked
            ]
        ];
        return $response;
    }

    public function actionGetComments($idSubject, $idStudent)
    {
        $comments = Comment::find()
            ->select(['comment.num_comments', 'usuario.nombre as name', 'usuario.apellido as lastName', 'usuario.url_image as avatar', 'comment.id', 'comment.comment_text', 'comment.num_likes'])
            ->innerJoin('usuario', 'usuario.id = comment.usuario_id')
            ->where(['subject_id' => $idSubject, 'comment.comment_id' => null])
            ->with(['comments' => function ($query) {
                $query
                    ->select(['comment.num_likes', 'comment.comment_text', 'comment.id', 'comment.comment_id', 'usuario.nombre as name', 'usuario.apellido as lastName', 'usuario.url_image as avatar'])
                    ->innerJoin('usuario', 'usuario.id = comment.usuario_id')
                    ->orderBy(['num_likes' => SORT_DESC])
                    ->asArray();
            }])
            //->with('comments')
            ->orderBy(['num_likes' => SORT_DESC])
            ->asArray()
            ->all();

        /* Likes que se hizo en los comentarios de la subclase por el student */
        $commentLikesList = CommentLikes::find()
            ->select(['comment_likes.comment_id as comment_id'])
            ->innerJoin('comment', 'comment.id = comment_likes.comment_id')
            ->where(['comment.subject_id' => $idSubject, 'comment_likes.usuario_id' => $idStudent])
            ->all();
        $response = [
            'success' => true,
            'message' => 'Comentarios obtenidos',
            'data' => [
                'comments' => $comments,
                'commentLikesList' => $commentLikesList
            ]
        ];

        return $response;
    }

    public function actionUpdateLikes($idComment, $idStudent)
    {
        $record = CommentLikes::find()->where(['comment_id' => $idComment, 'usuario_id' => $idStudent])->one();
        $comment = Comment::find()->where(['id' => $idComment])->one();
        if ($record) {
            try {
                $comment->num_likes = $comment->num_likes - 1;
                $record->delete();
                $comment->save();
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
            $record = new CommentLikes();
            $record->comment_id = $idComment;
            $record->usuario_id = $idStudent;
            $record->save();
            $comment->num_likes = $comment->num_likes + 1;
            $comment->save();
            $response = [
                'success' => true,
                'message' => 'Register created'
            ];
        }
        return $response;
    }

    public function actionUpdateLikesSub($idSubject, $idStudent)
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
}
