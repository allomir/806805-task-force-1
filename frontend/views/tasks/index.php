<?php 
use yii\helpers\Html;
use yii\widgets\ActiveForm;
// use yii\widgets\ActiveField; // Не используем

$this->title = 'Задания (Верстка browse.html)';
?>

<!-- Верстка browse.html контент -->

<section class="new-task">

    <div class="new-task__wrapper">
        <h1>Новые задания</h1>

        <!-- begin single task -->
        <?php foreach ($tasks as $task): ?>
        <div class="new-task__card">
            <div class="new-task__title">
                <a href="#" class="link-regular"><h2><?= ucfirst($task->name) ?></h2></a>
                <a class="new-task__type link-regular" href="#"><p><?= $task->category->name ?></p></a>
            </div>
            <div class="new-task__icon new-task__icon--<?= $task->category->symbol ?>"></div>
                <p class="new-task_description">
                    <?= $task->description ?>
                </p>
                <b class="new-task__price new-task__price--<?= $task->category->symbol ?>"><?= $task->price ?><b> ₽</b></b>
                <p class="new-task__place"><?= $task['address'] ?></p>
                <span class="new-task__time"><?= date( 'd.m.y',strtotime($task['add_time'])) ?></span>
        </div>
        <?php endforeach; ?>
        <!-- end single task -->

    </div>

    <!-- Верстка pagination --> 
    <div class="new-task__pagination">
        <ul class="new-task__pagination-list">
            <li class="pagination__item"><a href="#"></a></li>
            <li class="pagination__item pagination__item--current">
                <a>1</a></li>
            <li class="pagination__item"><a href="#">2</a></li>
            <li class="pagination__item"><a href="#">3</a></li>
            <li class="pagination__item"><a href="#"></a></li>
        </ul>
    </div>
    
</section>

<!-- Верстка right panel --> 
<section  class="search-task">
    <div class="search-task__wrapper">
        <!-- Форма начало -->
        <!-- <form class="search-task__form" name="test" method="post" action="#"> -->

        <?php 
        $form = ActiveForm::begin([
            'id' => 'task-form', 
            'options' => [
                'name' => 'test', // Имя формы не используется в ActiveForm, получение тела запроса производится по имени таблицы в модели в зависимости от модели в полях, которые автоматически назначаются при указании модели
                'class' => 'search-task__form'
            ],
            'fieldConfig' => [
                'template' => "{input}\n{label}", // Шаблон по умолчанию у большинства полей, также у каждого поля настраивается отдельно
                'options' => ['tag' => false], // отключение создания дополнительных тегов <div> для любых полей созданных с помощью activeForm на уровне $form->field (но не действует на new activeField), отключение Bootstrap does not work https://forum.yiiframework.com/t/how-to-generate-form-without-div-class-form-group/75797/2
            ], 
            // 'validationStateOn' => ActiveForm::VALIDATION_STATE_ON_INPUT, // или VALIDATION_STATE_ON_CONTAINER
        ]) 
        ?>

            <fieldset class="search-task__categories">
                <legend>Категории</legend>

                <!-- ПОЛЕ Категории тип чекбокс-список-->
                <!-- Верстка -->
                <!-- <input class="visually-hidden checkbox__input" id="1" type="checkbox" name="" value="" checked>
                <label for="1">Курьерские услуги </label>
                <input class="visually-hidden checkbox__input" id="2" type="checkbox" name="" value="" checked>
                <label  for="2">Грузоперевозки </label>
                <input class="visually-hidden checkbox__input" id="3" type="checkbox" name="" value="">
                <label  for="3">Переводы </label>
                <input class="visually-hidden checkbox__input" id="4" type="checkbox" name="" value="">
                <label  for="4">Строительство и ремонт </label>
                <input class="visually-hidden checkbox__input" id="5" type="checkbox" name="" value="">
                <label  for="5">Выгул животных </label> -->

                <?php /* ПОЛЕ Категории с помощью ActiveForm */
                    echo $form->field($tasksForm, 'categories', [
                        'template' => "{input}", // убираем показ label для общего контейнера списка чекбоксов
                    ])
                    ->checkboxList($tasksForm->getAttributeItems('categories'), [
                        'tag' => false, // Отклчает создание общего контейнера div
                        // 'name' => 'categories[]', // для общего контейнера div и общий для всех чекбоксов // Задается автоматически согласно метода load(), если задать вручную, то не попадает в load() в массив с именем формы как в модели
                        // 'unselect' => 0, // null - Не создвать скрытое поле, по умолчанию 0 - скрытое поле отправляется скрытое поле с именем value=0
                        'item' => function ($index, $label, $name, $checked, $value) {
                            $indexNew = ++$index;
                            // if($indexNew === 1 || $indexNew === 2) {$checked = true;}
                            return 
                                Html::checkbox($name, $checked, $options = [
                                    'id' => $indexNew,
                                    'class' => 'visually-hidden checkbox__input',
                                    'value' => $value, // !!!отличается от других, если не задавать то создается автоматически у всех всегда =1. !!!В данном случае необходимо использовать $value, для сохранения сортировки, так как значения хранятся в модели.
                                    // 'label' => $label, // В виде обертки не подходит
                                ]) . // !!!конкатенация
                                Html::label($label, $for = $index, $options = null);
                        }
                    ]); 
                ?>  
        
            </fieldset>

            <fieldset class="search-task__categories">
                <legend>Дополнительно</legend>
                
                <!-- ПОЛЕ Без откликов. тип чекбокс не выбран -->
                <!-- <input class="visually-hidden checkbox__input" id="6" type="checkbox" name="" value="">
                <label for="6">Без откликов</label>-->
                <?php 
                    echo $form->field($tasksForm, 'isOffers')
                        ->checkbox([
                                'id' => 20,
                                // 'name' => 'isOffers', // Задается автоматически согласно метода load(), если задать вручную, то не попадает в load() в массив с именем формы как в модели
                                'class' => 'visually-hidden checkbox__input',
                                // 'value' => '', // создается автоматически, по умолчанию всегда =1 !!!если задать значение, то влияет на сортировку но текущее состояние не отображается
                                // 'uncheck' => 0, // null - Не создвать скрытое поле, по умолчанию 0 - скрытое поле отправляется скрытое поле с именем value=0
                            ],
                            $enclosedByLabel = false // или true - чекбокс внутри, false - label отдельный и не содержит чекбокс 
                        ) 
                        // ->label($label = null, ['for' => '20', 'class' => null]) // Обязательно null вначале, нужен если изменить настройки label, аналогичен labelOptions
                    ;
                ?>  

                <!-- ПОЛЕ Удаленная работа. тип чекбокс выбран -->
                <!-- <input class="visually-hidden checkbox__input" id="7" type="checkbox" name="" value="" checked>
                <label for="7">Удаленная работа </label> -->
                <?php 
                    echo $form->field($tasksForm, 'isRemote')
                        ->checkBox([
                                'id' => 21,
                                // 'name' => 'isRemote', // Задается автоматически согласно метода load(), если задать вручную, то не попадает в load() в массив с именем формы как в модели
                                'class' => 'visually-hidden checkbox__input',
                                // 'value' => 1, // создается автоматически, по умолчанию всегда =1 !!!если задать значение, то влияет на сортировку но текущее состояние не отображается
                                // 'checked' => true, // отправляется, влияет на сортировку но не отображается, как будто остается всегда включенным
                                // 'uncheck' => 0, // null - Не создвать скрытое поле, по умолчанию 0 - скрытое поле отправляется скрытое поле с именем value=0
                            ],
                            $enclosedByLabel = false // или true - чекбокс внутри, false - label отдельный и не содержит чекбокс 
                        ) 
                        // ->label($label = null, ['for' => '21', 'class' => null]) // Обязательно null вначале, нужен если нужно изменить настройки label, аналогичен labelOptions
                    ;
                ?>  
            </fieldset>

            <!-- ПОЛЕ Период. тип выпадающий список|селектор-->
            <!-- Верстка -->
            <!-- <label class="search-task__name" for="8">Период</label>
                <select class="multiple-select input" id="8"size="1" name="time[]">
                <option value="day">За день</option>
                <option selected value="week">За неделю</option>
                <option value="month">За месяц</option>
            </select> -->
            <?php
                echo $form->field($tasksForm, 'dateInterval', [
                        'template' => "{label}\n{input}", // отличается от общего в форме, Обязательно !!!двойные кавычки, тк выводится на печать \n, символ переноса строки поддерживает только "\n"
                        'labelOptions' => ['for' => '30', 'class' => 'search-task__name'],
                        'inputOptions' => ['id' => '30', 
                            // 'name' => 'time', // Задается автоматически согласно метода load(), если задать вручную, то не попадает в load() в массив с именем формы как в модели
                            'class' => 'multiple-select input', 
                            'size' => 1,
                        ],
                    ])
                    ->dropdownList($tasksForm->getAttributeItems('dateInterval'), [
                        // 'options' => ['week' => ['selected' => true]] // Задать значение по умолчанию не получится, по умолчанию задается в объект модели в контроллере
                    ])
                ;
            ?>

            <!-- ПОЛЕ Поиск по названию. тип search-->
            <!-- Верстка -->
            <!-- <label class="search-task__name" for="9">Поиск по названию</label>
                <input class="input-middle input" id="9" type="search" name="q" placeholder=""> -->
            <?php 
                echo $form->field($tasksForm, 'search', [
                        'template' => "{label}\n{input}", // отличается от общего в форме, Обязательно !!!двойные кавычки, тк выводится на печать \n, символ переноса строки поддерживает только "\n"
                    ]) 
                    ->label($label = null, ['for' => '31','class' => 'search-task__name'])
                    ->input('search', [
                        'id' => '31', 
                        // 'name' => 'q', // Задается автоматически согласно метода load(), если задать вручную, то не попадает в load() в массив с именем формы как в модели
                        'class' => 'input-middle input', 
                        'placeholder' => ''
                    ])
                ;
            ?>

            <!-- <button class="button" type="submit">Искать</button> -->
            <?= Html::submitButton('Искать', ['class' => 'button']) ?>

        <?php ActiveForm::end() ?>
        <!-- Форма окончание -->
        <!-- </form> --> 
    </div>
</section>


