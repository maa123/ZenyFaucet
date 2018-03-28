<?php
/*
* install.php
* DBにテーブルを作成する
* SQLite対応はそのうちやるかも
*/
require_once './funcs.php';

$db = new Database();

$db->connect_mysql($db_host,$db_user,$db_password,$db_name);

$db->install_init();

echo "DBの設定が完了しました install.phpは削除してください";
