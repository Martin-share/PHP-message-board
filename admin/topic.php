<?php
/*
 * @Author: Martin
 * @Date: 2023-06-10 18:44:59
 * @Description: 
 */
/*
 * @Author: Martin
 * @Date: 2022-12-15 17:58:26
 * @Description: 用户留言管理
 */


include_once dirname(__DIR__) . "/lib/common.php";

$page = get('page', 1);


$topic_id = intval(get('topic_id',0));

$action = get('action', '');
//删除操作
if ($action == 'delete') {

    # die(var_dump(sprintf("DELETE FROM `topics` WHERE topic_id=%d", $topic_id)));
    $res = $DB->query(sprintf("DELETE FROM `comments` WHERE topic_id=%d", $topic_id));
    $res = $DB->query(sprintf("DELETE FROM `topics` WHERE topic_id=%d", $topic_id));
    if ($res) {
        echo "<script>
            alert('删除话题成功');
            setTimeout(function() {
                window.location.href = '/admin/topic.php';
            }, 10);
        </script>";
        die();
    } else{
        echo "<script>
            alert('删除话题失败');
            setTimeout(function() {
                window.location.href = '/admin/topic.php';
            }, 10);
        </script>";
        die();
    }    

}

$res = $DB->query("SELECT * FROM `topic_counts`");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $cTmp[$row['topic_id']] = $row;
        # die(var_dump($row));
    }
    $res->free_result(); // 释放结果集
}

var_dump($row);
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
    <title>用户留言管理 - PHP留言板</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.0/dist/css/bootstrap.css">
    <link rel="stylesheet" href="../static/css/style.css">
    <link rel="stylesheet" href="../static/css/toastr.min.css">
</head>

<body>

    <!-- 导航栏 -->
    <nav class="navbar navbar-default navbar-fixed-top affix" role="navigation" id="slider_sub">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#example-navbar-collapse">
                    <span class="sr-only">切换导航</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="../index.php" style="margin-left: 16px;">PHP留言板</a>
            </div>
            <div class="collapse navbar-collapse navbar-right" id="example-navbar-collapse">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="./index.php"><span class="glyphicon glyphicon-th-large" aria-hidden="true"></span>
                            用户管理</a>
                    </li>
                    <li class="active">
                        <a href="./topic.php">
                            <span class="glyphicon glyphicon-home" aria-hidden="true"></span>话题管理</a>
                    </li>
                    <li>
                        <a href="msg.php">
                            <span class="glyphicon glyphicon-user" aria-hidden="true"></span> 留言管理</a>
                    </li>
                    <li>
                        <a href="../index.php">
                            <span class="glyphicon glyphicon-home" aria-hidden="true"></span> 返回首页</a>
                    </li>
                    <?php if ($isLogin) : ?>
                        <li class="dropdown">
                            <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                <?php echo $uINFO['email']; ?>
                                <span class="caret"></span></a>
                            <ul class="dropdown-menu">

                                <?php if (!$isAdminLogin) : ?>
                                    <li role="separator" class="divider"></li>
                                    <li><a href="index.php">后台管理</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php else : ?>
                        <li>
                            <a href="../login.php">
                                <span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> 登录</a>
                        </li>
                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>

    <div class="container col-md-8 col-md-offset-2" style="padding-top: 72px;">
        <?php if (!$isLogin) : ?>
            <div class="jumbotron" style="color: #a94442; background-color: #f2dede;">
                <h1>请先登录账号 : (</h1>
                <p></p>
                <p class="text-right"><a class="btn btn-primary btn-lg" href="../login.php" role="button">登录账号</a></p>
            </div>
        <?php elseif ($isAdminLogin) : ?>
            <div class="jumbotron" style="color: #faf6f6; background-color: #e2aa56;">
                <h1><span class="glyphicon glyphicon-remove-sign"></span> 你的账号不具备超级管理员权限 : (</h1>
                <p></p>
                <p class="text-right"><a class="btn btn-danger btn-lg" href="../index.php" role="button">返回首页</a></p>
            </div>
        <?php else : ?>
            <div class="alert alert-warning" role="alert">
                <p>请注意，删除话题后，需要刷新页面才能查看最新结果</p>
            </div>
            <blockquote>
                话题管理
            </blockquote>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>topic_id</th>
                        <th>话题名</th>
                        <th>话题留言数量</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($cTmp as $v) : 
                        # var_dump($v);
                        ?>
                        
                        <tr data-cid="<?php echo $v['topic_id']; ?>">                                                    
                            <td data-type="reply_id"><?php echo $v['topic_id'] ?></td>
                            <td data-type="reply"><?php echo $v['name']; ?></td>
                            <td><?php echo $v['comment_count']; ?></td>
                            <td style="min-width: 44px">
                            <a href="topic.php?action=delete&topic_id=<?php echo $v['topic_id']?>">删除</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>

        <?php endif; ?>
    </div>


    <div class="modal fade" id="editUser" tabindex="-1" data-backdrop="static" aria-hidden="true" role="dialog" aria-labelledby="editUser">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="editUserLabel">编辑用户资料</h4>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="recipient-name" class="control-label">U资料ID:</label>
                            <input type="text" class="form-control" id="uid" disabled>
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="control-label">用户名:</label>
                            <input type="text" class="form-control" id="nickname">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="control-label">密码:</label>
                            <input type="text" class="form-control" minlength="8" maxlength="16" id="password" placeholder="留空表示不修改，长度在 8~16 位数">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="control-label">性别:</label>
                            <select id="sex" class="form-control">
                                <option value="0" disabled selected>请选择性别</option>
                                <option value="1">男</option>
                                <option value="2">女</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="control-label">QQ:</label>
                            <input type="text" class="form-control" id="qq">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="control-label">E-mail:</label>
                            <input type="text" class="form-control" id="email">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                    <button type="button" class="btn btn-primary" data-loading-text="编辑中..." id="confirm-edit">确认编辑
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="reply" tabindex="-1" data-backdrop="static" aria-hidden="true" role="dialog" aria-labelledby="reply">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="replyLabel">编辑用户资料</h4>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="recipient-name" class="control-label">用户:</label>
                            <input id="replyUser" type="text" class="form-control" placeholder="留言的用户" disabled>
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="control-label">reply_id:</label>
                            <input id="replyID" type="text" class="form-control" placeholder="reply_id" disabled>
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="control-label">留言内容: <a id="editLink">点击编辑留言</a></label>
                            <textarea id="replyContent" class="form-control" rows="6" required="required" disabled placeholder="留言内容"></textarea>
                            <!--                        <span class="help-block">如果你要修改它的留言内容，请点击“操作”中的“编辑”</span>-->
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="control-label">回复留言:</label>
                            <textarea class="form-control" rows="6" required="required" id="replyText" placeholder="回复Ta的留言"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                    <button type="button" class="btn btn-primary" data-loading-text="回复..." id="confirm-reply">确认回复
                    </button>
                </div>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.0/dist/js/bootstrap.min.js"></script>
    <script src="../static/js/toastr.min.js"></script>
    <script src="../static/js/script.js"></script>
</body>

</html>