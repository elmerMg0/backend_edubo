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
 * @property string|null $type
 *
 * @property Avance[] $avances
 * @property Clase $clase
 * @property Comment[] $comments
 * @property FileSubject[] $fileSubjects
 * @property LinkSubject[] $linkSubjects
 * @property Recurso[] $recursos
 * @property SubjectLikes[] $subjectLikes
 * @property Usuario[] $usuarios
 * @property Usuario[] $usuarios0
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
            [['type'], 'string', 'max' => 15],
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
            'type' => 'Type',
        ];
    }

    /**
     * Gets query for [[Avances]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAvances()
    {
        return $this->hasMany(Avance::class, ['subject_id' => 'id']);
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
     * Gets query for [[Comments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['subject_id' => 'id']);
    }

    /**
     * Gets query for [[FileSubjects]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFileSubjects()
    {
        return $this->hasMany(FileSubject::class, ['subject_id' => 'id']);
    }

    /**
     * Gets query for [[LinkSubjects]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLinkSubjects()
    {
        return $this->hasMany(LinkSubject::class, ['subject_id' => 'id']);
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

    /**
     * Gets query for [[SubjectLikes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubjectLikes()
    {
        return $this->hasMany(SubjectLikes::class, ['subject_id' => 'id']);
    }

    /**
     * Gets query for [[Usuarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuarios()
    {
        return $this->hasMany(Usuario::class, ['id' => 'usuario_id'])->viaTable('avance', ['subject_id' => 'id']);
    }

    /**
     * Gets query for [[Usuarios0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuarios0()
    {
        return $this->hasMany(Usuario::class, ['id' => 'usuario_id'])->viaTable('subject_likes', ['subject_id' => 'id']);
    }
}
