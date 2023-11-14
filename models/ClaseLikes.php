<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "clase_likes".
 *
 * @property int $id
 * @property int $clase_id
 * @property int $estudiante_id
 *
 * @property Clase $clase
 * @property Estudiante $estudiante
 */
class ClaseLikes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'clase_likes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['clase_id', 'estudiante_id'], 'required'],
            [['clase_id', 'estudiante_id'], 'default', 'value' => null],
            [['clase_id', 'estudiante_id'], 'integer'],
            [['clase_id'], 'exist', 'skipOnError' => true, 'targetClass' => Clase::class, 'targetAttribute' => ['clase_id' => 'id']],
            [['estudiante_id'], 'exist', 'skipOnError' => true, 'targetClass' => Estudiante::class, 'targetAttribute' => ['estudiante_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'clase_id' => 'Clase ID',
            'estudiante_id' => 'Estudiante ID',
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
     * Gets query for [[Estudiante]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEstudiante()
    {
        return $this->hasOne(Estudiante::class, ['id' => 'estudiante_id']);
    }
}
