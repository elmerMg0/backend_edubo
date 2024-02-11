<?php

namespace app\controllers;

use app\models\Curso;
use app\models\RutaAprendizaje;
use Exception;
use Yii;
use yii\data\Pagination;
use yii\helpers\Json;
use yii\web\UploadedFile;

class RutaAprendizajeController extends \yii\web\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'create' => ['POST'],
                'update' => ['POST'],
                'disable-road' => ['GET'],
                'get-roads' => ['GET'],
                'get-roads-with-courses' => ['GET'],
            ]
        ];
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['options']
        ];

        $behaviors['access'] = [
            'class' => \yii\filters\AccessControl::class,
            'only' => ['index', 'create', 'disable-road', 'get-all-users', 'get-roads', 'get-roads-with-courses'], // acciones a las que se aplicará el control
            'except' => [''],    // acciones a las que no se aplicará el control
            'rules' => [
                [
                    'allow' => true, // permitido o no permitido
                    'actions' => ['index', 'create', 'update', 'disable-road', 'get-roads', 'get-roads-with-courses'], // acciones que siguen esta regla
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
        $query = RutaAprendizaje::find()
            ->andFilterWhere(['LIKE', 'UPPER(nombre)',  strtoupper($name)]);

        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);

        $roads = $query
            ->orderBy('id DESC')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $currentPage = $pagination->getPage() + 1;
        $totalPages = $pagination->getPageCount();
        $response = [
            'success' => true,
            'message' => 'lista de rutas de aprendizaje',
            'data' => [
                'pageInfo' => [
                    'next' => $currentPage == $totalPages ? null  : $currentPage + 1,
                    'previus' => $currentPage == 1 ? null : $currentPage - 1,
                    'count' => count($roads),
                    'page' => $currentPage,
                    'start' => $pagination->getOffset(),
                    'totalPages' => $totalPages,
                ],
                'roads' => $roads
            ],
        ];
        return $response;
    }

    public function actionCreate()
    {
        $road = new RutaAprendizaje();
        $data = Json::decode(Yii::$app->request->post('data'));

        // $data = Json::decode(Yii::$app->request->post('data'));
        $file = UploadedFile::getInstanceByName('file');
        if ($file) {
            $fileName = uniqid() . '.' . $file->getExtension();
            $file->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
            $road->url_image = $fileName;
        }


        $file = UploadedFile::getInstanceByName('fileIcon');
        if ($file) {
            $fileName = uniqid() . '.' . $file->getExtension();
            $file->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
            $road->url_image_icon = $fileName;
        }

        $road->load($data, "");
        try {
            if ($road->save()) {
                //todo ok
                Yii::$app->getResponse()->setStatusCode(201);
                $response = [
                    "success" => true,
                    "message" => "Ruta de aprendizaje agreada exitosamente",
                    'road' => $road
                ];
            } else {
                //Cuando hay error en los tipos de datos ingresados 
                Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                $response = [
                    "success" => false,
                    "message" => "Existen parametros incorrectos",
                    'errors' => $road->errors
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

    public function actionUpdate($idRoad)
    {
        $road = RutaAprendizaje::findOne($idRoad);
        if ($road) {
            $data = JSON::decode(Yii::$app->request->post('data'));
            $image = UploadedFile::getInstanceByName('file');
            if ($image) {
                $url_image = $road->url_image;
                $imageOld = Yii::getAlias('@app/web/upload/' . $url_image);
                if (file_exists($imageOld) && $url_image) {
                    unlink($imageOld);
                    /* Eliminar */
                }
                $fileName = uniqid() . '.' . $image->getExtension();
                $image->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
                $imageNew = Yii::getAlias('@app/web/upload/' . $fileName);
                if (file_exists($imageNew)) {
                    $road->url_image = $fileName;
                } else {
                    return $response = [
                        'success' => false,
                        'message' => 'Ocurrio un error!',
                    ];
                }
            }
            $image = UploadedFile::getInstanceByName('fileIcon');
            if ($image) {
                $url_image = $road->url_image_icon;
                $imageOld = Yii::getAlias('@app/web/upload/' . $url_image);
                if (file_exists($imageOld) && $url_image) {
                    unlink($imageOld);
                    /* Eliminar */
                }
                $fileName = uniqid() . '.' . $image->getExtension();
                $image->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
                $imageNew = Yii::getAlias('@app/web/upload/' . $fileName);
                if (file_exists($imageNew)) {
                    $road->url_image_icon = $fileName;
                } else {
                    return $response = [
                        'success' => false,
                        'message' => 'Ocurrio un error!',
                    ];
                }
            }


            $road->load($data, '');
            try {

                if ($road->save()) {
                    $response = [
                        'success' => true,
                        'message' => 'Ruta actualizada correctamente',
                        'road' => $road
                    ];
                } else {
                    Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                    $response = [
                        'success' => false,
                        'message' => 'Existe errores en los campos',
                        'error' => $road->errors
                    ];
                }
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'message' => 'Cliente no encontrado',
                    'error' => $e
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Ruta de aprendizaje no encontrada',
            ];
        }
        return $response;
    }

    public function actionDisableRoad($idRoad)
    {
        $road = RutaAprendizaje::findOne($idRoad);

        if ($road) {
            try {
                $road->active = false;
                if ($road->save()) {
                    $response = [
                        "success" => true,
                        "message" => "Ruta de aprendizaje desabilitado correctamente",
                        "road" => $road
                    ];
                }
            } catch (\Exception $e) {
                Yii::$app->getResponse()->setStatusCode(422, "");
                $response = [
                    "success" => false,
                    "message" => $e->getMessage(),
                    "code" => $e->getCode()
                ];
            }
        } else {
            Yii::$app->getResponse()->setStatusCode(404);
            $response = [
                "success" => false,
                "message" => "Ruta de aprendizaje no encontrada"
            ];
        }
        return $response;
    }

  

   
}
