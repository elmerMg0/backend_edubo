<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "plan".
 *
 * @property string $nombre
 * @property int $precio_mes
 * @property bool $active
 * @property int $precio_total
 * @property int $id
 * @property string|null $duracion
 * @property string|null $benefit
 *
 * @property Inscripcion[] $inscripcions
 * @property RoadPlan[] $roadPlans
 * @property RoadUser[] $roadUsers
 */
class Plan extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'precio_mes', 'active', 'precio_total'], 'required'],
            [['precio_mes', 'precio_total'], 'default', 'value' => null],
            [['precio_mes', 'precio_total'], 'integer'],
            [['active'], 'boolean'],
            [['benefit'], 'string'],
            [['nombre'], 'string', 'max' => 20],
            [['duracion'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'nombre' => 'Nombre',
            'precio_mes' => 'Precio Mes',
            'active' => 'Active',
            'precio_total' => 'Precio Total',
            'id' => 'ID',
            'duracion' => 'Duracion',
            'benefit' => 'Benefit',
        ];
    }

    /**
     * Gets query for [[Inscripcions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInscripcions()
    {
        return $this->hasMany(Inscripcion::class, ['plan_id' => 'id']);
    }

    /**
     * Gets query for [[RoadPlans]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoadPlans()
    {
        return $this->hasMany(RoadPlan::class, ['plan_id' => 'id']);
    }

    /**
     * Gets query for [[RoadUsers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoadUsers()
    {
        return $this->hasMany(RoadUser::class, ['plan_id' => 'id']);
    }
}
