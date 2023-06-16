<?php
/*
 * @Author: Martin
 * @Date: 2022-12-15 17:58:26
 * @Description: 用户登录
 */


include __DIR__."/logs/functions.php";
write_logs();
session_start();

if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
    include_once __DIR__ . "/lib/common.php";
    if (get('action') === 'login') 
    {

        // 获取当前用户的 IP
        $ip = getIp();
        // 检查 IP 的登录次数和封禁状态
        if (checkLoginAttempts($ip)){
            // 若超过限制
            die(echoApiData(4, '60秒内请求次数太多'));
        }

        $isEmail = false;
        $isAdmin = false;
        # FILTER_SANITIZE_MAGIC_QUOTES 过滤器对字符串执行 addslashes() 函数。
        # 该过滤器在预定义的字符串前面设置反斜杠。 预定义字符串 ' " \ NULL
        # trim 删空白字符
        $account = filter_var(trim(post('account')), FILTER_SANITIZE_MAGIC_QUOTES);
        $password = post('password');

        $n_l = mb_strlen($account, "utf-8");
        if ($n_l < 1) {
            die(echoApiData(1, '用户名长度必须 大于1'));
        }

        // 首先判断是不是管理员登录：
        if (substr($account, 0, 2) == '23') {
            $isEmail = false;
            $isAdmin = true;
            $sql = sprintf("SELECT * FROM admin WHERE email='%s' LIMIT 1", $account);
            # die(var_dump(substr($str, 0, 2)));
        } 
        else
        {
            //用户登录
            //首先判断输入的账号是否为邮箱
            if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
                $isEmail = true;
                $sql = sprintf("SELECT * FROM users WHERE email='%s' LIMIT 1", $account);
            } 
            // 不是邮箱，则判断是不是uid或者qq
            else {
                $sql = sprintf("SELECT * FROM users WHERE qq=%d OR uid=%d LIMIT 1", $account, intval
                    ($account)-10000);
                }
        }
        # die(echoApiData(0,var_dump(substr($str, 0, 2))));
        $res = $DB->query($sql);
        $row = $res->fetch_assoc();

        if ($row === NULL) {
            die(echoApiData(3, '不存在该账号，请注意仅能输入 UID、QQ或E-mail ！'));
        }
        $md5_password = strtolower(md5($password.$row['reg_ip']));


        if ($row['password'] !== $md5_password) {
            die(echoApiData(3, '账号与密码不匹配，请检查'));
        }   
        // if ($row['password'] !== $password) {
        //     die(echoApiData(3, '账号与密码不匹配，请检查'));
        //Cookie 有效期7天+随机秒数，避免被爆破
        $expireTime = time() + 24 * 60 * 60 * 7 + rand(26, 909);
        // $expireTime = time() + 10;
        //用户ID|-|cookie过期时间戳|-| MD5散列值(过期时间戳+密码)
        //MD5散列值(过期时间戳+密码) 这样保证的是解密时 与 cookie过期时间戳 进行校验
        if ($isAdmin){
            $code = ($row['admin_id']+10000) . "|-|" . $expireTime . "|-|" . crypt($row['password'] . $expireTime, '$1$rasmusle$');
            setcookie("isAdmin", '1', $expireTime, "/");
            # die(echoApiData(1, var_dump($row['admin_id'])));
        }else{
            $code = ($row['uid']+10000) . "|-|" . $expireTime . "|-|" . crypt($row['password'] . $expireTime, '$1$rasmusle$');
            setcookie("isAdmin", '0', $expireTime, "/");
        }

        //进行对称加密
        $encode = authcode($code, 'ENCODE', KEY);

        //发送 cookie
        setcookie("mbToken", $encode, $expireTime, "/");
        // 设置安全的 Cookie
        // setcookie("mbToken", $encode, $expireTime, "/", "", true, true); // 添加最后两个参数
        //第五个参数：域名 example.com
        //第六个参数：true，表示启用 HttpOnly 标志。这将防止客户端的 JavaScript 访问该 Cookie。
        //第七个参数：true，表示启用 Secure 标志。这将确保 Cookie 只能通过安全的 HTTPS 连接传输。

        // 设置token
        $token = bin2hex(random_bytes(32));
        session_start();
        $_SESSION['token'] = $token;
        setcookie('token', $token, $expireTime, '/');



        $ip = getIp();
        $_SESSION['login_attempts'][$ip] = 0;
        die(echoApiData(0, '登录账号成功！'));
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="Wenzhou Chan">
    <title>登陆账号 - PHP留言板</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.0/dist/css/bootstrap.css">
    <link rel="stylesheet" href="static/css/style.css">
    <link rel="stylesheet" href="static/css/toastr.min.css">
</head>
<body>
<nav class="navbar navbar-default navbar-fixed-top affix" role="navigation" id="slider_sub">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse"
                    data-target="#example-navbar-collapse">
                <span class="sr-only">切换导航</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php" style="margin-left: 16px;">PHP留言板</a>
        </div>
        <div class="collapse navbar-collapse navbar-right" id="example-navbar-collapse">
            <ul class="nav navbar-nav">
                <li>
                    <a href="index.php"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> 首页</a>
                </li>
                <li class="active">
                    <a href="login.php"><span class="glyphicon glyphicon-log-in"
                                                             aria-hidden="true"></span> 登录</a>
                </li>
                <li>
                    <a href="reg.php"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 免费注册</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container col-md-6 col-md-offset-3" style="padding-top: 72px;">
    <ol class="breadcrumb">
        <li><a href="index.php">首页</a></li>
        <li class="active">登陆账号</li>
    </ol>

    <form class="form-horizontal" onsubmit="return loginAccount(this)">
        <div class="form-group">
            <label for="account" class="col-sm-2 control-label">账号</label>
            <div class="col-sm-10">
                <input required="required" type="text" minlength="1"
                       class="form-control" name="account" placeholder="请输入 UID/QQ账号/邮箱 其中之一">
            </div>
        </div>
        <div class="form-group">
            <label for="password" class="col-sm-2 control-label">密码</label>
            <div class="col-sm-10">
                <input required="required" type="password" minlength="8" maxlength="16"
                       class="form-control" name="password" placeholder="请输入你的密码，8~16 位的密码">
            </div>
        </div>

        <div class="form-group">
            <div style="margin-top: 16px">
                <div class="col-xs-4">
                    <button type="reset" class="btn btn-default btn-lg btn-block">重置表单</button>
                </div>
                <div class="col-xs-8">
                    <button name="regBtn" data-loading-text="登录中..."
                            type="submit" class="btn btn-primary btn-lg btn-block">立即登录
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.0/dist/js/bootstrap.min.js"></script>
<script src="static/js/toastr.min.js"></script>
<script src="static/js/script.js"></script>
</body>
</html>
