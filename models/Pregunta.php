<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pregunta".
 *
 * @property int $id
 * @property string $descripcion
 * @property string|null $url_image
 * @property int|null $clase_id
 * @property string $create_ts
 * @property string|null $update_ts
 * @property string|null $subtitle
 * @property bool $active
 * @property int|null $quiz_id
 *
 * @property Clase $clase
 * @property Quiz $quiz
 * @property Response[] $responses
 */
class Pregunta extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pregunta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['descripcion', 'active'], 'required'],
            [['descripcion'], 'string'],
            [['clase_id', 'quiz_id'], 'default', 'value' => null],
            [['clase_id', 'quiz_id'], 'integer'],
            [['create_ts', 'update_ts'], 'safe'],
            [['active'], 'boolean'],
            [['url_image'], 'string', 'max' => 50],
            [['subtitle'], 'string', 'max' => 250],
            [['clase_id'], 'exist', 'skipOnError' => true, 'targetClass' => Clase::class, 'targetAttribute' => ['clase_id' => 'id']],
            [['quiz_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quiz::class, 'targetAttribute' => ['quiz_id' => 'id']],
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
            'url_image' => 'Url Image',
            'clase_id' => 'Clase ID',
            'create_ts' => 'Create Ts',
            'update_ts' => 'Update Ts',
            'subtitle' => 'Subtitle',
            'active' => 'Active',
            'quiz_id' => 'Quiz ID',
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
     * Gets query for [[Quiz]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuiz()
    {
        return $this->hasOne(Quiz::class, ['id' => 'quiz_id']);
    }

    /**
     * Gets query for [[Responses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResponses()
    {
        return $this->hasMany(Response::class, ['pregunta_id' => 'id']);
    }
}
