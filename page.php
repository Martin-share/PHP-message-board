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
// $count = sprintf("SELECT COUNT(*) AS c FROM `topic_comments_replies` where topic_id=%d",$topic);
// $count = $DB->query($count);
// $max_page = ceil(($count->fetch_assoc()['c']) / 10);
// $max_page = ceil(($count=$DB->query("SELECT COUNT(*) AS c FROM `topic_comments_replies` where topic_id=1")->fetch_assoc()['c']) / 10);
$max_page = ceil(($count=$DB->query(sprintf("SELECT COUNT(*) AS c FROM `topic_comments_replies` where topic_id=%d",$topic))->fetch_assoc()['c']) / 10);



//页码*每页显示多少数据      每页显示多少数据
$sql = sprintf("SELECT * FROM `topic_comments_replies` where topic_id=%d ORDER BY send_time DESC LIMIT %d,%d", $topic,(intval($page) - 1) *
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
                <li>
                        <a href="./likes_page.php"><span class="glyphicon glyphicon-log-in" aria-hidden="true"></span>点赞排序</a>
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
                  placeholder="*请输入留言内容" title="请先登录后操作">
        </textarea>
        <!-- #<input class="form-control" type="hidden" name="topic" value="<?php echo $topic; ?>"> -->

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




<!-- 主页的留言部分 -->
    <?php foreach ($cTmp as $v): 
        #var_dump($v['topic']);
        ?>
        <?php if (($topic == 0) or ($topic == $v['topic'])) : ?>
    <div id="<?php echo $v['cid'] + 10000; ?>" class="media" ;>
        <div class="media-left"  >
            <a target="_blank" href="userinfo.php?uid=<?php echo $v['uid'] + 10000; ?>">
                <img class="media-object img-circle" title="点击查看用户资料"
                    width="64px" height="64px"
                    src = "http://q2.qlogo.cn/headimg_dl?dst_uin=<?php echo $uTmp[$v['uid']]['qq']; ?>&spec=100"
                    alt="<?php echo $uTmp[$v['uid']]['qq'] ?> QQ头像">
                     <!-- 这里的src 调用了QQ接口 直接显示qq头像  -->
                     <!-- src="https://q1.qlogo.cn/g?b=qq&nk=<?php echo $uTmp[$v['uid']]['qq']; ?>&s=640" -->
            </a>
        </div>
        <div class="media-body">
            <div class="media-heading">
                <span class="nickname"><?php echo $uTmp[$v['uid']]['nickname']; ?></span>
                <?php 
                        $query = $DB->query(sprintf("SELECT * FROM `follow` WHERE `follower_id` = %d AND `following_id` = %d",$v['uid'],$uINFO['uid']));
                        if ($query && $query->fetch_assoc() > 0) {
                            echo '<a href="javascript:void(0);" onclick="deleteFollow(' . $v['uid'] . ')">取消关注</a>';
                        } else{
                            echo '<a href="javascript:void(0);" onclick="follow(' . $v['uid'] . ')">关注</a>';
                        }
                ?>
                <div class="secondary">
                        <span class="time" title="<?php echo date("Y-m-d H:i", $v['send_time']); ?>"
                              datetime="<?php echo date('c', $v['send_time']); ?>"><?php echo formatTime($v['send_time']); ?></span>
                    <span class="summary"><?php echo $uTmp[$v['uid']]['summary']; ?></span>
                    <span class="summary">点赞数：<?php echo $v['likes_count']; ?></span>
                    <?php 
                    $query = $DB->query(sprintf("SELECT * FROM `likes` WHERE cid=%d AND uid=%d ", $v['cid'], $uINFO['uid']));
                    if ($query && $query->fetch_assoc() > 0) {
                        # var_dump(sprintf("SELECT * FROM `likes` WHERE cid=%d AND uid=%d ", $v['cid'], $uINFO['uid']));
                        echo '<span class="summary"><code>已点赞</code></span>';
                    }
                    ?>
                </div>
            </div>
            <p><?php echo join("</p><p>", explode("\n", htmlspecialchars($v['contents']))); ?></p>

            <?php if ($isLogin): ?>
                    <div class="operate">
                        <?php 
                        $query = $DB->query(sprintf("SELECT * FROM `likes` WHERE cid=%d AND uid=%d ", $v['cid'], $uINFO['uid']));
                        if ($query && $query->fetch_assoc() > 0) {
                            echo '<a href="javascript:void(0);" onclick="deletelikeM(' . $v['cid'] . ')">取消点赞</a>';
                        } else{
                            echo '<a href="javascript:void(0);" onclick="likeM(' . $v['cid'] . ')">点赞</a>';
                        }
                        ?>
                        <!-- <a href="javascript:void(0);" onclick="likeM(<?php echo $v['cid']; ?>)">点赞</a> -->
                        <?php if (intval($uINFO['uid']) === intval($v['uid'])): ?>
                        <span style="padding: 0 5px"></span>
                        <a href="edit.php?cid=<?php echo $v['cid'] + 10000; ?>">编辑</a>
                        <span style="padding: 0 5px"></span>
                        <a href="javascript:deleteM(<?php echo $v['cid'] + 10000; ?>)">删除</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php
        if (!empty($v['reply_contents'])):
            $current_cid = $v['cid'];
            foreach ($cTmp as $key => $val):
                if ($val['cid'] === $current_cid):
        ?>
        <div class="media second">
            <div class="media-left">
                <a href="javascript:;">
                    <img class="media-object img-circle admin-icon" src="./static/img/guanliyuan1.jpg"
                        width="64px" height="64px">
                </a>
            </div>
            <div class="media-body second">
                <div class="nickname" style="color: #ffa014">管理员<?php echo $val['admin_id']?></div>
                <div class="secondary">
                    <span class="time" title="<?php echo date("Y-m-d H:i", $v['reply_time']); ?>"
                        datetime="<?php echo date('c', $val['reply_time']); ?>"><?php echo formatTime($val['reply_time']); ?></span>
                </div>
                <p><?php echo join("</p><p>", explode("\n", $val['reply_contents'])); ?></p>
            </div>
        </div>
        <?php
                    unset($cTmp[$key]);
                endif;
            endforeach;
        endif;
        ?>
        </div>
    </div>
    <?php 
    endif;

    endforeach; ?>

    <?php echo multipage(intval($max_page), intval($page)); ?>
    <span>共计：<?php echo $count; ?>条</span>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.0/dist/js/bootstrap.min.js"></script>
<script src="static/js/toastr.min.js"></script>
<script src="static/js/script.js"></script>
<script>
    /**
 * 点赞留言
 * @param cid
 * @returns {boolean}
 */
function likeM(cid) {  
    $.ajax({
        url: '/api.php?do=like' ,
        method: 'POST',
        dataType: 'json',
        data: {
            cid: cid
        },

        success: function (res) {
            // confirm('?')
            if (res.msg === undefined) {
                res.msg = '服务器暂时出现错误，请稍后再试！';
            }

            if (res.code === 0) {
                $.toastr.success('点赞成功！即将刷新页面', {
                    position: 'top-right',
                    time: 1800,
                    size: 'lg',
                    callback: function () {
                        // window.location.href = "msg.php";
                        location.reload()
                    }
                });
                // $("tr[data-cid="+cid+"]").remove();
            } else {
                $.toastr.warning('点赞失败，原因：' + res.msg, {
                    time: 6000,
                    position: 'top-right',
                    size: "lg"
                });
            }
        },
        error: function (e) {
            $.toastr.error('点赞失败，请检查你的网络或服务器暂时出现故障，请稍后再试！', {
                time: 8000,
                position: 'top-right'
            });
        }
    });

    // return true;
}

function deletelikeM(cid) {  
$.ajax({
    url: '/api.php?do=deletelike' ,
    method: 'POST',
    dataType: 'json',
    data: {
        cid: cid
    },

    success: function (res) {
        // confirm('?')
        if (res.msg === undefined) {
            res.msg = '服务器暂时出现错误，请稍后再试！';
        }

        if (res.code === 0) {
            $.toastr.success('取消点赞成功！即将刷新页面', {
                position: 'top-right',
                time: 1800,
                size: 'lg',
                callback: function () {
                    // window.location.href = "msg.php";
                    location.reload()
                }
            });
            // $("tr[data-cid="+cid+"]").remove();
        } else {
            $.toastr.warning('取消点赞失败，原因：' + res.msg, {
                time: 6000,
                position: 'top-right',
                size: "lg"
            });
        }
    },
    error: function (e) {
        $.toastr.error('取消点赞失败，请检查你的网络或服务器暂时出现故障，请稍后再试！', {
            time: 8000,
            position: 'top-right'
        });
    }
});
}

function follow(uid) {  
    $.ajax({
        url: '/api.php?do=follow' ,
        method: 'POST',
        dataType: 'json',
        data: {
            uid: uid
        },

        success: function (res) {
            // confirm('?')
            if (res.msg === undefined) {
                res.msg = '服务器暂时出现错误，请稍后再试！';
            }

            if (res.code === 0) {
                $.toastr.success('关注成功！即将刷新页面', {
                    position: 'top-right',
                    time: 1800,
                    size: 'lg',
                    callback: function () {
                        // window.location.href = "msg.php";
                        location.reload()
                    }
                });
                // $("tr[data-cid="+cid+"]").remove();
            } else {
                $.toastr.warning('关注失败，原因：' + res.msg, {
                    time: 6000,
                    position: 'top-right',
                    size: "lg"
                });
            }
        },
        error: function (e) {
            $.toastr.error('关注失败，请检查你的网络或服务器暂时出现故障，请稍后再试！', {
                time: 8000,
                position: 'top-right'
            });
        }
    });

    // return true;
}

function deleteFollow(uid) {  
    $.ajax({
        url: '/api.php?do=deleteFollow' ,
        method: 'POST',
        dataType: 'json',
        data: {
            uid: uid
        },

        success: function (res) {
            // confirm('?')
            if (res.msg === undefined) {
                res.msg = '服务器暂时出现错误，请稍后再试！';
            }

            if (res.code === 0) {
                $.toastr.success('取消关注成功！即将刷新页面', {
                    position: 'top-right',
                    time: 1800,
                    size: 'lg',
                    callback: function () {
                        // window.location.href = "msg.php";
                        location.reload()
                    }
                });
                // $("tr[data-cid="+cid+"]").remove();
            } else {
                $.toastr.warning('取消关注失败，原因：' + res.msg, {
                    time: 6000,
                    position: 'top-right',
                    size: "lg"
                });
            }
        },
        error: function (e) {
            $.toastr.error('取消关注失败，请检查你的网络或服务器暂时出现故障，请稍后再试！', {
                time: 8000,
                position: 'top-right'
            });
        }
    });

    // return true;
}
</script>
</body>
</html>
