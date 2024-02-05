<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pregunta".
 *
 * @property int $id
 * @property string $descripcion
 * @property string|null $url_image
 * @property int $clase_id
 * @property string $create_ts
 * @property string|null $update_ts
 * @property string|null $subtitle
 * @property bool $active
 *
 * @property Clase $clase
 * @property Response[] $responses
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
            [['descripcion', 'clase_id', 'active'], 'required'],
            [['clase_id'], 'default', 'value' => null],
            [['clase_id'], 'integer'],
            [['create_ts', 'update_ts'], 'safe'],
            [['active'], 'boolean'],
            [['descripcion'], 'string', 'max' => 150],
            [['url_image'], 'string', 'max' => 50],
            [['subtitle'], 'string', 'max' => 250],
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
            'url_image' => 'Url Image',
            'clase_id' => 'Clase ID',
            'create_ts' => 'Create Ts',
            'update_ts' => 'Update Ts',
            'subtitle' => 'Subtitle',
            'active' => 'Active',
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
     * Gets query for [[Responses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResponses()
    {
        return $this->hasMany(Response::class, ['pregunta_id' => 'id']);
    }
}
