DROP DATABASE IF EXISTS sms_names;
CREATE DATABASE sms_names CHARACTER SET utf8 COLLATE utf8_general_ci;
use sms_names;

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE users (
  id int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL
  )
  ENGINE INNODB
  COMMENT 'Пользователи';

CREATE TABLE name_sms (
  id int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` char(11) NOT NULL UNIQUE,
  user_id int UNSIGNED,
  CONSTRAINT FK_name_sms_users FOREIGN KEY (user_id)
    REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE,
  last_op_id int UNSIGNED COMMENT 'id последней операции с именем' ,
  CONSTRAINT FK_name_sms_name_operations FOREIGN KEY (last_op_id)
    REFERENCES name_operations (id) ON DELETE SET NULL ON UPDATE CASCADE
  )
  ENGINE INNODB
  COMMENT 'Имена отправителей смс';

CREATE TABLE name_statuses (
  id int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` char(10) NOT NULL
  )
  ENGINE INNODB
  COMMENT 'Статусы обработки имен отправителей смс';

CREATE TABLE name_operations (
  id int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name_id int UNSIGNED NOT NULL,
  CONSTRAINT FK_name_op_names FOREIGN KEY (name_id)
    REFERENCES name_sms (id) ON DELETE CASCADE ON UPDATE CASCADE,
  status_id int UNSIGNED NOT NULL,
  CONSTRAINT FK_name_op_statuses FOREIGN KEY (status_id)
    REFERENCES name_statuses (id) ON DELETE CASCADE ON UPDATE CASCADE,
  date_op datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время операции'
  )
  ENGINE INNODB
  COMMENT 'Дата присвоения статуса обработки для имен отправителей смс';

CREATE TABLE name_operation_reasons (
  name_operations_id int UNSIGNED NOT NULL,
  CONSTRAINT FK_name_op_reason_op_name FOREIGN KEY (name_operations_id)
    REFERENCES name_operations (id) ON DELETE CASCADE ON UPDATE CASCADE,
  message varchar(255)
  )
  ENGINE INNODB
  COMMENT 'Причина отказа в бронировании имени';

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO name_statuses VALUES
  (1, 'Ordered'),
  (2, 'Accepted'),
  (3, 'Rejected');

INSERT INTO users VALUES
  (1, 'Иванов Иван Иванович'),
  (2, 'Петров Петр Петрович');

INSERT INTO name_sms SET
  `name` = 'my_sms_name', user_id = 1;

INSERT INTO name_operations SET
  name_id = 1, status_id = 3, date_op = now();

UPDATE name_sms SET last_op_id = 1 WHERE id = 1;

INSERT INTO name_operation_reasons SET name_operations_id = 1, message = 'Incorrect name';