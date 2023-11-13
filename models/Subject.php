<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "subject".
 *
 * @property int $id
 * @property bool $is_public
 * @property string $slug
 * @property string $title
 * @property int $clase_id
 * @property string|null $duration
 * @property string|null $video_url
 * @property int|null $views
 *
 * @property Clase $clase
 * @property Recurso[] $recursos
 */
class Subject extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subject';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_public', 'slug', 'title', 'clase_id'], 'required'],
            [['is_public'], 'boolean'],
            [['clase_id', 'views'], 'default', 'value' => null],
            [['clase_id', 'views'], 'integer'],
            [['duration'], 'string'],
            [['slug'], 'string', 'max' => 10],
            [['title'], 'string', 'max' => 50],
            [['video_url'], 'string', 'max' => 100],
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
            'is_public' => 'Is Public',
            'slug' => 'Slug',
            'title' => 'Title',
            'clase_id' => 'Clase ID',
            'duration' => 'Duration',
            'video_url' => 'Video Url',
            'views' => 'Views',
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
     * Gets query for [[Recursos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecursos()
    {
        return $this->hasMany(Recurso::class, ['subject_id' => 'id']);
    }
}
