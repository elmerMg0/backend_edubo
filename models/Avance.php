<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "avance".
 *
 * @property int $id
 * @property int $clase_id
 * @property int $estudiante_id
 * @property string $create_ts
 * @property string $update_ts
 *
 * @property Clase $clase
 * @property Estudiante $id0
 */
class Avance extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'avance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['clase_id', 'estudiante_id', 'create_ts', 'update_ts'], 'required'],
            [['clase_id', 'estudiante_id'], 'default', 'value' => null],
            [['clase_id', 'estudiante_id'], 'integer'],
            [['create_ts', 'update_ts'], 'safe'],
            [['clase_id'], 'exist', 'skipOnError' => true, 'targetClass' => Clase::class, 'targetAttribute' => ['clase_id' => 'id']],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => Estudiante::class, 'targetAttribute' => ['id' => 'id']],
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
     * Gets query for [[Id0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getId0()
    {
        return $this->hasOne(Estudiante::class, ['id' => 'id']);
    }
}
