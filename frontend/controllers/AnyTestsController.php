<?php

namespace frontend\controllers;

use yii\web\Controller;
use yii;
use yii\db\Connection;
use yii\db\Query;
use frontend\models\db\Tasks;
use yii\web\NotFoundHttpException;


class AnytestsController extends Controller
{

    public function d($value) {
        echo "<pre>";
        var_dump($value);
        echo "</pre>";
    }

    public function actionIndex() 
    {
        $pageSets = ['title' => 'hellow world'];
        return $this->render('index', ['pageSets' => $pageSets]);
    }

    public function actionConn() {
        
        // Подключение к базе данных вручную
        $db = new yii\db\Connection([
            'dsn' => 'mysql:host=localhost;dbname=task_force',
            'username' => 'user',
            'password' => 'Universal85',
            'charset' => 'utf8',
        ]);

        // $table = Yii::$app->db->getTableSchema('categories'); // выполнение запросов с помощью подключения глобально из настроек приложения и его компонентов
        $table = $db->getTableSchema('categories'); // выполнение запросов с использованием подключения вручную, те заданного не глобально а частно.

        $this->d($table);

        $pageSets = ['title' => 'Подключение к БД'];
        return $this->render('conn', ['pageSets' => $pageSets]);
    }

    public function actionTasks() {

        date_default_timezone_set('Europe/Moscow');
        /* Пример 1 */
        // Пользователи являются Исполнителями, если они не являются заказчиками в текущий момент, те когда Task_status=new и Task_status=running
        // Находим уникальные id заказчиков customer_id где Task_status=new и Task_status=running 
        // !!! Не работает при SET sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
        // Устанавливается с помощью sql запроса

        // $customer_tasks = Tasks::find()
        //     ->select(['customer_id'])
        //     ->where(['status_id' => '1'])
        //     ->orWhere(['status_id' => '3'])
        //     ->groupBy('customer_id')
        //     ->indexBy('customer_id')
        //     ->asArray()->all(); 
        //     // ->createCommand()->sql // показать как sql запрос
        // ;
        // Показать sql-запрос
        // $this->d($customer_tasks); die;

        // Создаем простой массив в качестве значений id заказчиков 
        // $customers_id = array_keys($customer_tasks);

        /* Пример 2 */
        // $tasks = Tasks::find()->joinWith(['category c', 'location l', 'status s'])
        //     ->where('end_date < NOW()') // сравнение с sql временем
        //     //->where('end_date < :curTime', ['curTime' => date('Y-m-d H:i:s', time())]) // пример сравнение с временем в php 
        //     ->andWhere(['s.symbol' => 'STATUS_NEW'])
        //     ->orderBy(['add_time' => SORT_DESC])
        //     ->limit(3)
        //     ->all(); // в верстке преобразовать в запись вида 4 часа назад
            
        // if (!$tasks) {
        //     throw new NotFoundHttpException("Задание с ID $id не найдено");
        // }

        // Примеры - данные как объекты
        // $tasks = Tasks::findAll(['status_id' => 1]);
        // $tasks = Tasks::find()->where(['status_id' => 1])->all();
        // $tasks = Tasks::find()->where(['status_id' => 1])->orderBy('id_task')->limit(3)->all();
        // $tasks = Tasks::find()->where(['category_id' => 3])->orderBy('id_task')->joinWith('category')->limit(3)->all();
        // $tasks = Tasks::find()->joinWith('category c', 'location')->where(['c.symbol' => 'neo'])->orderBy(['add_time' => SORT_ASC])->limit(3)->all();
        $tasks = Tasks::find()->where(['<=', 'id_task', 5])->all();
        // $tasks = Tasks::find()->where('id_task <= 5')->all();
        // $tasks = Tasks::find()->where('id_task <=' . '5')->all();
   
        // Примеры - c одним значением
        // $task = Tasks::find()->where(['id_task' => '3'])->limit(1)->one();
        // $task = Tasks::find()->where(['id_task' => $id])->joinWith('category')->limit(1)->asArray()->one();
        // $task = Tasks::find()->where(['id_task' => 3])->joinWith('category')->limit(1)->one();
        // $task = Tasks::find()->where(['status_id' => 3])->joinWith('category')->limit(1)->one();


        // Примеры - данные как массивы
        // $tasks = Tasks::find()->where(['category_id' => 3])->orderBy('id_task')->joinWith('category')->limit(3)->asArray()->all();
        
        print('<pre>');
        // печать примеров как массив
        // print('<hr>');
        // $task = Tasks::findOne($id);

        // @property TaskRunnings[] $taskRunnings
        // print_r ($task->taskRunnings[0]->task_running_id);
        print('</pre>');

        $pageSets = ['title' => 'Подключение к БД'];
        return $this->render('tasks', ['tasks' => $tasks, 'pageSets' => $pageSets]);

    }

}
