<?php
/*
 * @Author: Martin
 * @Date: 2022-12-15 17:58:26
 * @Description: 公用函数的实现
 */




/**
 * 获取客户端IP
 * @return array|false|string
 */
function getIp()
{
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        $ip = getenv("HTTP_CLIENT_IP");
    else
        if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else
            if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
                $ip = getenv("REMOTE_ADDR");
            else
                if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
                    $ip = $_SERVER['REMOTE_ADDR'];
                else
                    $ip = "unknown";
    return ($ip);
}

/**
 * 检查 IP 的登录次数和封禁状态
 * @param string $ip
 * @return true|false
 */
// 
function checkLoginAttempts($ip) {
    // 获取 IP 的登录次数和封禁状态
    $loginAttempts = isset($_SESSION['login_attempts'][$ip]) ? $_SESSION['login_attempts'][$ip] : 0;
    $isBanned = isset($_SESSION['ip_ban'][$ip]) ? $_SESSION['ip_ban'][$ip] : false;
    $_SESSION['login_attempts'][$ip] = $loginAttempts + 1;
    // 设置限制条件
    $maxLoginAttempts = 3; // 最大登录次数
    $banDuration = 60; // 封禁时长（秒）
    // die(var_dump($loginAttempts));
    // 检查登录次数是否超过限制
    if ($loginAttempts >= $maxLoginAttempts) {
        // 检查 IP 是否已被封禁
        if (!$isBanned) {
            // 如果 IP 未被封禁，则封禁 IP 并设置封禁结束时间
            $_SESSION['ip_ban'][$ip] = true;
            $_SESSION['ban_end_time'][$ip] = time() + $banDuration;
        } else {
            // 如果 IP 已被封禁，则检查封禁结束时间是否已过
            $banEndTime = isset($_SESSION['ban_end_time'][$ip]) ? $_SESSION['ban_end_time'][$ip] : 0;
            if (time() > $banEndTime) {
                // 如果封禁结束时间已过，则解封 IP
                $_SESSION['ip_ban'][$ip] = false;
                $_SESSION['login_attempts'][$ip] = 0;
            } else {
                // 如果封禁结束时间未过，则阻止登录、
                write_attack_logs();
                return TRUE;
                die('登录次数过多，IP 已被封禁，请稍后再试！');
            }
        }
    }
    return FALSE;
}

/**
 * 避免重复造轮子，json 信息格式化输出
 * @param int $code
 * @param string $msg
 * @param array $data
 * @return false|string
 */
function echoApiData($code = 0, $msg = 'ok', $data = [])
{
    $data = [
        'code' => $code,
        'msg' => $msg,
        'data' => $data
    ];
    if (count($data) < 1) unset($data['data']);

    return json_encode($data, JSON_UNESCAPED_UNICODE);
}


/**
 * 格式化时间输出
 * @param string $time
 * @return string
 */
function formatTime($time)
{
    //现在的 年 月 日
    list($y, $m, $d, $h_i) = explode("-", date("Y-m-d-H:i"));

    //传入时间的 年 月 日
    list($_y, $_m, $_d, $_h_i) = explode("-", date("Y-m-d-H:i", $time));

    // 如果传入时间早，就用传入时间
    if ($y > $_y) {
        $prefix = "{$_y}-{$_m}-{$_d}";
    }

    if ($y === $_y) {
        $prefix = "{$_m}-{$_d}";
    }

    if ($d === $_d) {
        $prefix = '今天';
    }

    return "{$prefix} {$_h_i}";
}

/**
 * @param string $field GET 请求字段
 * @param string $default 如果不存在，默认输出的值
 * @return boolean|string   输出结果
 *
 * 获取 GET 参数，并给予默认值，封装成函数
 * 避免直接写，多出不必要的判断
 *
 */
function get($field = '', $default = false)
{
    if (!isset($_GET[$field])) return $default;
    return $_GET[$field];
}

/**
 * @param string $field POST 请求字段
 * @param string $default 如果不存在，默认输出的值
 * @return boolean|string   输出结果
 *
 * 获取 POST 参数，并给予默认值，封装成函数
 * 避免直接写，多出不必要的判断
 *
 */
function post($field = '', $default = false)
{
    if (!isset($_POST[$field])) return $default;
    return $_POST[$field];
}


/**
 * @param int $maxpage 总页数
 * @param int $page 当前页
 * @param string $para 翻页参数(不需要写$page),如http://www.example.com/article.php?page=3&id=1，$para参数就应该设为'&id=1'
 * @return  string          返回的输出分页html内容
 */

function multipage($maxpage = 5, $page = 1, $para = '')
{
    $multipage = '';  //输出的分页内容
    $listnum = 5;     //同时显示的最多可点击页面
    if (isset($_GET['topic'])) 
    {
        $topic = $_GET['topic'];
    } else {
        // 如果未提供topic参数的默认值
        $topic = 1;
    }
    if ($maxpage < 2) {
        return '<ul class="pagination"><li class="active"><a href="?page=1&topic='.$topic.'">1</a></li></ul>';
    } 
    else {
        $offset = 2;
        if ($maxpage <= $listnum) {
            $from = 1;
            $to = $maxpage;
        } else {
            $from = $page - $offset; //起始页
            $to = $from + $listnum - 1;  //终止页

            if ($from < 1) {
                $to = $page + 1 - $from;
                $from = 1;
                if ($to - $from < $listnum) {
                    $to = $listnum;
                }
            } elseif ($to > $maxpage) {
                $from = $maxpage - $listnum + 1;
                $to = $maxpage;
            }
        }

        $multipage .= ($page - $offset > 1 && $maxpage >= $page ? '<li><a href="?page=1&topic='.$topic.'" >1...</a></li>' : '') .
            ($page > 1 ? '<li><a href="?page=' . ($page - 1) . '&topic='.$topic.'&para='.$para.'" >&laquo;</a></li>' : '');

        for ($i = $from; $i <= $to; $i++) {
            $multipage .= $i == $page ? '<li class="active"><a href="?page=' . $i . '&topic='.$topic.'&para='.$para.'" >' . $i . '</a></li>' : '<li><a href="?page=' . $i . '&topic='.$topic.'&para='.$para.'" >' . $i . '</a></li>';
        }

        $multipage .= ($page < $maxpage ? '<li><a href="?page=' . ($page + 1) . '&topic='.$topic.'&para='.$para.'" >&raquo;</a></li>' : '') .
            ($to < $maxpage ? '<li><a href="?page=' . $maxpage . '&topic='.$topic.'&para='.$para.'" class="last" >...' . $maxpage . '</a></li>' : '');
        $multipage .= ' <li><a><input type="text" size="3"  οnkeydοwn="if(event.keyCode===13) {self.window.location=\'?page=\'+this.value+\'&topic='.$topic.'&para='.$para.'\'; return false;}" ></a></li>';

        $multipage = $multipage ? '<ul class="pagination">' . $multipage . '</ul>' : '';
    }

    return $multipage;
}
// function multipage($maxpage = 5, $page = 1, $para = '')
// {
//     $multipage = '';  //输出的分页内容
//     $listnum = 5;     //同时显示的最多可点击页面
//     $content = post('content', '');
//     if(isset($_SERVER['HTTP_REFERER'])){
//         $referer = $_SERVER['HTTP_REFERER'];
//         $query = parse_url($referer, PHP_URL_QUERY);
//         parse_str($query, $params);

//         if(isset($params['topic'])){
//             $topic = $params['topic'];
//         }
//     }
//     if ($maxpage < 2) {
//         return '<ul class="pagination"><li class="active"><a href="?page=1' . $para . '&topic='. $topic.'>1</a></li></ul>';
//     } else {
//         $offset = 2;
//         if ($maxpage <= $listnum) {
//             $from = 1;
//             $to = $maxpage;
//         } else {
//             $from = $page - $offset; //起始页
//             $to = $from + $listnum - 1;  //终止页
//             if ($from < 1) {
//                 $to = $page + 1 - $from;
//                 $from = 1;
//                 if ($to - $from < $listnum) {
//                     $to = $listnum;
//                 }
//             } elseif ($to > $maxpage) {
//                 $from = $maxpage - $listnum + 1;
//                 $to = $maxpage;
//             }
//         }

//         $multipage .= ($page - $offset > 1 && $maxpage >= $page ? '<li><a href="?page=1' . $para . '" >1...</a></li>' : '') .
//             ($page > 1 ? '<li><a href="?page=' . ($page - 1) . $para . '" >&laquo;</a></li>' : '');

//         for ($i = $from; $i <= $to; $i++) {
//             $multipage .= $i == $page ? '<li class="active"><a href="?page=' . $i . $para . '" >' . $i . '</a></li>' : '<li><a href="?page=' . $i . $para . '" >' . $i . '</a></li>';
//         }

//         $multipage .= ($page < $maxpage ? '<li><a href="?page=' . ($page + 1) . $para . '" >&raquo;</a></li>' : '') .
//             ($to < $maxpage ? '<li><a href="?page=' . $maxpage . $para . '" class="last" >...' . $maxpage . '</a></li>' : '');
//         $multipage .= ' <li><a><input type="text" size="3"  οnkeydοwn="if(event.keyCode===13) {self.window.location=\'?page=\'+this.value+\'' . $para . '\'; return false;}" ></a></li>';


//         $multipage = $multipage ? '<ul class="pagination">' . $multipage . '</ul>' : '';
//     }

//     return $multipage;
// }


/**
 * desc 加密和解密
 * @param string $string 需要加密或解密的字符串
 * @param string $operation DECODE:解密;ENCODE:加密;
 * @param string $key 秘钥
 * @param int $expiry 密文有效期
 * @return bool|string
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
    $ckey_length = 4;

    // 密匙
    $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);

    // 密匙a会参与加解密
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) :
        substr(md5(microtime()), -$ckey_length)) : '';
    // 参与运算的密匙
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
    // 解密时会通过这个密匙验证数据完整性
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
        sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    // 产生密匙簿
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 核心加解密部分
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        // 验证数据有效性，请看未加密明文的格式
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
            substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

/**
 * 检查请求头Referer
 * @return true|false
 */
function checkReferer() {
    // 检查 Referer 头部是否存在
    if (isset($_SERVER['HTTP_REFERER'])) {
        // 获取当前站点的域名
        $currentHost = parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST);
        // die(var_dump($currentHost));
        
        // 获取请求的来源域名
        $referer = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        // die(var_dump($referer));
        // 检查来源域名是否与当前域名匹配
        if ($referer === $currentHost) {
            // 验证通过，来源合法
            
            return true;
        }
    }
    
    // 验证失败，来源不合法
    return false;
}

/**
 * 检查Token
 * @param string $token
 * @return true|false
 */
function validateToken($token) {
    // 开启会话
    session_start();

    // 检查会话中是否存在 Token 值
    if (isset($_SESSION['token']) && $_SESSION['token'] === $token) {
        // Token 验证通过
        return true;
    }

    // Token 验证失败
    return false;
}

/**
 * 过滤不良词汇
 * @param string $message
 * @return true|false
 */
function filterMessage($message) {
    // 定义敏感词汇和不良内容列表
    $badWords = array("傻子", "小黑子", "侮辱性词汇", "不良内容");

    // 将敏感词汇替换为星号或其他自定义内容
    $filteredMessage = str_ireplace($badWords, "*", $message);

    // 返回过滤后的留言内容
    return $filteredMessage;
}




?>




