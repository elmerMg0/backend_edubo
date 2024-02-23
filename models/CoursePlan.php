<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "course_plan".
 *
 * @property int $id
 * @property int $course_id
 * @property int $plan_id
 * @property string $create_ts
 *
 * @property Curso $course
 * @property Plan $plan
 */
class CoursePlan extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'course_plan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['course_id', 'plan_id'], 'required'],
            [['course_id', 'plan_id'], 'default', 'value' => null],
            [['course_id', 'plan_id'], 'integer'],
            [['create_ts'], 'safe'],
            [['course_id'], 'exist', 'skipOnError' => true, 'targetClass' => Curso::class, 'targetAttribute' => ['course_id' => 'id']],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plan::class, 'targetAttribute' => ['plan_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'course_id' => 'Course ID',
            'plan_id' => 'Plan ID',
            'create_ts' => 'Create Ts',
        ];
    }

    /**
     * Gets query for [[Course]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCourse()
    {
        return $this->hasOne(Curso::class, ['id' => 'course_id']);
    }

    /**
     * Gets query for [[Plan]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlan()
    {
        return $this->hasOne(Plan::class, ['id' => 'plan_id']);
    }
}
