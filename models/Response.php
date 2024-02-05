<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "response".
 *
 * @property int $id
 * @property string $description
 * @property string|null $url_image
 * @property int $pregunta_id
 * @property bool $is_correct
 * @property string $slug
 * @property bool|null $active
 *
 * @property Pregunta $pregunta
 */
class Response extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'response';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'pregunta_id', 'slug'], 'required'],
            [['pregunta_id'], 'default', 'value' => null],
            [['pregunta_id'], 'integer'],
            [['is_correct', 'active'], 'boolean'],
            [['description'], 'string', 'max' => 250],
            [['url_image'], 'string', 'max' => 50],
            [['slug'], 'string', 'max' => 10],
            [['pregunta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pregunta::class, 'targetAttribute' => ['pregunta_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'description' => 'Description',
            'url_image' => 'Url Image',
            'pregunta_id' => 'Pregunta ID',
            'is_correct' => 'Is Correct',
            'slug' => 'Slug',
            'active' => 'Active',
        ];
    }

    /**
     * Gets query for [[Pregunta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPregunta()
    {
        return $this->hasOne(Pregunta::class, ['id' => 'pregunta_id']);
    }
}
