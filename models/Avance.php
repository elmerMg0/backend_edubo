<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "avance".
 *
 * @property int $id
 * @property int $subject_id
 * @property int $usuario_id
 * @property string $create_ts
 * @property string|null $update_ts
 *
 * @property Subject $subject
 * @property Usuario $usuario
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
            [['subject_id', 'usuario_id'], 'required'],
            [['subject_id', 'usuario_id'], 'default', 'value' => null],
            [['subject_id', 'usuario_id'], 'integer'],
            [['create_ts', 'update_ts'], 'safe'],
            [['subject_id', 'usuario_id'], 'unique', 'targetAttribute' => ['subject_id', 'usuario_id']],
            [['subject_id'], 'exist', 'skipOnError' => true, 'targetClass' => Subject::class, 'targetAttribute' => ['subject_id' => 'id']],
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
            'subject_id' => 'Subject ID',
            'usuario_id' => 'Usuario ID',
            'create_ts' => 'Create Ts',
            'update_ts' => 'Update Ts',
        ];
    }

    /**
     * Gets query for [[Subject]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubject()
    {
        return $this->hasOne(Subject::class, ['id' => 'subject_id']);
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
