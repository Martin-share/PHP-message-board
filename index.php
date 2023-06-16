<?php
/*
 * @Author: Martin
 * @Date: 2022-12-15 17:58:26
 * @Description: 留言板主页，显示留言内容
 */


include __DIR__."/logs/functions.php";
write_logs();

include_once __DIR__ . "/lib/common.php";

session_start();
$token2 = bin2hex(random_bytes(32));
$_SESSION['token2'] = $token2;



//默认第一页
$page = get('page', 1); 
$topic = get('topic', 0); 
/**
 * 如果页码小于 0
 * 则返回首页
 */
if ($page < 1) {
    header("Location: index.php");
    die;
}


$uidTmp = [];//储存uid
$cTmp = [];//储存帖子

//最大页码数量 ceil()向上取整
$max_page = ceil(($count=$DB->query("SELECT COUNT(*) AS c FROM `topic_comments_replies`")->fetch_assoc()['c']) / 10);

//页码*每页显示多少数据      每页显示多少数据
$sql = sprintf("SELECT * FROM `topic_comments_replies` ORDER BY send_time DESC LIMIT %d,%d", (intval($page) - 1) *
    10, 10);
$res = $DB->query($sql);

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $uidTmp[] = $row['uid'];
        $cTmp[] = $row;
    }
    $res->free_result();// 释放结果集
}

$uTmp = [];//储存用户信息
$uidTmp = join(",", $uidTmp);

//使用 WHERE IN 语法批量查询用户信息
$res = $DB->query("SELECT uid,nickname,summary,sex,qq,email FROM users WHERE uid IN({$uidTmp})");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $uTmp[$row['uid']] = $row;
    }
    $res->free_result();// 释放结果集
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
    <meta name="author" content="Martin">
    <title>PHP留言板</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.0/dist/css/bootstrap.css">
    <link rel="stylesheet" href="static/css/style.css">
    <link rel="stylesheet" href="static/css/toastr.min.css">
</head>
<body>
    <style type="text/css">
    /* body {
        background: url("./static/img/zhuan.png");
    } */
    
    </style>
<style>
    .horizontal-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .inline-item {
        display: inline-block;
        margin-right: 10px; /* Adjust spacing between buttons */
        text-align: center; /* Center align the content */
    }

    .button {
        display: inline-block;
        padding: 20px 40px; /* Adjust padding to increase button size */
        font-size: 20px; /* Adjust font size */
        text-decoration: none;
        background-color: #f0f0f0; /* Button background color */
        color: #333; /* Button text color */
        border: none;
        border-radius: 5px; /* Adjust border radius to make button corners round */
        line-height: 1; /* Center align the button text vertically */
    }
    .form-container {
        transform: scale(1.2);
        transform-origin: top left;
    }

    /* Additional styling as needed */
</style>

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
            <a class="navbar-brand" href="./" style="margin-left: 16px;">PHP留言板</a>
        </div>
        <div class="collapse navbar-collapse navbar-right" id="example-navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active">
                    <a href="./"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> 首页</a>
                </li>
                <li>
                    <a href="search.php"><span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span> 查找</a>
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
                <?php else: ?>
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
    <form class="form-horizontal" onsubmit="return submitMessage(this)">
        <textarea class="form-control" rows="6" name="content" required="required"
                  placeholder="*请输入留言内容" title="请先登录后操作"></textarea>
        <input type="hidden" name="topic" value="<?php echo $topic; ?>"> <!-- 设置 topicId 的值 -->


        <div class="form-group">
            <div style="margin-top: 16px">
                <div class="col-xs-4">
                    <button type="reset" class="btn btn-default btn-lg btn-block">重置表单</button>
                </div>
                <div class="col-xs-8">
                    <button name="submitBtn" data-loading-text="发表留言中..."
                            type="submit" class="btn btn-primary btn-lg btn-block">发表留言
                    </button>
                </div>
            </div>
        </div>
    </form>
    
    <?php
    $res = $DB->query("SELECT * from topics");
    echo "<div class='sidebar'>";
    echo "<h3>Topics</h3>";
    echo '<div class="form-container">
    <form method="POST" action="./addTopic.php">
        <label for="content">添加话题：</label>
        <input type="text" name="topic" id="content" required>
        <button type="submit" name="submit">提交</button>
    </form>
</div>';

    echo "<br>";

    echo "<ul class='horizontal-list'>"; // Add a CSS class for styling

    if ($res) {
        while ($tmp = $res->fetch_assoc()) {
            echo "<li class='inline-item'><a class='button' href='page.php?topic={$tmp['topic_id']}'>{$tmp['name']}</a></li>";
        }
        $res->free_result();// 释放结果集
    }
    echo "</ul>";
    echo "</div>";
    ?>
 


</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.0/dist/js/bootstrap.min.js"></script>
<script src="static/js/toastr.min.js"></script>
<script src="static/js/script.js"></script>
</body>
</html>
