<?php

namespace app\controllers;

use app\models\Professor;
use Exception;
use Yii;
use yii\helpers\Json;
use yii\web\UploadedFile;

class ProfessorController extends \yii\web\Controller
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
                'get-category' => ['get'],

            ]
        ];
        $behaviors['authenticator'] = [         	
            'class' => \yii\filters\auth\HttpBearerAuth::class,         	
            'except' => ['options']     	
        ];

        $behaviors['access'] = [
            'class' => \yii\filters\AccessControl::class,
            'only' => ['index', 'create', 'update', 'professors'], // acciones a las que se aplicará el control
            'except' => [''],    // acciones a las que no se aplicará el control
            'rules' => [
                [
                    'allow' => true, // permitido o no permitido
                    'actions' => ['index', 'create', 'update', 'professors'], // acciones que siguen esta regla
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

    public function actionProfessors()
    {
        $professors = Professor::find()
                                ->orderBy(['id' => SORT_DESC])
                                ->all();

        $response = [
            'success' => true,
            'message' => 'Lista de Professores',
            'data' => [
                'professors' => $professors
            ]
        ];
        return $response;
    }

    public function actionIndex()
    {
        $professors = Professor::find()
                                ->orderBy(['id' => SORT_DESC])
                                ->where(['active' => true])
                                ->all();

        $response = [
            'success' => true,
            'message' => 'Lista de Professores',
            'data' => [
                'professors' => $professors
            ]
        ];
        return $response;
    }

    public function actionCreate()
    {

        $professor = new Professor();
        $file = UploadedFile::getInstanceByName('file');
        $data = Json::decode(Yii::$app->request->post('data'));

        if ($file) {
            $fileName = uniqid() . '.' . $file->getExtension();
            $file->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
            $professor->url_image = $fileName;
        }

        $professor->load($data, "");
        try {
            if ($professor->save()) {
                Yii::$app->getResponse()->setStatusCode(201);
                $response = [
                    "success" => true,
                    "message" => "Professor agreado exitosamente",
                    'cliente' => $professor
                ];
            } else {
                //Cuando hay error en los tipos de datos ingresados 
                Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                $response = [
                    "success" => false,
                    "message" => "Existen parametros incorrectos",
                    'errors' => $professor->errors
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

    public function actionUpdate($idProfessor)
    {
        $professor = Professor::findOne($idProfessor);
        if ($professor) {
            $data = Json::decode(Yii::$app->request->post('data'));
            $image = UploadedFile::getInstanceByName('file');
            if ($image) {
                $url_image = $professor->url_image;
                $imageOld = Yii::getAlias('@app/web/upload/' . $url_image);
                if (file_exists($imageOld) && $url_image) {
                    unlink($imageOld);
                    /* Eliminar */
                }
                $fileName = uniqid() . '.' . $image->getExtension();
                $image->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
                $imageNew = Yii::getAlias('@app/web/upload/' . $fileName);
                if (file_exists($imageNew)) {
                    $professor->url_image = $fileName;
                } else {
                    return $response = [
                        'success' => false,
                        'message' => 'Ocurrio un error!',
                    ];
                }
            }
            $professor->load($data, '');
            try {

                if ($professor->save()) {
                    $response = [
                        'success' => true,
                        'message' => 'Professor actualizada correctamente',
                        'professor' => $professor
                    ];
                } else {
                    Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                    $response = [
                        'success' => false,
                        'message' => 'Existe errores en los campos',
                        'error' => $professor->errors
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
                'message' => 'Professor no encontrado',
            ];
        }
        return $response;
    }
}
