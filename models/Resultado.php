<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "resultado".
 *
 * @property int $id
 * @property int $pregunta_id
 * @property int $estudiante_id
 * @property string $update_ts
 * @property string $create_ts
 *
 * @property Estudiante $estudiante
 * @property Pregunta $id0
 */
class Resultado extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'resultado';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pregunta_id', 'estudiante_id', 'update_ts', 'create_ts'], 'required'],
            [['pregunta_id', 'estudiante_id'], 'default', 'value' => null],
            [['pregunta_id', 'estudiante_id'], 'integer'],
            [['update_ts', 'create_ts'], 'safe'],
            [['estudiante_id'], 'exist', 'skipOnError' => true, 'targetClass' => Estudiante::class, 'targetAttribute' => ['estudiante_id' => 'id']],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => Pregunta::class, 'targetAttribute' => ['id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pregunta_id' => 'Pregunta ID',
            'estudiante_id' => 'Estudiante ID',
            'update_ts' => 'Update Ts',
            'create_ts' => 'Create Ts',
        ];
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

    /**
     * Gets query for [[Id0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getId0()
    {
        return $this->hasOne(Pregunta::class, ['id' => 'id']);
    }
}
