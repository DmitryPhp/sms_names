<?php

namespace App\Entity;

use Doctrine\DBAL\Driver\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\DBAL\Exception;

class NameSms
{

    private
        $conn,
        $validator,
        $name_sms_model,
        $logger;

    public function __construct(Connection $conn, ValidatorInterface $validator, LoggerInterface $logger, NameSmsModel $name_sms_model)
    {
        $this->name_sms_model = $name_sms_model;
        $this->conn = $conn;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * Вернем текущее состояние указанного буквенного имени
     * @param int $user_id
     * @param string $name
     * @return ResultJson
     */
    public function nameCheck(int $user_id, string $name): ResultJson
    {
        $this->name_sms_model->name = $name;

        $errors = $this->validator->validate($this->name_sms_model);
        if (count($errors) > 0) {
            $err_str = $errors->get(0);
            return new ResultJson(400, $err_str->getMessage());
        }

        $q = "SELECT ns.user_id, ns.name, name_statuses.name status, nor.message
                FROM name_sms ns, name_operations nop
                    LEFT JOIN name_statuses on nop.status_id = name_statuses.id
                    LEFT JOIN name_operation_reasons nor on nop.id = nor.name_operations_id
                WHERE ns.name = ? AND ns.id = nop.name_id
                ORDER BY nop.date_op DESC LIMIT 1";
        $name_status = $this->conn->fetchAssociative($q, [$this->name_sms_model->name]);
        if (!$name_status)
            return new ResultJson(200, 'Name is free');
        elseif ($name_status['user_id'] && $name_status['user_id'] != $user_id)
            return new ResultJson(400, 'Name is busy');
        else
            return new ResultJson(400, $name_status['status'] . ' ' . $name_status['message']);
    }

    /**
     * Попытаемся заказать буквенное имя
     * @param int $user_id
     * @param string $name
     * @return ResultJson
     */
    public function nameOrder(int $user_id, string $name): ResultJson
    {
        $this->conn->beginTransaction();
        $name_check = $this->nameCheck($user_id, $name);
        if ($name_check->status != 200) {
            $this->conn->rollBack();
            return $name_check;
        }

        try {
            $this->conn->insert('name_sms', [
                "user_id" => $user_id,
                "name" => $this->name_sms_model->name,
            ]);
            $name_id = $this->conn->lastInsertId();
            $this->conn->insert('name_operations', [
                "name_id" => $name_id,
                "status_id" => 1,
            ]);
            $name_op_id = $this->conn->lastInsertId();
            $this->conn->update('name_sms', ['last_op_id' => $name_op_id],
                ['id' => $name_id]);
        } catch (Exception $e) {
            $this->conn->rollBack();
            $err_msg = $e->getMessage();
            $this->logger->error($err_msg);
            return new ResultJson(400, $err_msg);
        }

        $this->conn->commit();
        return new ResultJson(200, '');
    }

    /**
     * Получим список буквенных имен пользователя
     * @param int $user_id
     * @return array
     */
    public function nameList(int $user_id): array
    {
        $q = "SELECT ns.user_id, ns.name, name_statuses.name status, nop.date_op, nor.message
                FROM name_sms ns
                    LEFT JOIN name_operations nop ON ns.last_op_id = nop.id
                    LEFT JOIN name_statuses ON nop.status_id = name_statuses.id
                    LEFT JOIN name_operation_reasons nor ON nop.id = nor.name_operations_id
                WHERE ns.user_id = ? ORDER BY nop.date_op;";
        $name_list = $this->conn->fetchAllAssociative($q, [$user_id]);
        return $name_list;
    }

}