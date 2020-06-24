<?php
require_once('DownLoad.php');
$params = file_get_contents('php://stdin');
$params = unserialize($params);
$obj = new DownLoad($params['config'], $params['max_process_num'], $params['timeout']);
$handle_num = $obj->download();
