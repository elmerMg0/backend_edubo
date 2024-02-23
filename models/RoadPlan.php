<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "road_plan".
 *
 * @property int $id
 * @property int $plan_id
 * @property int $ruta_aprendizaje_id
 * @property string $create_ts
 *
 * @property RutaAprendizaje $id0
 * @property Plan $plan
 */
class RoadPlan extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'road_plan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['plan_id', 'ruta_aprendizaje_id'], 'required'],
            [['plan_id', 'ruta_aprendizaje_id'], 'default', 'value' => null],
            [['plan_id', 'ruta_aprendizaje_id'], 'integer'],
            [['create_ts'], 'safe'],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plan::class, 'targetAttribute' => ['plan_id' => 'id']],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => RutaAprendizaje::class, 'targetAttribute' => ['id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'plan_id' => 'Plan ID',
            'ruta_aprendizaje_id' => 'Ruta Aprendizaje ID',
            'create_ts' => 'Create Ts',
        ];
    }

    /**
     * Gets query for [[Id0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getId0()
    {
        return $this->hasOne(RutaAprendizaje::class, ['id' => 'id']);
    }

    /**
     * Gets query for [[Plan]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlan()
    {
        return $this->hasOne(Plan::class, ['id' => 'plan_id']);
    }
}
