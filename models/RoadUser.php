<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "road_user".
 *
 * @property int $id
 * @property int $usuario_id
 * @property int $ruta_aprendizaje
 * @property string $create_ts
 * @property int $plan_id
 * @property string $expire_date
 *
 * @property Plan $plan
 * @property RutaAprendizaje $rutaAprendizaje
 * @property Usuario $usuario
 */
class RoadUser extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'road_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['usuario_id', 'ruta_aprendizaje', 'plan_id', 'expire_date'], 'required'],
            [['usuario_id', 'ruta_aprendizaje', 'plan_id'], 'default', 'value' => null],
            [['usuario_id', 'ruta_aprendizaje', 'plan_id'], 'integer'],
            [['create_ts', 'expire_date'], 'safe'],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plan::class, 'targetAttribute' => ['plan_id' => 'id']],
            [['ruta_aprendizaje'], 'exist', 'skipOnError' => true, 'targetClass' => RutaAprendizaje::class, 'targetAttribute' => ['ruta_aprendizaje' => 'id']],
            [['usuario_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['usuario_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'usuario_id' => 'Usuario ID',
            'ruta_aprendizaje' => 'Ruta Aprendizaje',
            'create_ts' => 'Create Ts',
            'plan_id' => 'Plan ID',
            'expire_date' => 'Expire Date',
        ];
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

    /**
     * Gets query for [[RutaAprendizaje]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRutaAprendizaje()
    {
        return $this->hasOne(RutaAprendizaje::class, ['id' => 'ruta_aprendizaje']);
    }

    /**
     * Gets query for [[Usuario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuario::class, ['id' => 'usuario_id']);
    }
}