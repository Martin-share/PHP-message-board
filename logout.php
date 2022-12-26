<?php
/*
 * @Author: Martin
 * @Date: 2022-12-15 17:58:26
 * @Description: 退出登录，删cookie，跳转到首页 
 */


include __DIR__."/lib/common.php";
//退出登录
$logout = get('logout');
if($logout === 'true'){
    //发送 cookie
    setcookie("mbToken", NULL, time(), "/");
    #die(var_dump($_COOKIE));
    header("Location: ./index.php");
}