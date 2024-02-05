<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "recurso".
 *
 * @property int $id
 * @property string|null $descripcion
 * @property int $subject_id
 * @property bool $active
 * @property string|null $update_ts
 * @property string $create_ts
 *
 * @property Subject $subject
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
            [['descripcion'], 'string'],
            [['subject_id', 'active'], 'required'],
            [['subject_id'], 'default', 'value' => null],
            [['subject_id'], 'integer'],
            [['active'], 'boolean'],
            [['update_ts', 'create_ts'], 'safe'],
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
            'descripcion' => 'Descripcion',
            'subject_id' => 'Subject ID',
            'active' => 'Active',
            'update_ts' => 'Update Ts',
            'create_ts' => 'Create Ts',
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
}
