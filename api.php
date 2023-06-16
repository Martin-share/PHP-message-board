<?php
/*
 * @Author: Martin
 * @Date: 2022-12-15 17:58:26
 * @Description: 用户 submit delete edit
 */



include_once __DIR__ . "/lib/common.php";
include __DIR__."/logs/functions.php";

$token2 = post('token2','');

if (!checkReferer()){
    write_attack_logs();
    die(echoApiData(-100, "防csrf攻击"));
}
if (validateToken($_SESSION['token'])){
    write_attack_logs();
    die(echoApiData(-100, "防csrf攻击"));
}

$do = get('do');

if ($do === 'submit') {
    goto submit;
} else if ($do === 'delete') {
    goto delete;
} else if ($do === 'edit') {
    goto edit;
} else if ($do === 'like'){
    goto like;
}else if ( $do === 'deletelike'){
    goto deletelike;
} else if ( $do === 'follow'){
    goto follow;
}else if ( $do === 'deleteFollow'){
        goto deleteFollow;
} else {
    write_attack_logs();
    die(echoApiData(-500, "此方法不存在！"));
}

//修改用户个人资料内容
edit:
$nickname = addslashes(post('nickname'));//昵称
$sex = addslashes(post('sex'));//性别
$summary = addslashes(post('summary'));//更新签名

$_password = addslashes(post('_password'));//源密码
$password = addslashes(post('password'));//密码

if(!isset($uINFO['uid'])){
    die(echoApiData(3, '请先登录再进行操作'));
}

if(!$nickname){
    die(echoApiData(5, '昵称不能为空'));
}

if(!$sex){
    die(echoApiData(5, '性别不能为空'));
}
$flag = FALSE;

if(!empty($_password))
{
    $_password = strtolower(md5($_password.$uINFO['reg_ip']));
    if($_password !== $uINFO['password']){
        write_attack_logs();
        die(echoApiData(6, '原密码错误'));
    }
    $flag = TRUE; //原密码输出正确

}else{
    $password = $uINFO['password'];
}

if(!$password){
    die(echoApiData(5, '请输入你要修改的密码，长度在8~16位之间'));
}

if($flag){
    // 原密码输入且正确。
    $password = strtolower(md5($password.$uINFO['reg_ip']));
}

//更新

$res = $DB->query(sprintf("UPDATE users SET nickname='%s', sex=%d, summary='%s', password='%s' WHERE uid=%d",
    $nickname, $sex, $summary, $password, $uINFO['uid']
));

if($res){
    die(echoApiData(0, "修改个人资料成功！"));
}

write_attack_logs();
die(echoApiData(-1, "修改个人资料失败，请稍后再试！", [$DB->error]));



//发表留言
submit:
$content = post('content', '');
if(isset($_SERVER['HTTP_REFERER'])){
    $referer = $_SERVER['HTTP_REFERER'];
    $query = parse_url($referer, PHP_URL_QUERY);
    parse_str($query, $params);

    if(isset($params['topic'])){
        $topic = $params['topic'];
        # echo $topic;
    }
    else{
        $topic = 1;
    }
}
// $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
// 转义特殊字符
$content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
// 移除危险的 HTML 标签和属性
$content = strip_tags($content, '<b><strong><i><em><u><s><del><p><br><ul><ol><li>');
// 移除 JavaScript 事件处理属性
$content = preg_replace('/\bon\w+=\s*(["\']\s*?).*?\1/si', '', $content);
// 移除内联样式属性
$content = preg_replace('/\s*style\s*=\s*(["\']\s*?).*?\1/si', '', $content);
// 移除 HTML 注释
$content = preg_replace('/<!--(.|\s)*?-->/', '', $content);
// 不良词汇
$content = filterMessage($content);
if (!$isLogin) {
    die(echoApiData(3, "请先登陆账号后再进行留言！"));
}

if (empty($content)) {
    die(echoApiData(1, "留言内容不能为空"));
}

// 修改过后的预处理语句
// $query = $DB->prepare("INSERT INTO comments (uid, contents, send_time, post_ip) VALUES (?, ?, ?, ?)");
// $res = $query->execute([$uINFO['uid'], $content, time(), getIp()]);

// $sql = "INSERT INTO comments (uid, contents, send_time, post_ip) VALUES (?, ?, ?, ?)";
// $stmt = $DB->prepare($sql);
// $time = time();
// $ip = getIP();
// $stmt->bind_param("isss", $uINFO['uid'], $content, $time ,$ip);
// $stmt->execute();
// 之前的 数字或布尔值，addslashes()无法提供适当的转义。
# die(echoApiData(0, var_dump($uINFO['uid'])));



$content = addslashes($content);//评论内容发转义
$res = $DB->query(sprintf("INSERT INTO comments (uid, contents, send_time, post_ip, topic_id) VALUES (%d, '%s', %s, '%s', '%d')",
    $uINFO['uid'], $content, time(), getIp(), $topic
));

// die(var_dump(sprintf("INSERT INTO comments (uid, contents, send_time, post_ip, topic_id) VALUES (%d, '%s', %s, '%s', '%d')",
// $uINFO['uid'], $content, time(), getIp(), $topicId
// )));

// 之前的 数字或布尔值，addslashes()无法提供适当的转义。
// $content = addslashes($content);//评论内容发转义
// $res = $DB->query(sprintf("INSERT INTO comments (uid, contents, send_time, post_ip) VALUES (%d, '%s', %d, '%s')",
//     $uINFO['uid'], $content, time(), getIp()
// ));

if ($res) {
    die(echoApiData(0, "发表留言成功！"));
}

// die(var_dump(sprintf("INSERT INTO comments (uid, contents, send_time, post_ip) VALUES (%d, '%s', %s, '%s')",
// $uINFO['uid'], $content, time(), getIp()
// )));
write_attack_logs();
die(echoApiData(-1, "管理员不发留言，请直接回复"));


//删除留言
delete:
$cid = post('cid', '');

if (!$isLogin) {
    die(echoApiData(3, "请先登陆账号后再进行删除留言！"));
}

if (empty($cid)) {
    write_attack_logs();
    die(echoApiData(1, "CID 不能为空，请刷新页面！"));
}

$cid = intval($cid) - 10000;

$query = $DB->prepare("SELECT uid FROM comments WHERE cid = ?");
$query->bind_param("i", $cid);
$query->execute();
$res = $query->get_result();
// $res = $DB->query(sprintf("SELECT uid FROM comments WHERE cid=%d", $cid));


if ($res &&($row = $res->fetch_assoc())) {
    //判断是否为自己的留言
    //不是自己的禁止删除
    if (!$isAdminLogin){
    }elseif (intval($row['uid']) !== intval($uINFO['uid'])) {
        write_attack_logs();
        die(echoApiData(5, "不能删除他人留言，请刷新页面！"));
    }
    
    $deleteQuery = $DB->prepare("DELETE FROM comments WHERE cid = ?");
    $deleteQuery->bind_param("i", $cid);
    $deleteQuery->execute();

    $del = $DB->query(sprintf("DELETE FROM comments WHERE cid=%d", $cid));
    if ($del) {
        die(echoApiData(0, "删除留言成功！"));
    }

    die(echoApiData(-1, "删除留言失败，请稍后再试！"));
}

write_attack_logs();
die(echoApiData(4, "该留言不存在，请刷新页面！"));

// 点赞
like:
$cid = post('cid', '');

if (!$isLogin) {
    die(echoApiData(3, "请先登陆账号后再进行删除点赞！"));
}

if (empty($cid)) {
    write_attack_logs();
    die(echoApiData(1, "CID 不能为空，请刷新页面！"));
}

$cid = intval($cid);

$query = $DB->query(sprintf("SELECT * FROM `likes` WHERE cid=%d AND uid=%d ", $cid, $uINFO['uid']));
if ($query && $query->fetch_assoc() > 0) {
    die(echoApiData(-1, "重复点赞"));
}

//更新操作
$sql = sprintf("INSERT INTO likes (`cid`, `uid`, `like_time`) VALUES (%d, %d, '%s')", $cid, $uINFO['uid'], time());
# die(sprintf("INSERT INTO likes (`cid`, `uid`, `like_time`) VALUES (%d, %d, '%s')", $cid, $uINFO['uid'], time()));
if ($DB->query($sql)) {
    die(echoApiData(0, "点赞成功！"));
}
write_attack_logs();

die(echoApiData(1, "点赞失败"));


deletelike:

$cid = post('cid', '');

if (!$isLogin) {
    die(echoApiData(3, "请先登陆账号后再进行取消点赞！"));
}

if (empty($cid)) {
    write_attack_logs();
    die(echoApiData(1, "CID 不能为空，请刷新页面！"));
}

$cid = intval($cid);


//更新操作
$sql = sprintf("DELETE FROM likes WHERE cid=%d AND uid=%d", $cid, $uINFO['uid']);
# die(sprintf("INSERT INTO likes (`cid`, `uid`, `like_time`) VALUES (%d, %d, '%s')", $cid, $uINFO['uid'], time()));
if ($DB->query($sql)) {
    die(echoApiData(0, "取消点赞成功！"));
}
write_attack_logs();

die(echoApiData(1, "取消点赞失败"));

follow:

$uid = post('uid', '');

if (!$isLogin) {
    die(echoApiData(3, "请先登陆账号后再进行取消点赞！"));
}

if (empty($uid)) {
    write_attack_logs();
    die(echoApiData(1, "uid 不能为空，请刷新页面！"));
}

$uid = intval($uid);


//更新操作
$sql = sprintf("INSERT INTO `follow` (`follower_id`,`following_id`, `follow_time`) VALUES ( %d, %d,'%s')", $uid, $uINFO['uid'],time());
# die(sprintf("INSERT INTO `follow` (`follower_id`,`following_id`, `follow_time`) VALUES ( %d, %d,'%s')", $uid, $uINFO['uid'],time()));
if ($DB->query($sql)) {
    die(echoApiData(0, "关注成功！"));
}
write_attack_logs();

die(echoApiData(1, "关注失败"));


deleteFollow:

$uid = post('uid', '');

if (!$isLogin) {
    die(echoApiData(3, "请先登陆账号后再进行取消点赞！"));
}

if (empty($uid)) {
    write_attack_logs();
    die(echoApiData(1, "uid 不能为空，请刷新页面！"));
}

$uid = intval($uid);


//更新操作
$sql = sprintf("DELETE FROM `follow` WHERE `follower_id`=%d AND `following_id`=%d", $uid, $uINFO['uid']);
# die(sprintf("INSERT INTO `follow` (`follower_id`,`following_id`) VALUES ( %d, %d,'%s')", $uid, $uINFO['uid'],time()));
if ($DB->query($sql)) {
    die(echoApiData(0, "取消关注成功！"));
}
write_attack_logs();

die(echoApiData(1, "取消关注失败"));

