<?php

require_once('../../htdocs/bootstrap.php');

$dbh = E()->getDB();
$res = $dbh->select('SHOW TABLES LIKE "%_uploads"');
if ($res) {

    echo "\nSET names utf8;\n\n";
    echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach($res as $row) {
        $tableName = current($row);
        if ($tableName == 'share_uploads') continue;
        echo '-- ' . $tableName . "\n";

        $struct = $dbh->getColumnsInfo($tableName);
        $mainTableName = str_replace('_uploads', '', $tableName);
        // принимаем решение об обновлении по наличию у поля upl_id PRI
        if (isset($struct['upl_id']) && isset($struct['upl_id']['index']) && $struct['upl_id']['index'] == 'PRI') {

            // 0. добавляем индексы на FK поля
            foreach($struct as $fieldName => $fieldProps) {
                if (isset($fieldProps['key']) && isset($fieldProps['key']['tableName']) && $fieldName != 'upl_id') {
                    echo sprintf("ALTER TABLE `%s` ADD INDEX `%s` (`%s`);\n", $tableName, $fieldName, $fieldName);
                }
            }

            // 1. удаляем PRIMARY KEY
            echo sprintf("ALTER TABLE `%s` DROP PRIMARY KEY;\n", $tableName);

            // 2. добавляем новый PK auto_increment
            $new_prefix = '';
            $table_parts = explode('_', $tableName);
            foreach($table_parts as $part) {
                $new_prefix .= substr($part, 0, 1);
            }
            $new_pk = $new_prefix . '_id';
            echo sprintf("ALTER TABLE `%s` ADD COLUMN `%s` int(10) unsigned auto_increment FIRST, ADD PRIMARY KEY `%s` (`%s`);\n", $tableName, $new_pk, $new_pk, $new_pk);

            // 3. добавляем поле session_id
            echo sprintf("ALTER TABLE `%s` ADD COLUMN `session_id` varchar(255) DEFAULT NULL;\n", $tableName);
            echo sprintf("ALTER TABLE `%s` ADD INDEX `session_id` (`session_id`);\n", $tableName);

            // 4. делаем FK на основную таблицу NULLABLE
            foreach($struct as $fieldName => $fieldProps) {
                if (isset($fieldProps['key']) && isset($fieldProps['key']['tableName']) && $fieldProps['key']['tableName'] == $mainTableName) {
                    echo sprintf("ALTER TABLE `%s` CHANGE COLUMN `%s` `%s` int(10) unsigned DEFAULT NULL;\n", $tableName, $fieldName, $fieldName);
                }
            }

            // 5. переименовываем _order_num
            $new_order_num = $new_prefix . '_order_num';
            echo sprintf("ALTER TABLE `%s` CHANGE COLUMN `upl_order_num` `%s` int(10) unsigned NOT NULL DEFAULT 0;\n", $tableName, $new_order_num);
            echo sprintf('ALTER TABLE `%s` ADD INDEX `%s_idx` (`%2$s`);'.PHP_EOL, $tableName, $new_order_num);

            echo "\n";
        } else {
            echo "-- Already patched\n\n";
        }
    }
    echo "\nSET FOREIGN_KEY_CHECKS=1;\n\n";
}
