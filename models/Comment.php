<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "comment".
 *
 * @property int $id
 * @property string $comment_text
 * @property int $usuario_id
 * @property int $subject_id
 * @property int $num_likes
 * @property int|null $num_comments
 * @property string $created_ts
 * @property string|null $state
 * @property int|null $comment_id
 *
 * @property Comment $comment
 * @property CommentLikes[] $commentLikes
 * @property Comment[] $comments
 * @property Subject $subject
 * @property Usuario $usuario
 */
class Comment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['comment_text', 'usuario_id', 'subject_id', 'num_likes'], 'required'],
            [['comment_text'], 'string'],
            [['usuario_id', 'subject_id', 'num_likes', 'num_comments', 'comment_id'], 'default', 'value' => null],
            [['usuario_id', 'subject_id', 'num_likes', 'num_comments', 'comment_id'], 'integer'],
            [['created_ts'], 'safe'],
            [['state'], 'string', 'max' => 20],
            [['comment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Comment::class, 'targetAttribute' => ['comment_id' => 'id']],
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
            'comment_text' => 'Comment Text',
            'usuario_id' => 'Usuario ID',
            'subject_id' => 'Subject ID',
            'num_likes' => 'Num Likes',
            'num_comments' => 'Num Comments',
            'created_ts' => 'Created Ts',
            'state' => 'State',
            'comment_id' => 'Comment ID',
        ];
    }

    /**
     * Gets query for [[Comment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComment()
    {
        return $this->hasOne(Comment::class, ['id' => 'comment_id']);
    }

    /**
     * Gets query for [[CommentLikes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCommentLikes()
    {
        return $this->hasMany(CommentLikes::class, ['comment_id' => 'id']);
    }

    /**
     * Gets query for [[Comments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['comment_id' => 'id']);
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
