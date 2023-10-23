<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "usuario".
 *
 * @property int $id
 * @property string $nombre
 * @property string $apellido
 * @property string $email
 * @property string|null $password_hash
 * @property string $access_token
 * @property int $plan_id
 * @property string $create_ts
 * @property string $update_ts
 * @property int $puntos
 * @property string $tipo
 * @property bool $active
 *
 * @property Avance[] $avances
 * @property Inscripcion[] $inscripcions
 * @property Resultado[] $resultados
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
            [['nombre', 'apellido', 'email', 'access_token', 'plan_id', 'update_ts', 'tipo', 'active'], 'required'],
            [['plan_id', 'puntos'], 'default', 'value' => null],
            [['plan_id', 'puntos'], 'integer'],
            [['create_ts', 'update_ts'], 'safe'],
            [['active'], 'boolean'],
            [['nombre'], 'string', 'max' => 50],
            [['apellido'], 'string', 'max' => 80],
            [['email', 'password_hash', 'access_token'], 'string', 'max' => 250],
            [['tipo'], 'string', 'max' => 15],
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
            'tipo' => 'Tipo',
            'active' => 'Active',
        ];
    }

    /**
     * Gets query for [[Avances]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAvances()
    {
        return $this->hasMany(Avance::class, ['id' => 'id']);
    }

    /**
     * Gets query for [[Inscripcions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInscripcions()
    {
        return $this->hasMany(Inscripcion::class, ['estudiante_id' => 'id']);
    }

    /**
     * Gets query for [[Resultados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResultados()
    {
        return $this->hasMany(Resultado::class, ['estudiante_id' => 'id']);
    }
}
