<?php

namespace app\controllers;

use app\models\CoursePlan;
use app\models\Plan;
use app\models\RoadPlan;
use Exception;
use Yii;

class PlanController extends \yii\web\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'planes' => ['GET'],
                'create' => ['POST'],
                'update' => ['POST'],
            ]
        ];
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['options']
        ];

        $behaviors['access'] = [
            'class' => \yii\filters\AccessControl::class,
            'only' => ['planes', 'create', 'disable-class', 'update'], // acciones a las que se aplicará el control
            'except' => [''],    // acciones a las que no se aplicará el control
            'rules' => [
                [
                    'allow' => true, // permitido o no permitido
                    'actions' => ['planes', 'create', 'disable-class', 'update'], // acciones que siguen esta regla
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

    

    public function actionCreate()
    {
        $params = Yii::$app->getRequest()->getBodyParams();
        $class = new Plan();
        $class->load($params, "");
        try {
            if ($class->save()) {
                //todo ok
                Yii::$app->getResponse()->setStatusCode(201);
                $response = [
                    "success" => true,
                    "message" => "Clase agreado exitosamente",
                    'cliente' => $class
                ];
            } else {
                //Cuando hay error en los tipos de datos ingresados 
                Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                $response = [
                    "success" => false,
                    "message" => "Existen parametros incorrectos",
                    'errors' => $class->errors
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

    public function actionUpdate($idPlan)
    {
        $class = Plan::findOne($idPlan);
        if ($class) {
            $params = Yii::$app->getRequest()->getBodyParams();
            $class->load($params, '');
            $class->active = $params['active'];
            try {

                if ($class->save()) {
                    $response = [
                        'success' => true,
                        'message' => 'Plan actualizada correctamente',
                        'class' => $class
                    ];
                } else {
                    Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                    $response = [
                        'success' => false,
                        'message' => 'Existe errores en los campos',
                        'error' => $class->errors
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
                'message' => 'Plan no encontrado',
            ];
        }
        return $response;
    }

    public function actionDisableClass($idPlan)
    {
        $class = Plan::findOne($idPlan);

        if ($class) {
            try {
                $class->active = false;
                if ($class->save()) {
                    $response = [
                        "success" => true,
                        "message" => "Clase desabilitado correctamente",
                        "class" => $class
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
                "message" => "Clase no encontrado"
            ];
        }
        return $response;
    }

    public function actionPlanes()
    {
        $planes = Plan::find()
                    ->orderBy(['id' => SORT_DESC])
                    ->all();
        $response = [
            'success' => true,
            'message' => 'Lista de los planes',
            'data' => [
                'planes' => $planes,
            ]
        ];
        return $response;
    }

/* After */
    public function actionAssignPlan($idPlan, $type, $idItem){
        if($type === 'course'){
            $coursePlan = new CoursePlan();
            $coursePlan->course_id = $idItem;
            $coursePlan->plan_id = $idPlan;
            if($coursePlan->save()){
                
            }
        }else{
            $roadPlan = new RoadPlan();
            $roadPlan->plan_id = $idPlan;
            $roadPlan->ruta_aprendizaje_id = $idItem;
            if($roadPlan->save()){
                
            }
        }
        $response = [
            'success' => true,
            'message' => 'Informacion',
        
        ];
        return $response;
    }

    public function actionPlansByItem($idItem, $type){
        $assigedPlans = [];

        if($type === 'course'){
            $coursePlans = CoursePlan::find()
                        ->select(['plan.nombre','plan.benefit','precio_total','duracion' ,  'course_plan.id'])
                        ->innerJoin('plan', 'plan.id = course_plan.plan_id')
                        ->where(['course_id' => $idItem])
                        ->asArray()
                        -> all();
            $assigedPlans= [...$coursePlans];
        }else{
            $roadPlans = RoadPlan::find()
            ->select(['plan.nombre','plan.benefit','precio_total','duracion' ,  'road_plan.id'])
                        ->innerJoin('plan', 'plan.id = road_plan.plan_id')
                        ->where(['ruta_aprendizaje_id' => $idItem])
                        ->asArray()
                        -> all();
            $assigedPlans = [...$roadPlans];
        }

        $response = [
            'success' => true,
            'message' => 'Informacion',
            'data' => [
                'assigedPlans' => $assigedPlans
            ]
        ];
        return $response;
    }

    /* Eliminar registro de coursePlan o roadPlan depende del tipo */
    public function actionUnassignPlan($idPlan, $type){
        if($type === 'course'){
            $coursePlan = CoursePlan::findOne($idPlan);
            $coursePlan->delete();
        }else{
            $roadPlan = RoadPlan::findOne($idPlan);
            $roadPlan->delete();
        }
        $response = [
            'success' => true,
            'message' => 'Informacion',
        
        ];
        return $response;
    }
}
