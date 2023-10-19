<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pregunta".
 *
 * @property int $id
 * @property string $descripcion
 * @property string $respuesta
 * @property string|null $url_image
 * @property int $clase_id
 * @property string $create_ts
 * @property string|null $update_ts
 *
 * @property Clase $clase
 * @property Resultado $resultado
 */
class Pregunta extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pregunta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['descripcion', 'respuesta', 'clase_id'], 'required'],
            [['clase_id'], 'default', 'value' => null],
            [['clase_id'], 'integer'],
            [['create_ts', 'update_ts'], 'safe'],
            [['descripcion'], 'string', 'max' => 150],
            [['respuesta'], 'string', 'max' => 80],
            [['url_image'], 'string', 'max' => 50],
            [['clase_id'], 'exist', 'skipOnError' => true, 'targetClass' => Clase::class, 'targetAttribute' => ['clase_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'descripcion' => 'Descripcion',
            'respuesta' => 'Respuesta',
            'url_image' => 'Url Image',
            'clase_id' => 'Clase ID',
            'create_ts' => 'Create Ts',
            'update_ts' => 'Update Ts',
        ];
    }

    /**
     * Gets query for [[Clase]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClase()
    {
        return $this->hasOne(Clase::class, ['id' => 'clase_id']);
    }

    /**
     * Gets query for [[Resultado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResultado()
    {
        return $this->hasOne(Resultado::class, ['id' => 'id']);
    }
}
