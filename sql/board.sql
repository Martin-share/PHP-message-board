-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2022-6-8 18:56:00
-- 服务器版本： 8.0.28
-- PHP 版本： 7.3.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `board`
--

-- --------------------------------------------------------

--
-- 表的结构 `comments`
--

CREATE TABLE `comments` (
  `cid` int UNSIGNED NOT NULL COMMENT '留言内容ID',
  `reply` text CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '管理员回复',
  `reply_time` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '回复时间',
  `uid` int NOT NULL COMMENT '关联 users 表的 uid',
  `contents` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '留言内容',
  `send_time` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '发表留言时间',
  `post_ip` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '发表留言时的IP地址'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;


--
-- 转存表中的数据 `comments`
--

INSERT INTO `comments` (`cid`, `reply`, `reply_time`, `uid`, `contents`, `send_time`, `post_ip`) VALUES
(43, NULL, NULL, 1, '123', '1671370085', '127.0.0.1'),
(44, NULL, NULL, 1, '123123', '1671370265', '127.0.0.1'),
(45, NULL, NULL, 1, '我', '1671411647', '127.0.0.1'),
(47, NULL, NULL, 1, '111111111111111111111111111111', '1671416294', '127.0.0.1'),
(49, NULL, NULL, 1, '123123', '1671416826', '127.0.0.1'),
(55, '123', '1671436529', 1, 'aaaaaaaaaaaaaa', '1671418719', '127.0.0.1'),
(56, NULL, NULL, 6, '人生若只如初见，何事秋风悲画扇。等闲变却故人心，却道故人心易变。', '1671497858', '127.0.0.1'),
(57, NULL, NULL, 7, '山一程，水一程，身向榆关那畔行，夜深千帐灯。风一更，雪一更，聒碎乡心梦不成，故园无此声。', '1671497895', '127.0.0.1'),
(58, '111', '1671698721', 8, '<script>alert(/xss/)</script>', '1671497951', '127.0.0.1'),
(59, '123123123', '1671698755', 10, '<img src=\"javascript:alert(/xss/)\">', '1671499648', '127.0.0.1'),
(60, '123123123', '1671698755', 1, '11111111111111', '1671506843', '127.0.0.1'),
(61, NULL, NULL, 12, '123', '1671535936', '192.168.43.248'),
(62, '11111111111111', '1671699300', 6, '123', '1671622795', '127.0.0.1'),
(63, NULL, NULL, 1, '举头望明月，低头思故乡', '1671767861', '127.0.0.1'),
(64, NULL, NULL, 6, '未能抛得杭州去，一半勾留是此湖', '1671767881', '127.0.0.1'),
(65, NULL, NULL, 6, '接天莲叶无穷碧，映日荷花别样红', '1671767885', '127.0.0.1'),
(66, NULL, NULL, 6, '大江东去，浪淘尽，千古风流人物', '1671767892', '127.0.0.1'),
(67, NULL, NULL, 6, '1', '1671767899', '127.0.0.1'),
(68, NULL, NULL, 7, '大漠孤烟直，长河落日圆。', '1671767924', '127.0.0.1'),
(69, NULL, NULL, 7, '二十四桥明月夜，玉人何处教吹箫', '1671767928', '127.0.0.1'),
(70, '测试管理员回复111111111', '1671792232', 8, '瓦罐不离井上破，将军难免阵中亡。', '1671767950', '127.0.0.1'),
(71, NULL, NULL, 1, '1111111111123', '1671777079', '127.0.0.1'),
(72, NULL, NULL, 1, '1111111111111111123', '1671777092', '127.0.0.1'),
(73, NULL, NULL, 1, '111111111111', '1671792152', '127.0.0.1');

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `uid` INT NOT NULL,
  `nickname` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户昵称',
  `summary` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '个性签名',
  `password` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户密码，md5(password+reg_ip)',
  `sex` INT NOT NULL DEFAULT '0' COMMENT '0:未知性别 1:男 2:女',
  `qq` VARCHAR(28) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'QQ账号',
  `email` VARCHAR(54) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '邮箱',
  `reg_time` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '注册时间',
  `reg_ip` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '注册时的IP地址',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `uniq_qq` (`qq`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`uid`, `nickname`, `summary`, `password`, `sex`, `qq`, `email`, `reg_time`, `reg_ip`) VALUES
(1, 'Martin', '我是Martin!', '2a7d21b31a661657ff6724a7b8304e56', 1, '2311415873', '2311415873@qq.com', '1671241400', '192.168.3.5'),
(6, 'rootroot1', 'rootroot1我是个性签名', '0e0ca72a4628c7b05127710d7e2d2029', 1, '1412957549', 'rootroot1@qq.com', '1671438390', '127.0.0.1'),
(7, 'rootroot2', '', '48917e9b64b9c8014f487fc5abcfcb0f', 2, '3045120064', 'rootroot2@qq.com', '1671438435', '127.0.0.1'),
(8, 'rootroot3', '', 'f36a33056324cb345faa4c44f65c3328', 1, '3153178401', 'rootroot3@qq.com', '1671438510', '127.0.0.1'),
(10, 'rootroot4', '', '17a2883d3000fd73e51a30bbd43f8f85', 1, '1131909980', 'rootroot4@qq.com', '1671438580', '127.0.0.1'),
(11, 'rootroot6', '', '6877f08a60f951f0a6cdb9d44658e32b', 1, '1234567', 'rootroot6@qq.com', '1671533508', '127.0.0.1'),
(12, 'rootroot8', '', '5687ec3b1a4114c6d0bc464c9cad422d', 2, '12312313131', 'rootroot8@qq.com', '1671535907', '192.168.43.248');

--
-- 转储表的索引
--

--
-- 表的索引 `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`cid`) USING BTREE;

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uid`) USING BTREE;

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `comments`
--
ALTER TABLE `comments`
  MODIFY `cid` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '留言内容ID', AUTO_INCREMENT=74;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `uid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
