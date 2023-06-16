<?php
/*
 * @Author: Martin
 * @Date: 2022-12-15 17:58:26
 * @Description: 写日志的接口
 */



function write_logs(){
    $datetime = new DateTime();
    $now_time = $datetime->format("Y-m-d H:i:s.u");
    $text = "[{$now_time}]\t{$_SERVER['REQUEST_URI']}";
    $text .= "\n[GET 参数]\t".print_r($_GET, true)."\n";
    $text .= "[POST 参数]\t".print_r($_POST, true)."\n";
    $text .= "---------------------------------------------\n\n";

    return file_put_contents(__DIR__."/orther-log/".date("Y-m-d_H-i").".log", $text, FILE_APPEND);
}

function write_attack_logs(){
    $datetime = new DateTime();
    $now_time = $datetime->format("Y-m-d H:i:s.u");
    $text = "[{$now_time}]\t{$_SERVER['REQUEST_URI']}";
    $text .= "\n[GET 参数]\t".print_r($_GET, true)."\n";
    $text .= "[POST 参数]\t".print_r($_POST, true)."\n";
    $text .= "---------------------------------------------\n\n";

    return file_put_contents(__DIR__."/attack-log/".date("Y-m-d_H-i").".log", $text, FILE_APPEND);
}