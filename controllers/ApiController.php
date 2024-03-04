<?php

namespace app\controllers;

use app\models\Avance;
use app\models\Clase;
use app\models\Comment;
use app\models\CommentLikes;
use app\models\Curso;
use app\models\Inscripcion;
use app\models\Plan;
use app\models\Pregunta;
use app\models\Professor;
use app\models\Response;
use app\models\RoadUser;
use app\models\RutaAprendizaje;
use app\models\Subject;
use app\models\SubjectLikes;
use Exception;
use Yii;
use DateInterval;
use DateTime;

class ApiController extends \yii\web\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'learning-paths' => ['GET'],
                'get-roads' => ['GET'],
                'get-roads-with-courses' => ['GET'],
                'course' => ['GET'],
                'update-progress' => ['GET'],
                'get-comments' => ['GET'],
                'update-likes' => ['GET'],
                'update-likes-sub' => ['GET'],
                'quiz' => ['GET'],
                'check' => ['GET'],
                'create' => ['POST']
            ]
        ];
         $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['options', 'learning-paths', 'plans']
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
            ->where(['ruta_aprendizaje_id' => $idRoad, 'curso.active' => true])
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
                        $query->select(['subject.title', 'subject.slug', 'subject.id', 'subject.clase_id', 'subject.duration', 'subject.type'])
                            ->orderBy(['slug' => SORT_ASC]); 
                    }
                ])
                ->asArray()
                ->orderBy(['numero_clase' => SORT_ASC])
                ->all();
            
            $isEnrolledPath = RoadUser::find()
                ->where(['ruta_aprendizaje_id' => $course -> ruta_aprendizaje_id,'finished' => false ,'usuario_id' => Yii::$app->user->getId()])
                ->exists();
            
            $isEnrolledCourse = Inscripcion::find()
                ->where(['curso_id' => $idCourse,'finished' => false ,'usuario_id' => Yii::$app->user->getId()])
                ->exists();

            $subscribed = false;
            if($isEnrolledPath || $isEnrolledCourse){
                $subscribed = true;
            }   

            //$course = Curso::findOne($idCourse);
            $teacher = Professor::findOne($course->professor_id);

            $response = [
                'success' => true,
                'message' => 'Lista de Cursos',
                'data' => [
                    'course' => $course,
                    'classes' => $classes,
                    'professor' => $teacher,
                    'subscribed' => $subscribed
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

    public function actionGetComments($idSubject, $idStudent, $nroClass)
    {
        $subject = Subject::find()->
                                innerJoin('clase', 'clase.id = subject.clase_id')->
                                where(['subject.slug' => $idSubject, 'clase.numero_clase' => $nroClass]) -> one();
        $comments = Comment::find()
            ->select(['comment.num_comments', 'usuario.nombre as name', 'usuario.apellido as lastName', 'usuario.url_image as avatar', 'comment.id', 'comment.comment_text', 'comment.num_likes'])
            ->innerJoin('usuario', 'usuario.id = comment.usuario_id')
            ->where(['subject_id' => $subject -> id, 'comment.comment_id' => null])
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

    public function actionCheck($idResponse)
    {
        $answer = Response::findOne($idResponse);
        $answerCorrect = $answer->description;
        if (!$answer->is_correct) {
            $answerCorrect = Response::find()
                ->where(['pregunta_id' => $answer->pregunta_id])
                ->andWhere(['is_correct' => true])
                ->one();
            $answerCorrect = $answerCorrect->description;
        }

        $response = [
            "success" => true,
            "message" => "Respuesta",
            "data" => [
                "is_correct" => $answer->is_correct ? true : false,
                'answer' => $answerCorrect
            ]
        ];
        return $response;
    }

    public function actionPlans($idRoad, $idCourse){
        $plansCourse = [];
        $course = null;
        if($idCourse){
            $plansCourse = Plan::find()
                                ->select(['plan.id', 'plan.nombre', 'plan.precio_total', 'plan.duracion', 'plan.benefit', 'course_plan.course_id'])
                                ->where(['course_id' => $idCourse])
                                ->innerJoin('course_plan', 'plan.id =  course_plan.plan_id')
                                ->asArray()
                                ->all();
            $course = Curso::find()->select(['id', 'name', 'ruta_aprendizaje_id']) -> where(['id' => $idCourse]) -> one();
            $idRoad = $course -> ruta_aprendizaje_id;
        }

        $plansRoad = Plan::find()
                        ->select(['plan.id', 'plan.nombre', 'plan.precio_total', 'plan.duracion', 'plan.benefit', 'road_plan.ruta_aprendizaje_id'])
                        ->innerJoin('road_plan', 'plan.id = road_plan.plan_id')
                        ->where(['ruta_aprendizaje_id' => $idRoad, 'plan.active' => true])
                        ->asArray()
                        ->all();
        $path = RutaAprendizaje::findOne($idRoad);

        $response = [
            'success' => true,
            'message' => 'List of plans',
            "data" => [
                "plansRoad" => $plansRoad,
                'path' => $path,
                'course' => $course,
                'plansCourse' => $plansCourse
            ]
        ];
        return $response;                 
    }

    public function actionEnroll(){
        /* Plan elegido, estudiante, curso o ruta */
        try{
            $params = Yii::$app -> getRequest() -> getBodyParams();
            $enrollment = $this -> enrollFactory($params['type'], $params['id']);//id can be course or path
            $enrollment -> usuario_id = $params['student'];
            $enrollment -> plan_id = $params['plan'];
            $enrollment -> expire_date = $this -> incrementarMeses($params['quantity']);
            $enrollment -> create_ts = date('Y-m-d H:i:s');
            $enrollment -> months = $params['quantity'];
            $enrollment -> finished = false;
            $enrollment -> save();
            $auth = Yii::$app->authManager;
            $role = $auth->getRole('studentpro');
            $auth -> assign($role, $params['student']);
            $response = [
                'success' => true,
                'message' => 'Register created'
            ];
    
        }catch(Exception $e){
            $response = [
                'success' => false,
                'message' => 'Ocurrio un error '.$e
            ];
        }
      
        return $response;
    }

    private function incrementarMeses($n) {
        // Crear un objeto DateTime con la fecha proporcionada
        $fecha = new DateTime();
    
        // Incrementar la cantidad de meses
        $fecha->add(new DateInterval("P{$n}M"));
    
        // Obtener la nueva fecha
        $nuevoAnio = $fecha->format('Y');
        $nuevoMes = $fecha->format('m');
        $nuevoDia = $fecha->format('d');
    
        // Devolver la nueva fecha como arreglo asociativo
      /*   return [
            'anio' => $nuevoAnio,
            'mes' => $nuevoMes,
            'dia' => $nuevoDia
        ]; */
        return $nuevoAnio . '-' . $nuevoMes . '-' . $nuevoDia;
    }
 
    private function enrollFactory($type, $id){
        $enrollment = null;
        switch($type){
            case 'course':
                $enrollment = new Inscripcion();
                $enrollment -> curso_id = $id;
                break;
            case 'path':
                $enrollment = new RoadUser();
                $enrollment -> ruta_aprendizaje_id = $id;
                break;
        }
        return $enrollment;
    }
    public function actionCreate()
    {
        $params = Yii::$app->getRequest()->getBodyParams();
        $api_key = Yii::$app->params['keygpt'];
        
        $api_url = 'https://api.openai.com/v1/chat/completions';  
        // Parámetros de la solicitud
        $temperature = 0.0;
        $max_tokens = 60;
        $top_p = 1.0;
        $frequency_penalty = 0.5;
        $presence_penalty = 0.0;
        
        // Texto de entrada para la solicitud
        $input_text = 'Diseña un sistema de clasificación de sentimientos para evaluar respuestas a preguntas en el área de matemáticas y ciencias. La tarea principal es determinar si el comentario es positivo, negativo o neutral. La API debe manejar consultas sobre temas específicos como álgebra, cálculo, biología, química y física.

        Ten en cuenta las siguientes pautas:
        
        Si el mensaje contiene elogios, aprobación o expresiones positivas relacionadas con álgebra, cálculo, biología, química o física, clasifícalo como "positivo".
        Si el mensaje tiene críticas, desaprobación o expresiones negativas relacionadas con álgebra, cálculo, biología, química o física, clasifícalo como "negativo".
        Si el mensaje no contiene ninguna carga emocional o valoración sobre los temas mencionados, clasifícalo como "neutral".
        Si la pregunta se refiere a temas ajenos a álgebra, cálculo, biología, química o física, clasifícalo automáticamente como "negativo".
        Ejemplo de interacción:
        Usuario: "Me encanta cómo resuelven ecuaciones en este sitio. ¡Son geniales!"
        positivo
        
        Usuario: "No entiendo nada de química, esto es una pérdida de tiempo."
        negativo
        
        Usuario: "¿Cuál es la fórmula química del agua?"
        neutral
        
        Usuario: "¿Cuánto cuesta el último iPhone?"
        negativo
        El mensaje proporcionado por el usuario es el siguiente: '. $params['comment_text'];
        $model = 'gpt-3.5-turbo';          
        // Construir el cuerpo de la solicitud
        $data = array(
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $input_text
                ]
            ],
            'temperature' => $temperature,
            'max_tokens' => $max_tokens,
            'top_p' => $top_p,
            'frequency_penalty' => $frequency_penalty,
            'presence_penalty' => $presence_penalty,
            'model' => $model,
        );

        // Convertir los datos a formato JSON
        $json_data = json_encode($data);

        // Configurar la solicitud HTTP
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
        ));

        // Configurar opciones adicionales si es necesario
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud
        $response = curl_exec($ch);
        $json = json_decode($response, true);
        $responseAPi = $json['choices'][0]['message']['content']; 
        if($responseAPi == 'negativo'){
            return [
                "success" => false,
                "message" => "No se pudo enviar el comentario",
                "comment" => $json
            ];
        }

        $comment = new Comment();
        $comment->load($params, "");
        $comment_id = $params['comment_id'] ?? null;
        if ($comment_id) {
            $comment->comment_id = $comment_id;
            $commentParent = Comment::findOne($comment_id);
            $commentParent->num_comments = $commentParent->num_comments + 1;
            $commentParent->save();
        }
        try {
            $subject = Subject::find()
                                ->where(['subject.slug' => $params['slugSubject'], 'numero_clase' => $params['nroClass'], 'curso.id' => $params['courseId']])
                                ->innerJoin('clase', 'clase.id = subject.clase_id')
                                ->innerJoin('curso', 'curso.id = clase.curso_id')
                                ->one();
            $comment -> subject_id = $subject->id;
            if ($comment->save()) {
                //todo ok
                Yii::$app->getResponse()->setStatusCode(201);
                $response = [
                    "success" => true,
                    "message" => "Comentario agreado exitosamente",
                    'cliente' => $comment
                ];
            } else {
                //Cuando hay error en los tipos de datos ingresados 
                Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                $response = [
                    "success" => false,
                    "message" => "Existen parametros incorrectos",
                    'errors' => $comment->errors
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

    public function actionRecentClass(){
        $params = Yii::$app -> getRequest() -> getBodyParams();
        $enrollment = RoadUser::find()->where(['usuario_id' => 10, 'finished' => false]) -> all();
        $recentClasses = [];
        if($enrollment){
            foreach($enrollment as $enrollment){
                $learningPath = RutaAprendizaje::find()->where(['id' => $enrollment->ruta_aprendizaje_id])->one();
                $courses = Curso::find()->where(['ruta_aprendizaje_id' => $learningPath->id, 'active' => true])->all();
                foreach($courses as $course){
                    $infoCourse = Curso::find()
                                    ->select(['curso.id','avance.create_ts', 'curso.url_image', 'curso.name', 'curso.slug', 'curso.id as courseId', 'clase.numero_clase', 'subject.slug as subjectSlug', 'subject.title', 'subject.thumbnailurl'])
                                    ->where(['curso.id' => $course->id, 'avance.usuario_id' => $params['idStudent']]) 
                                    ->innerJoin('clase', 'clase.curso_id = curso.id')
                                    ->innerJoin('subject', 'subject.clase_id = clase.id')
                                    ->innerJoin('avance', 'avance.subject_id = subject.id')
                                    ->orderBy(['avance.create_ts' => SORT_DESC])
                                    ->asArray()
                                    ->one();
                    if($infoCourse){
                        $infoCourse['learningPathSlug'] = $learningPath -> id . '-' . $learningPath -> slug;
                        $recentClasses[] = $infoCourse;
                    }
                }
            }

            $enrollmentCourses = Inscripcion::find()->where(['usuario_id' => 10, 'finished' => false]) -> all();
            if($enrollmentCourses){
                foreach($enrollmentCourses as $enrollmentCourse){
                    $infoCourse = Curso::find()
                    ->select(['curso.id','avance.create_ts', 'curso.url_image', 'curso.name', 'curso.slug', 'curso.id as courseId', 'clase.numero_clase', 'subject.slug as subjectSlug', 'subject.title', 'subject.thumbnailurl'])
                    ->where(['curso.id' => $enrollmentCourse->curso_id, 'avance.usuario_id' => $params['idStudent']]) 
                    ->innerJoin('clase', 'clase.curso_id = curso.id')
                    ->innerJoin('subject', 'subject.clase_id = clase.id')
                    ->innerJoin('avance', 'avance.subject_id = subject.id')
                    ->orderBy(['avance.create_ts' => SORT_DESC])
                    ->asArray()
                    ->one();
                    if($infoCourse){
                        $infoCourse['learningPathSlug'] = $learningPath -> id . '-' . $learningPath -> slug;
                        $recentClasses[] = $infoCourse;
                    }
                    $recentClasses[] = $enrollmentCourse->curso_id;
                }
            }

        }
       

        $response = [
            'success' => true,
            'message' => 'Informacion',
            'data' => [
                'recentClasses' => $recentClasses
            ]
        ];
        return $response;
    }
}

