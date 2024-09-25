<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "usuario".
 *
 * @property int $id
 * @property string $nombre
 * @property string|null $apellido
 * @property string $email
 * @property string|null $password_hash
 * @property string $access_token
 * @property int $plan_id
 * @property string $create_ts
 * @property string|null $update_ts
 * @property int $puntos
 * @property string|null $url_image
 * @property string|null $type
 * @property bool $active
 * @property string $username
 * @property string|null $telefono
 *
 * @property Avance[] $avances
 * @property CommentLikes[] $commentLikes
 * @property Comment[] $comments
 * @property Inscripcion[] $inscripcions
 * @property RoadUser[] $roadUsers
 * @property SubjectLikes[] $subjectLikes
 * @property Subject[] $subjects
 * @property Subject[] $subjects0
 */
class Usuario extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'usuario';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'email', 'access_token', 'plan_id', 'username'], 'required'],
            [['access_token'], 'string'],
            [['plan_id', 'puntos'], 'default', 'value' => null],
            [['plan_id', 'puntos'], 'integer'],
            [['create_ts', 'update_ts'], 'safe'],
            [['active'], 'boolean'],
            [['nombre', 'type', 'username'], 'string', 'max' => 50],
            [['apellido'], 'string', 'max' => 80],
            [['email', 'password_hash'], 'string', 'max' => 250],
            [['url_image'], 'string', 'max' => 100],
            [['telefono'], 'string', 'max' => 11],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'apellido' => 'Apellido',
            'email' => 'Email',
            'password_hash' => 'Password Hash',
            'access_token' => 'Access Token',
            'plan_id' => 'Plan ID',
            'create_ts' => 'Create Ts',
            'update_ts' => 'Update Ts',
            'puntos' => 'Puntos',
            'url_image' => 'Url Image',
            'type' => 'Type',
            'active' => 'Active',
            'username' => 'Username',
            'telefono' => 'Telefono',
        ];
    }

    /**
     * Gets query for [[Avances]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAvances()
    {
        return $this->hasMany(Avance::class, ['usuario_id' => 'id']);
    }

    /**
     * Gets query for [[CommentLikes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCommentLikes()
    {
        return $this->hasMany(CommentLikes::class, ['usuario_id' => 'id']);
    }

    /**
     * Gets query for [[Comments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['usuario_id' => 'id']);
    }

    /**
     * Gets query for [[Inscripcions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInscripcions()
    {
        return $this->hasMany(Inscripcion::class, ['usuario_id' => 'id']);
    }

    /**
     * Gets query for [[RoadUsers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoadUsers()
    {
        return $this->hasMany(RoadUser::class, ['usuario_id' => 'id']);
    }

    /**
     * Gets query for [[SubjectLikes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubjectLikes()
    {
        return $this->hasMany(SubjectLikes::class, ['usuario_id' => 'id']);
    }

    /**
     * Gets query for [[Subjects]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubjects()
    {
        return $this->hasMany(Subject::class, ['id' => 'subject_id'])->viaTable('avance', ['usuario_id' => 'id']);
    }

    /**
     * Gets query for [[Subjects0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubjects0()
    {
        return $this->hasMany(Subject::class, ['id' => 'subject_id'])->viaTable('subject_likes', ['usuario_id' => 'id']);
    }
}
