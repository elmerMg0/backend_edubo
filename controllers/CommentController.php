<?php

namespace app\controllers;

use app\models\Comment;
use app\models\CommentLikes;
use Exception;
use Yii;

class CommentController extends \yii\web\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'create' => ['POST'],
                'comments' => ['POST'],
                'get-comments' => ['GET'],
                'update-likes' => ['GET'],
            ]
        ];
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['options']
        ];

        $behaviors['access'] = [
            'class' => \yii\filters\AccessControl::class,
            'only' => ['index', 'comments', 'get-comments', 'update-likes'], // acciones a las que se aplicará el control
            'except' => [''],    // acciones a las que no se aplicará el control
            'rules' => [
                [
                    'allow' => true, // permitido o no permitido
                    'actions' => ['index', 'comments', 'get-comments', 'update-likes'], // acciones que siguen esta regla
                    'roles' => ['administrador'] // control por roles  permisos
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
    public function actionCreate()
    {
        $params = Yii::$app->getRequest()->getBodyParams();
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
    public function actionComments()
    {
    }

   

   
}
