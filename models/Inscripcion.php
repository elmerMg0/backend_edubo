<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "inscripcion".
 *
 * @property int $id
 * @property int $curso_id
 * @property int $usuario_id
 * @property string $create_ts
 * @property bool $aprobado
 * @property int $plan_id
 * @property string $expire_date
 * @property int $months
 * @property bool $finished
 *
 * @property Curso $curso
 * @property Plan $plan
 * @property Usuario $usuario
 */
class Inscripcion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'inscripcion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['curso_id', 'usuario_id', 'plan_id', 'expire_date', 'months', 'finished'], 'required'],
            [['curso_id', 'usuario_id', 'plan_id', 'months'], 'default', 'value' => null],
            [['curso_id', 'usuario_id', 'plan_id', 'months'], 'integer'],
            [['create_ts', 'expire_date'], 'safe'],
            [['aprobado', 'finished'], 'boolean'],
            [['curso_id'], 'exist', 'skipOnError' => true, 'targetClass' => Curso::class, 'targetAttribute' => ['curso_id' => 'id']],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plan::class, 'targetAttribute' => ['plan_id' => 'id']],
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
            'curso_id' => 'Curso ID',
            'usuario_id' => 'Usuario ID',
            'create_ts' => 'Create Ts',
            'aprobado' => 'Aprobado',
            'plan_id' => 'Plan ID',
            'expire_date' => 'Expire Date',
            'months' => 'Months',
            'finished' => 'Finished',
        ];
    }

    /**
     * Gets query for [[Curso]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurso()
    {
        return $this->hasOne(Curso::class, ['id' => 'curso_id']);
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
     * Gets query for [[Usuario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuario::class, ['id' => 'usuario_id']);
    }
}
