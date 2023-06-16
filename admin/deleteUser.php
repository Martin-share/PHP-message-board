<?php
/*
 * @Author: Martin
 * @Date: 2022-12-19 15:52:46
 * @Description: 管理员删除用户接口
 */
include_once dirname(__DIR__) . "/lib/common.php";

$do = get('do');

if ($do === 'delete') {
    goto delete;
} else {
    die(echoApiData(-500, "此方法不存在！"));
}
//删除用户
delete:
$uid = post('uid','');
if (!$isLogin) {
    die(echoApiData(4, "请先登陆后再进行操作！"));
}

if ($isLoginAdmin) {
    die(echoApiData(3, "你没有超级管理员权限，无法进行操作！"));
}
if (empty($uid)) {
    die(echoApiData(1, "UID 不能为空，请刷新页面！"));
}
    
$uid = intval($uid)-10000;




$res = $DB->query(sprintf("SELECT uid,nickname,password,sex,qq,email FROM users WHERE uid=%d", $uid));
if (($row = $res->fetch_assoc())) {
    $DB->query(sprintf("DELETE FROM users WHERE uid=%d", $uid));
    if ($del){
        die(echoApiData(0, "删除用户成功！"));
    }

        die(echoApiData(-1, "删除用户失败，请稍后再试！"));
}
die(echoApiData(4, "该用户不存在，请刷新页面！"));