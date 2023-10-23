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
                'get-category' => ['get'],

            ]
        ];
        /* $behaviors['authenticator'] = [         	
            'class' => \yii\filters\auth\HttpBearerAuth::class,         	
            'except' => ['options']     	
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

    public function actionIndex($name, $pageSize=5)
    {   
        if($name === 'undefined')$name = null;
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
                'questions' => $questions
            ]
        ];
        return $response;
    }

    public function actionGetQuestions () {
        $questions = Pregunta::find()
                      ->where(['estado' => 'Activo'])
                      ->orderBy(['id' => 'SORT_ASC'])             
                      ->all();

        if($questions){
            $response = [
                'success' => true,
                'message' => 'Lista de preguntas',
                'questions' => $questions,
            ];
        }else{
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
        if($file){
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
                Yii::$app->getResponse()->setStatusCode(422,"Data Validation Failed.");
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
                if(file_exists($imageOld) && $url_image){
                    unlink($imageOld);
                    /* Eliminar */
                }
                $fileName = uniqid().'.'.$image->getExtension();
                $image->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
                $imageNew = Yii::getAlias('@app/web/upload/' . $fileName);
                if(file_exists($imageNew)){
                    $question -> url_image = $fileName;
                }else{
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

    public function actionGetCategory($idQuestion)
    {
        $question = Pregunta::findOne($idQuestion);
        if ($question) {
            $response = [
                'success' => true,
                'message' => 'Accion realizada correctamente',
                'category' => $question
            ];
        } else {
            Yii::$app->getResponse()->setStatusCode(404);
            $response = [
                'success' => false,
                'message' => 'No existe la pregunta',
                'category' => $question
            ];
        }
        return $response;
    }

   /*  public function actionDelete($idCategory)
    {
        $category = Pregutna::findOne($idCategory);

        if ($category) {
            try {
                $url_image = $category->url_image;
                $category->delete();
                $pathFile = Yii::getAlias('@webroot/upload/'.$url_image);
                if( file_exists($pathFile)){
                    unlink($pathFile);
                }
                $response = [
                    "success" => true,
                    "message" => "Categoria eliminado correctamente",
                    "category" => $category
                ];
            } catch (yii\db\IntegrityException $ie) {
                Yii::$app->getResponse()->setStatusCode(409, "");
                $response = [
                    "success" => false,
                    "message" =>  "El Categoria esta siendo usado",
                    "code" => $ie->getCode()
                ];
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
                "message" => "Categoria no encontrado"
            ];
        }
        return $response;
    } */

    public function actionGetQuestionsByClass($idClass){
        $class = Clase::findOne($idClass);
        if($class){
            $questions = Pregunta::find()
                        ->where(['estado' => true, 'clase_id' => $idClass])
                        ->all();
            $response = [
                "success" => true,
                "message" => "Lista de pregutnas por clase",
                "category" => $class,
                "products" => $questions
            ];
        }else{
            Yii::$app->getResponse()->setStatusCode(404);
            $response = [
                "success" => false,
                "message" => "Categoria no encontrada",
            ];
        }
        return $response;
    }

    public function actionDisableCategory( $idCategory ){
        $question = Pregunta::findOne($idCategory);
        if($question){
            $question -> active = false;
            if($question -> save()){
                $response = [
                    'success' => true,
                    'message' => 'Pregunta actualizada'
                ];
            }else{
                $response = [
                    'success' => false,
                    'message' => 'Ocurrio un error!'
                ];
            }
        }else{
            $response = [
                'success' => false,
                'message' => 'Ocurrio un error!'
            ];
        }
        return $response;
    }
}
