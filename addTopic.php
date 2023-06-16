/*
 * @Author: Martin
 * @Date: 2023-06-10 17:55:43
 * @Description: 
 */
<?php
include_once __DIR__ . "/lib/common.php";
include __DIR__."/logs/functions.php";

$topic = post('topic','');
// var_dump($topic);
if (empty($topic)) {
    // header("Location: index.php"); // 重定向到 index.php
    exit; // 终止脚本执行
} else {
    // 执行添加到数据库的操作
    $sql = sprintf("SELECT * FROM `topics` WHERE `name`='%s' ", $topic);
    $query = $DB->query($sql);
    # die(sprintf("SELECT * FROM `topics` WHERE `name`='%s' ", $topic));
    if ($query && $query->fetch_assoc() > 0)
    {
        echo "<script>
            alert('已有该话题，添加失败');
            setTimeout(function() {
                window.location.href = './index.php';
            }, 10);
        </script>";
        die();
        // die(echoApiData(10,'未登录'));
    } else{
        // 之前数据库没有，就插入
        $sql = sprintf("INSERT INTO  `topics` (name) VALUES ('%s')", $topic);
        $query = $DB->query($sql);
        # die(sprintf("INSERT INTO  `topics` (name) VALUES ('%s')", $topic));
        if ($query){
            echo "<script>
                alert('添加成功');
                setTimeout(function() {
                    window.location.href = './index.php';
                }, 10);
            </script>";
            die();
            // die(echoApiData(10,'未登录'));
        }
    }
}
?>
