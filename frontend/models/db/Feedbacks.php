<?php

namespace frontend\models\db;

use Yii;

/**
 * This is the model class for table "feedbacks".
 *
 * @property int $feedback_id
 * @property int $author_id
 * @property int $recipient_id
 * @property int $task_id
 * @property string|null $desc_text
 * @property int $point_num
 * @property string $add_time
 *
 * @property Users $author
 * @property Users $recipient
 * @property Tasks $task
 */
class Feedbacks extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'feedbacks';
    }

    public function rules()
    {
        return [
            [['author_id', 'recipient_id', 'task_id', 'point_num', 'add_time'], 'required'],
            [['author_id', 'recipient_id', 'task_id', 'point_num'], 'integer'],
            [['desc_text'], 'string'],
            [['add_time'], 'safe'],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['author_id' => 'user_id']],
            [['recipient_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['recipient_id' => 'user_id']],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tasks::class, 'targetAttribute' => ['task_id' => 'task_id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'feedback_id' => 'Feedback ID',
            'author_id' => 'Author ID',
            'recipient_id' => 'Recipient ID',
            'task_id' => 'Task ID',
            'desc_text' => 'Desc Text',
            'point_num' => 'Point Num',
            'add_time' => 'Add Time',
        ];
    }

    public function getAuthor()
    {
        return $this->hasOne(Users::class, ['user_id' => 'author_id']);
    }

    public function getRecipient()
    {
        return $this->hasOne(Users::class, ['user_id' => 'recipient_id']);
    }

    public function getTask()
    {
        return $this->hasOne(Tasks::class, ['task_id' => 'task_id']);
    }
}
