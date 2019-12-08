<?php 

namespace TaskForce\General; 

class AvailableActions
{
    /* КОНСТАНТЫ */

    // statuses of task
    const STATUS_NEW = 'Новое';
    const STATUS_CANCELED = 'Отменено';
    const STATUS_RUNNING = 'Выполняется';
    const STATUS_COMPLETED = 'Выполнено';
    const STATUS_FAILED = 'Провалено';

    // roles of user
    const ROLE_CONTRACTOR = 'Исполнитель';
    const ROLE_CUSTOMER = 'Заказчик';

    // action buttons of task,
    // как работает ::class - https://www.php.net/manual/ru/function.get-class.php
    const ACTION_ADD_TASK = AddTaskAction::class; // Новое: или new
    const ACTION_OFFER = OfferAction::class; // Откликнутся: или respond 
    const ACTION_FAILURE = FailureAction::class; // Отказатся: или refuse
    const ACTION_CANCEL = CancelAction::class; // Отменить
    const ACTION_SET_CONTRACTOR = SetContractorAction::class; // Выбрать исполнителя: или executor
    const ACTION_COMPLETE = CompleteAction::class; // Завершить работу, для заказчика: или Finish
    const ACTION_ACCEPT = AcceptAction::class; // Принять работу, те стать исполнителем = добавить id в таблицу
    const ACTION_SEND_MESS = SendMessAction::class; // Написать сообщение

    /* СВОЙСТВА */

    // Свойства стандартные
    public $taskId; // new
    public $taskName; // new
    public $currentStatus; // обязательное свойство
    public $endDate; // обязательное свойство
    public $customerId; // обязательное свойство
    public $contractorId; // обязательное свойство

    /* МЕТОДЫ МАГИЧЕСКИЕ */

    /**
     * Конструктор - Слушать базовые данные страницы.
     * Task constructor.
     * @param $taskId
     * @param $taskName
     * @param $currentStatus
     * @param $endDate
     * @param $customerId
     * @param $contractorId
     */
    public function __construct ($taskId, $taskName, $currentStatus, $endDate, $customerId, $contractorId) {

        $this->taskId = $taskId;
        $this->taskName = $taskName;
        $this->currentStatus = $currentStatus;
        $this->endDate = $endDate;
        $this->customerId = $customerId;
        $this->contractorId = $contractorId;
    }

    /* МЕТОДЫ ЦЕЛЕВЫЕ */

    /**
     * Получение статусов простым обращением к ним
     * @return array
     */
    public function getStatuses(): array
    {
        return array(
            self::STATUS_NEW,
            self::STATUS_CANCELED,
            self::STATUS_COMPLETED,
            self::STATUS_RUNNING,
            self::STATUS_FAILED
        );
    }

    /**
     * Получение всех экшенов
     * @return array
     */
    public function getActions(): array
    {
        return array(
            self::ACTION_ADD_TASK,
            self::ACTION_OFFER,
            self::ACTION_FAILURE,
            self::ACTION_CANCEL,
            self::ACTION_SET_CONTRACTOR,
            self::ACTION_COMPLETE,
            self::ACTION_ACCEPT,
            self::ACTION_SEND_MESS
        );
    }

    /**
     * Получение текущего статуса задачи
     * @return string
     */
    public function getCurrentStatus(): string
    {
        return $this->currentStatus;
    }

/**
 * Получение список ролей
 * @return string
 * 
 */
    public function getRoles(): array 
    {
        return array(
            self::ROLE_CONTRACTOR,
            self::ROLE_CUSTOMER
        );
    }

/**
 * Получение id пользователя Заказчик/Исполнитель
 * @return int
 * 
 */
public function getMemberId($role): int 
{
    if($role === self::ROLE_CONTRACTOR) {
        return $this->contractorId;
    } 
    return $this->customerId;
}

public function getCustomerId(): string 
{
    return $this->customerId;
}

    /**
     * Получение следующего статуса
     * @param $action
     * @return string
     */
    public function getNextStatus($action): string
    {
        if (!in_array($action, $this->getActions())) {
            return 'Ошибка';
        }
        switch ($action) {
            case self::ACTION_ADD_TASK:
                return $this->currentStatus = self::STATUS_NEW;
                break;
                case self::ACTION_SET_CONTRACTOR:
            case self::ACTION_ACCEPT: // начать работать, согласится, те стать исполнителем 
                return $this->currentStatus = self::STATUS_RUNNING;
                break;
            case self::ACTION_CANCEL:
                return $this->currentStatus = self::STATUS_CANCELED;
                break;
            case self::ACTION_FAILURE:
                return $this->currentStatus = self::STATUS_FAILED;
                break;
            case self::ACTION_COMPLETE: // приянть работу, для заказчика
                return $this->currentStatus = self::STATUS_COMPLETED;
                break;
            default:
                return $this->currentStatus; // нет перехода - оставить текущий стаутус
        }
    }

    /**
     * Получение всех доступных действий исходя из роли пользователя
     * @param $userId
     * @return array
     */
    public function getAvailableActions($currentStatus, $userId, $role): array
    {
        if(!in_array($role, $this->getRoles())) {
            return 'Ошибка! Wrong! Error! Fault! Inaccuracy! Lapse! Mistake!';
        }

        if ($userId === $this->customerId) {
            switch ($currentStatus) {
                case self::STATUS_NEW:
                    return [self::ACTION_CANCEL, self::ACTION_SET_CONTRACTOR];
                case self::STATUS_RUNNING:
                    return [self::ACTION_COMPLETE, self::ACTION_SEND_MESS];
            }
        } elseif ($userId === $this->contractorId) {
            switch ($currentStatus) {
                case self::STATUS_NEW:
                    return [self::ACTION_ACCEPT];
                case self::STATUS_RUNNING:
                    return [self::ACTION_FAILURE, self::ACTION_SEND_MESS];
            }
        } elseif ($role === self::ROLE_CONTRACTOR) {
            switch ($currentStatus) {
                case self::STATUS_NEW:
                    return [self::ACTION_OFFER];
            }
        }
        
        // ??? Наверное, следует учесть Действие создать новое задание, при условии что отправлена форма, в которой $currentStatus = NULL.
        if(!$currentStatus && $role === self::ROLE_CUSTOMER) {
            return [self::ACTION_ADD_TASK];
        }

        return [];
    }

}
