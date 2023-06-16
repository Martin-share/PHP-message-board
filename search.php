/*
* @Author: Martin
* @Date: 2022-12-18 21:35:18
* @Description: 留言板搜索页
*/

<?php
global $string;

include __DIR__ . "/logs/functions.php";
write_logs();

include_once __DIR__ . "/lib/common.php";

//默认第一页
$page = get('page', 1);

/**
 * 如果页码小于 0
 * 则返回首页
 */
if ($page < 1) {
    header("Location: index.php");
    die;
}

$FLAG = FALSE;
$uidTmp = []; //储存uid
$cTmp = []; //储存帖子

if (isset($_POST['content'])) {
    global $string;
    $string = $_POST['content'];
    $string = filter_var($string, FILTER_SANITIZE_STRING);

    $FLAG = TRUE;   
    // var_dump(($string));
}
//最大页码数量 ceil()向上取整
if ($FLAG){
$max_page = ceil(($count = $DB->query("SELECT COUNT(*) AS c FROM `comments` where contents like '%$string%'")->fetch_assoc()['c']) / 10);

//页码*每页显示多少数据      每页显示多少数据
// $sql = sprintf("SELECT * FROM `comments` ORDER BY send_time DESC LIMIT %d,%d", (intval($page) - 1) *
//     10, 10);

$sql = "SELECT * FROM `comments` Where contents like '%$string%' ORDER BY send_time DESC";
$res = $DB->query($sql);

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $uidTmp[] = $row['uid'];
        $cTmp[] = $row;
        }
        // var_dump($row['contents']);
        // // if (stripos($row['contents'], $string) == FALSE)
        // if ($row['contents'] == $string)
        // {
        //     var_dump($row['contents']);
        //     $uidTmp[] = $row['uid'];
        //     $cTmp[] = $row;
        // }
    }
    $res->free_result(); // 释放结果集
//    var_dump($cTmp);
}
$uTmp = []; //储存用户信息
$uidTmp = join(",", $uidTmp);

//使用 WHERE IN 语法批量查询用户信息
$res = $DB->query("SELECT uid,nickname,summary,sex,qq,email FROM users WHERE uid IN({$uidTmp})");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $uTmp[$row['uid']] = $row;
    }
    $res->free_result(); // 释放结果集
}
$FLAG = False;

?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="Martin">
    <title>PHP留言板</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.0/dist/css/bootstrap.css">
    <link rel="stylesheet" href="static/css/style.css">
    <link rel="stylesheet" href="static/css/toastr.min.css">
</head>

<body>
    <style type="text/css">
        body {
            /* background: url("./static/img/zhuan.png"); */
            background-color: white;
        }
    </style>
    <nav class="navbar navbar-default navbar-fixed-top affix" role="navigation" id="slider_sub">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#example-navbar-collapse">
                    <span class="sr-only">切换导航</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="./" style="margin-left: 16px;">PHP留言板</a>
            </div>
            <div class="collapse navbar-collapse navbar-right" id="example-navbar-collapse">
                <ul class="nav navbar-nav">
                    <li class="active">
                        <a href="./"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> 首页</a>
                    </li>

                    <?php if ($isLoginAdmin): ?>
                    <li class="dropdown">
                        <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" role="button"
                           aria-haspopup="true"
                           aria-expanded="false"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                            <?php echo $uINFO['email']; ?>
                            <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <?php if (intval($uINFO['nickname']) === 0): ?>
                                <li role="separator" class="divider"></li>
                                <li><a href="admin/index.php">后台管理</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php elseif ($isLogin): ?>
                    <li class="dropdown">
                        <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" role="button"
                           aria-haspopup="true"
                           aria-expanded="false"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                            <?php echo $uINFO['email']; ?>
                            <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="userinfo.php?uid=<?php echo 10000 + intval($uINFO['uid']) ?>">个人资料</a></li>
                            <li><a href="userinfo.php?uid=<?php echo 10000 + intval($uINFO['uid']) ?>#sendM">发布的留言</a>
                            </li>
                        </ul>
                    </li>
                    <?php else : ?>
                        <li>
                            <a href="./login.php"><span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> 登录</a>
                        </li>
                        <li>
                            <a href="./reg.php"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 免费注册</a>
                        </li>
                    <?php endif; ?>
                    <li><a href="javascript:logout()">退出登录</a></li>
                    <!-- <li><a href="logout.php?logout=true">退出登录</a></li> -->
                </ul>
            </div>
        </div>
    </nav>

    <!-- 留言框 -->
    <div class="container col-md-8 col-md-offset-2" style="padding-top: 72px;">
        <form class="form-horizontal" method="POST" action="search.php">
            <textarea class="form-control" rows="6" name="content" required="required" placeholder="*请输入要查找的留言内容"></textarea>

            <div class="form-group">
                <div style="margin-top: 16px">
                    <div class="col-xs-4">
                        <button type="reset" class="btn btn-default btn-lg btn-block">重置表单</button>
                    </div>
                    <div class="col-xs-8">
                        <button name="submitBtn" data-loading-text="查找留言中..." type="submit" class="btn btn-primary btn-lg btn-block">查找留言
                        </button>
                    </div>
                </div>
            </div>
        </form>


        <!-- 主页的留言部分 -->
        <?php foreach ($cTmp as $v) : ?>

            <div id="<?php echo $v['cid'] + 10000; ?>" class="media">
                <div class="media-left">
                    <a target="_blank" href="userinfo.php?uid=<?php echo $v['uid'] + 10000; ?>">
                        <img class="media-object img-circle" title="点击查看用户资料" width="64px" height="64px" src="https://q1.qlogo.cn/g?b=qq&nk=<?php echo $uTmp[$v['uid']]['qq']; ?>&s=640" alt="<?php echo $uTmp[$v['uid']]['qq'] ?> QQ头像">
                        <!-- 这里的src 调用了QQ接口 直接显示qq头像  -->
                    </a>
                </div>
                <div class="media-body">
                    <div class="media-heading">
                        <div class="nickname"><?php echo $uTmp[$v['uid']]['nickname']; ?></div>
                        <div class="secondary">
                            <span class="time" title="<?php echo date("Y-m-d H:i", $v['send_time']); ?>" datetime="<?php echo date('c', $v['send_time']); ?>"><?php echo formatTime($v['send_time']); ?></span>
                            <span class="summary"><?php echo $uTmp[$v['uid']]['summary']; ?></span>
                        </div>
                    </div>
                    <p><?php echo join("</p><p>", explode("\n", htmlspecialchars($v['contents']))); ?></p>

                    <?php if ($isLogin) : ?>
                        <?php if (intval($uINFO['uid']) === intval($v['uid'])) : ?>
                            <div class="operate">
                                <a href="edit.php?cid=<?php echo $v['cid'] + 10000; ?>">编辑</a>
                                <span style="padding: 0 5px"></span>
                                <a href="javascript:deleteM(<?php echo $v['cid'] + 10000; ?>)">删除</a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($v['reply'])) : ?>
                        <div class="media second">
                            <div class="media-left">
                                <a href="javascript:;">
                                    <img class="media-object img-circle admin-icon" src="./static/img/guanliyuan.png" \ width="64px" height="64px">
                                </a>
                            </div>
                            <div class="media-body second">
                                <div class="nickname" style="color: #ffa014">管理员</div>
                                <div class="secondary">
                                    <span class="time" title="<?php echo date("Y-m-d H:i", $v['reply_time']); ?>" datetime="<?php echo date('c', $v['reply_time']); ?>"><?php echo formatTime($v['reply_time']); ?></span>
                                </div>
                                <p><?php echo join("</p><p>", explode("\n", $v['reply'])); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <?php $string = $_POST['content']; ?>                
        <?php echo multipage(intval($max_page), intval($page),"&content=$string"); ?>
        <span>共计：<?php echo $count; ?>条</span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.0/dist/js/bootstrap.min.js"></script>
    <script src="static/js/toastr.min.js"></script>
    <script src="static/js/script.js"></script>
</body>

</html>