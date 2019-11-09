<?php
//п.6.7 Страница Просмотр задания view.html
//п.6.7 Кнопки статуса (блок страницы)
//п.6.11 клик по кнопкам вызывает модальное окно согласно (Отклик на задание, Завершение задания, Отказ от задания)
//п.6.6 Список исполнителей. Исполнителями считаются пользователи, отметившие хотя бы одну категорию у себя в профиле.
//п.8 Роли пользователей. Зарегистрированный пользователь может принадлежать одной из двух ролей: Исполнитель или Заказчик. По умолчанию присваивается Заказчик

    // Пример. Запись $formValidator->title = "Поле title должно быть заполнено"; __set()
    // Пример. Чтение $descriptionError = $formValidator->description; __get()

//КЛАСС разрешенные действия - блок Кнопки статуса. 

class _permitted_actions {

    // Принимаем что есть таблица зарегистрированных (авторизованных) пользователей - users  и таблица заданий - tasks 

    // СВОЙСТВА

    public $task_status = ''; // тестирование - значение зависит от $task
    public $task_endtime; // тестирование - значение зависит от $task, формат '2019-11-29 12:00:00'
    public $id_employer; // значение зависит от $task
    public $id_workman; // значение зависит от $task
    public $id_user; // значение зависит от $user
    public $user_group = ''; // тестирование - значение зависит от $user или от $id_user

    private $task_actions = []; // цель вызова объекта

    // СВОЙСТВА-КОНСТАНТЫ (описание действий, статусов, ролей)

    // Константные данные - роль или группы пользователя
    public $std_ugroups = ['employers/Заказчики', 'workmans/Исполнители']; 
    // Константные данные - статусы заданий. Согласно п.5 Список статусов и переходов
    public $std_statuses = [0 => 'Новое', 1 => 'Отменено', 2 => 'Выполняется/В работе/На исполнении', 3=> 'Завершено/Выполнено', 4 => 'Провалено'];

    /* 
        *** Заказчик
        п.4.7 Выполняется после нажатия заказчиком кнопки «Отменить» в модальном окне «Отмена» на странице просмотра.
        Статус задания меняется на «Отменено». Отмена заданий со статусом «На исполнении» невозможна.
        п.6.11 Модальные окна. Завершение задания - Вызывается, когда заказчик кликает на кнопку «Завершить». 
        После нажатия кнопки «Отправить» страница перезагружается, сохраняется новый отклик, а задание переходит в статус «Завершено».
        п.6.7 Переписка. Блок переписки между заказчиком и исполнителем. 
        Существует на странице, если на задание назначен исполнитель и скрыто от всех остальных пользователей сайта, кроме исполнителя и заказчика.
        !!! Кнопка Написать сообщение - нет в описания в ТЗ в п.6.11 Модальные окна !!! Существует если статус = Выполняется/В работе
        п.4.10 Отправка сообщения. Выполняется после отправки формы из блока «Переписка» на странице задания.
        п.4.8 Выполняется после нажатия заказчиком кнопки «Принять» из списка откликов (не кнопки статуса!!!) к заданию. 
        Последовательность действий: Сменить статус задания: «На исполнении». 
        п.6.7 Отклики. Для автора задания внутри каждого отклика показаны кнопки для выбора пользователя исполнителем или отклонения его отклика.
        Клик по кнопке «Подтвердить» вызывает процесс «Старт задания».
        *** Исполнитель
        п.4.4. Добавление отклика. Форма
        п.6.11 Модальные окна. Отклик на задание - Вызывается кликом на кнопку «Откликнуться». Не меняет статус задания.
        п.4.6 Отказ от задания. Выполняется после нажатия исполнителем кнопки «Отказаться» в модальном окне «Отказ от задания» на странице просмотра.
        Отказ меняет статус задания на «Провалено».
    */
    
    // Константные данные - кнопки-действия в зависимости от группы пользователя
    public $std_actions = 
    [
        'employers/Заказчики' => [0 => 'Отменить', 1 => 'Завершить', 2 => 'Написать сообщение', 3 => 'Принять/Подтвердить'],
        'workmans/Исполнители' => [0 => 'Откликнуться', 1 => 'Отказаться', 2 => 'Написать сообщение']
    ];

    // МЕТОДЫ МАГИЧЕСКИЕ

    // Получение базовые данные. $task, $user - ассоциативные массивы (строки из аналогичных таблиц $tasks, $users). 
    public function __construct ($task, $user) 
    {
        $this->task_status = $_GET['task_status_new'] ?? $task['status']; // Имитация записи в таблицу
        $this->task_endtime = strtotime($task['endtime']);
        $this->id_employer = $task['id_employer'];
        $this->id_workman = $task['id_workman'];
        $this->id_user = $user['id'];

        // по умолчанию пользователь является Заказчик
        $this->user_group = $this->std_ugroups[0]; 
        // Проверка пользователь что он Исполнитель
        if ($this->get_user_group($user)) {
            $this->user_group = $this->std_ugroups[1];
        }

    }

    // МЕТОДЫ 

    //Метод определяет новый статус, вызывается полсе правильном заполнении форм модальных окон, значит user определен и ему разрешены действия
    public function make_new_task_status($task_action = 'По событию') 
    {
        $task_status_new = '';
        
        // Изменение статуса автоматически при обращении к методу (по событию) 
        if(time() > $this->task_endtime) {

            if($this->task_status === $this->std_statuses[1] = 'Отменено') 
            {            }
            elseif($this->task_status === $this->std_statuses[2] = 'Выполняется/В работе/На исполнении') 
            {            }
            else
            {$task_status_new = $this->std_statuses[1] = 'Отменено';}

            /* не работает условие. Не понимаю!!!
            if($this->task_status != $this->std_statuses[1] = 'Отменено' && $this->task_status != $this->std_statuses[2] = 'Выполняется/В работе/На исполнении')
            {$task_status_new = $this->std_statuses[1] = 'Отменено'; print('canceled');}
            */
        }

        // Проверяем действия изменяющие статус задания 
        if ($task_action === $this->std_actions['employers/Заказчики'][0] = 'Отменить') {
            $task_status_new = $this->std_statuses[1] = 'Отменено';
        }
        elseif ($task_action === $this->std_actions['employers/Заказчики'][1] = 'Завершить') {
            $task_status_new = $this->std_statuses[3] = 'Завершено/Выполнено';
        }
        elseif ($task_action === $this->std_actions['employers/Заказчики'][3] = 'Принять/Подтвердить') {
            $task_status_new = $this->std_statuses[2] = 'Выполняется/В работе/На исполнении';
        }
        elseif ($task_action === $this->std_actions['workmans/Исполнители'][1] = 'Отказаться') {
            $task_status_new = $this->std_statuses[4] = 'Провалено';
        }

        // Пример - имитация записи статуса в таблицу
        if(!empty($task_status_new)) {
            
            if($task_status_new == $_GET['task_status_new']) {return $task_status_new;} 

            header("location: /classes/_tests.php?task_status_new=$task_status_new");
        } 

        return $task_status_new;
    }

    // Перед каждым вызовом функции вызывать $task_status_new, чтобы вызвать событие Задание просрочено, и сменить статус на отменено
    public function make_task_actions() 
    {
        // Цель метода - простой массив кнопки-действий в порядке отображения
        $task_actions = [];

        // Автоматически. Проверяем что задание просрочено (но такого статуса не существует!!!)
        if(time() > $this->task_endtime) {
            $this->make_new_task_status();
        }

        // Главное условие - пользователь является Заказчиком
        if($this->user_group === $this->std_ugroups[0] = 'employers/Заказчики' && $this->id_user === $this->id_employer) 
        {
            // создаем простой массив с возможными действиями Заказчика
            $actions = $this->std_actions['employers/Заказчики'];

            // В зависимости от статуса задания заполняем $task_actions для Заказчика
            if($this->task_status === $this->std_statuses[0] = 'Новое') {
                $task_actions[] = $actions[0] = 'Отменить';
            }
            if($this->task_status === $this->std_statuses[1] = 'Отменено') {
                //$task_actions по умолчанию
            }
            
            if($this->task_status === $this->std_statuses[2] = 'Выполняется/В работе/На исполнении') {
                $task_actions = [$actions[1], $actions[2]] = ['Завершить', 'Написать сообщение'];
            }
            if($this->task_status === $this->std_statuses[3] = 'Завершено/Выполнено') {
                //$task_actions по умолчанию
            }
            if($this->task_status === $this->std_statuses[4] = 'Провалено') {
                //$task_actions по умолчанию
            }

        }

        if($this->user_group === $this->std_ugroups[1] = 'workmans/Исполнители') 
        {
            // создаем простой массив с возможными действиями Исполнителя
            $actions = $this->std_actions['workmans/Исполнители'];

            // В зависимости от статуса задания заполняем $task_actions
            if($this->task_status === $this->std_statuses[0] = 'Новое') {
                $task_actions[] = $actions[0] = 'Откликнуться';
            }
            if($this->task_status === $this->std_statuses[1] = 'Отменено') {
                //$task_actions по умолчанию
            }
            // Также зависит от id пользователей в таблице task
            if($this->task_status === $this->std_statuses[2] = 'Выполняется/В работе/На исполнении') {
                if($this->id_user === $this->id_workman) {
                    $task_actions = [$actions[1], $actions[2]] = ['Отказаться', 'Написать сообщение'];
                }
            }
            if($this->task_status === $this->std_statuses[3] = 'Завершено/Выполнено') {
                //$task_actions по умолчанию
            }
            if($this->task_status === $this->std_statuses[4] = 'Провалено') {
                //$task_actions по умолчанию
            }

        }

        return $this->task_actions = $task_actions;
    }

    // МЕТОДЫ ДОПОЛНИТЕЛЬНЫЕ 

    public function get_task_actions()
    {
        return $this->task_actions;
    }

    // Проверяем пользователя, отметившие хотя бы одну категорию у себя в профиле
    public function get_user_group($user)
    {
        $user_categories = [];
        $name_categories = ['category_I', 'category_II', 'category_III'];
        foreach ($name_categories as $name_category) {
            $user_categories[$name_category] = $user[$name_category];
        }
        $user_categories = array_filter($user_categories);

        return !empty($user_categories);
    }

}