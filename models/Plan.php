<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "plan".
 *
 * @property string $id
 * @property string $nombre
 * @property int $precio_mes
 * @property bool $active
 * @property int $precio_total
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
            [['id', 'nombre', 'precio_mes', 'active', 'precio_total'], 'required'],
            [['id'], 'safe'],
            [['precio_mes', 'precio_total'], 'default', 'value' => null],
            [['precio_mes', 'precio_total'], 'integer'],
            [['active'], 'boolean'],
            [['nombre'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'precio_mes' => 'Precio Mes',
            'active' => 'Active',
            'precio_total' => 'Precio Total',
        ];
    }
}
