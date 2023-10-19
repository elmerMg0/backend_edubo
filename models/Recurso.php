<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "recurso".
 *
 * @property int $id
 * @property string|null $descripcion
 * @property string|null $url_video
 * @property int $clase_id
 * @property bool $active
 * @property string|null $update_ts
 * @property string $create_ts
 *
 * @property Clase $clase
 */
class Recurso extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recurso';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['descripcion', 'url_video'], 'string'],
            [['clase_id', 'active'], 'required'],
            [['clase_id'], 'default', 'value' => null],
            [['clase_id'], 'integer'],
            [['active'], 'boolean'],
            [['update_ts', 'create_ts'], 'safe'],
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
            'url_video' => 'Url Video',
            'clase_id' => 'Clase ID',
            'active' => 'Active',
            'update_ts' => 'Update Ts',
            'create_ts' => 'Create Ts',
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
}
