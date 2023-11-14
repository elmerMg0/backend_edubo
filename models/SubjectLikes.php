<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "subject_likes".
 *
 * @property int $id
 * @property int $subject_id
 * @property int $estudiante_id
 *
 * @property Estudiante $estudiante
 * @property Subject $subject
 */
class SubjectLikes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subject_likes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['subject_id', 'estudiante_id'], 'required'],
            [['subject_id', 'estudiante_id'], 'default', 'value' => null],
            [['subject_id', 'estudiante_id'], 'integer'],
            [['estudiante_id'], 'exist', 'skipOnError' => true, 'targetClass' => Estudiante::class, 'targetAttribute' => ['estudiante_id' => 'id']],
            [['subject_id'], 'exist', 'skipOnError' => true, 'targetClass' => Subject::class, 'targetAttribute' => ['subject_id' => 'id']],
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
            'estudiante_id' => 'Estudiante ID',
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
     * Gets query for [[Subject]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubject()
    {
        return $this->hasOne(Subject::class, ['id' => 'subject_id']);
    }
}
