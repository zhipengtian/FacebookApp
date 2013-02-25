-- phpMyAdmin SQL Dump
-- version 3.5.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 25, 2013 at 04:33 PM
-- Server version: 5.1.67
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `facebook`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `facebook_id` bigint(19) unsigned NOT NULL,
  `username` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `app_installed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'tell whether the user installed our app or not',
  PRIMARY KEY (`id`),
  UNIQUE KEY `facebook_id` (`facebook_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=46 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `facebook_id`, `username`, `first_name`, `last_name`, `app_installed`) VALUES
(39, 100000317933362, 'zhipeng.tian', 'Zhipeng', 'Tian', 1),
(40, 100005276949652, 'gavin.field.524', 'Gavin', 'Field', 0),
(41, 100004103717803, 'mike.song.106', 'Mike', 'Song', 0),
(42, 100000159938386, 'zhangwan', 'Wanyu', 'Zhang', 0),
(43, 1401952249, 'yuzhouwu', 'Joey', 'Wu', 0),
(44, 100000150867721, 'di.zhang.12327', 'Di', 'Zhang', 0),
(45, 100002013547241, 'tian.wang.1420', 'Tian', 'Wang', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_bio`
--

CREATE TABLE IF NOT EXISTS `user_bio` (
  `user_id` int(9) unsigned NOT NULL,
  `gender` varchar(6) NOT NULL,
  `birthday` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `hometown` varchar(100) NOT NULL,
  `language` varchar(100) NOT NULL,
  `politics` varchar(100) NOT NULL,
  `religion` varchar(100) NOT NULL,
  `website` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_bio`
--

INSERT INTO `user_bio` (`user_id`, `gender`, `birthday`, `email`, `location`, `hometown`, `language`, `politics`, `religion`, `website`) VALUES
(39, 'male', '1990-09-05', 'zhiptian@gmail.com', 'Bloomington, Indiana', 'Tianjin, China', 'Mandarin Chinese, English', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `user_education_history`
--

CREATE TABLE IF NOT EXISTS `user_education_history` (
  `user_id` int(9) unsigned NOT NULL,
  `school` varchar(255) NOT NULL,
  `year` year(4) NOT NULL,
  `type` varchar(100) NOT NULL,
  KEY `use_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_education_history`
--

INSERT INTO `user_education_history` (`user_id`, `school`, `year`, `type`) VALUES
(39, '天津市耀华中学  （yaohua  high school  tianjin china）', 2006, 'High School'),
(39, 'Tianjin NO.43 high school', 2009, 'High School'),
(39, 'Indiana University', 2012, 'College');

-- --------------------------------------------------------

--
-- Table structure for table `user_family`
--

CREATE TABLE IF NOT EXISTS `user_family` (
  `user1_id` int(9) unsigned NOT NULL,
  `user2_id` int(9) unsigned NOT NULL,
  `relationship` varchar(100) NOT NULL,
  KEY `user1_id` (`user1_id`,`user2_id`),
  KEY `user2_id` (`user2_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_family`
--

INSERT INTO `user_family` (`user1_id`, `user2_id`, `relationship`) VALUES
(39, 40, 'brother');

-- --------------------------------------------------------

--
-- Table structure for table `user_likes`
--

CREATE TABLE IF NOT EXISTS `user_likes` (
  `like_id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `user_id` int(9) unsigned NOT NULL,
  `created_time` datetime NOT NULL,
  PRIMARY KEY (`like_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=54 ;

--
-- Dumping data for table `user_likes`
--

INSERT INTO `user_likes` (`like_id`, `category`, `name`, `user_id`, `created_time`) VALUES
(50, 'Games/toys', 'Diablo', 39, '2011-11-17 13:18:47'),
(51, 'Games/toys', 'Cut the Rope', 39, '2011-07-25 12:28:01'),
(52, 'Community', 'WeiPhoneBBS', 39, '2011-05-02 01:16:37'),
(53, 'Entertainer', '陳建州﹏黑人老大', 39, '2010-02-19 01:45:08');

-- --------------------------------------------------------

--
-- Table structure for table `user_photos`
--

CREATE TABLE IF NOT EXISTS `user_photos` (
  `photo_id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `photo_fb_id` bigint(19) unsigned NOT NULL,
  `user_id` int(9) unsigned NOT NULL,
  `created_time` datetime NOT NULL,
  `place` varchar(100) NOT NULL,
  PRIMARY KEY (`photo_id`),
  UNIQUE KEY `photo_fb_id` (`photo_fb_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=191 ;

--
-- Dumping data for table `user_photos`
--

INSERT INTO `user_photos` (`photo_id`, `photo_fb_id`, `user_id`, `created_time`, `place`) VALUES
(13, 532583793428880, 39, '2013-02-19 14:28:31', ''),
(14, 532582820095644, 39, '2013-02-19 14:25:43', ''),
(15, 532582526762340, 39, '2013-02-19 14:24:40', ''),
(16, 532560673431192, 39, '2013-02-19 13:10:54', 'Visit Gatlinburg'),
(17, 532560650097861, 39, '2013-02-19 13:10:52', ''),
(18, 532560663431193, 39, '2013-02-19 13:10:52', 'Visit Gatlinburg'),
(19, 532575026763090, 39, '2013-02-19 14:02:19', 'Daytona Beach, Florida'),
(20, 532575020096424, 39, '2013-02-19 14:02:18', 'Daytona Beach, Florida'),
(21, 532575033429756, 39, '2013-02-19 14:02:19', 'Daytona Beach, Florida'),
(22, 532571433430116, 39, '2013-02-19 13:52:19', 'Atlanta, Georgia'),
(23, 532571446763448, 39, '2013-02-19 13:52:20', 'Atlanta, Georgia'),
(24, 532571436763449, 39, '2013-02-19 13:52:19', 'Atlanta, Georgia'),
(25, 532567143430545, 39, '2013-02-19 13:36:12', 'Indiana University'),
(26, 532567130097213, 39, '2013-02-19 13:36:11', 'Indiana University'),
(27, 532567133430546, 39, '2013-02-19 13:36:12', 'Indiana University'),
(28, 532072366813356, 39, '2013-02-18 16:27:19', ''),
(29, 377877052232889, 39, '2012-04-02 17:10:17', ''),
(30, 377878302232764, 39, '2012-04-02 17:12:25', ''),
(31, 103050043048926, 39, '2009-12-18 17:19:47', ''),
(32, 377873565566571, 39, '2012-04-02 17:04:01', ''),
(33, 377872828899978, 39, '2012-04-02 17:02:24', ''),
(34, 532560693431190, 39, '2013-02-19 13:10:55', 'Visit Gatlinburg'),
(35, 532560703431189, 39, '2013-02-19 13:10:56', 'Visit Gatlinburg'),
(36, 532560720097854, 39, '2013-02-19 13:10:57', 'Visit Gatlinburg'),
(37, 532560733431186, 39, '2013-02-19 13:10:58', 'Visit Gatlinburg'),
(38, 532560750097851, 39, '2013-02-19 13:11:00', 'Visit Gatlinburg'),
(39, 532560760097850, 39, '2013-02-19 13:11:00', 'Visit Gatlinburg'),
(40, 532560783431181, 39, '2013-02-19 13:11:02', 'Visit Gatlinburg'),
(41, 532560800097846, 39, '2013-02-19 13:11:03', 'Visit Gatlinburg'),
(42, 532560820097844, 39, '2013-02-19 13:11:04', 'Visit Gatlinburg'),
(43, 532560833431176, 39, '2013-02-19 13:11:05', 'Visit Gatlinburg'),
(44, 532560860097840, 39, '2013-02-19 13:11:07', 'Visit Gatlinburg'),
(45, 532560873431172, 39, '2013-02-19 13:11:07', 'Visit Gatlinburg'),
(46, 532560890097837, 39, '2013-02-19 13:11:10', 'Visit Gatlinburg'),
(47, 532560893431170, 39, '2013-02-19 13:11:11', 'Visit Gatlinburg'),
(48, 532560906764502, 39, '2013-02-19 13:11:11', 'Visit Gatlinburg'),
(49, 532577036762889, 39, '2013-02-19 14:09:09', 'Visit Gatlinburg'),
(50, 532577083429551, 39, '2013-02-19 14:09:15', 'Visit Gatlinburg'),
(51, 532577056762887, 39, '2013-02-19 14:09:10', 'Visit Gatlinburg'),
(52, 532577096762883, 39, '2013-02-19 14:09:17', 'Visit Gatlinburg'),
(53, 532577110096215, 39, '2013-02-19 14:09:19', 'Visit Gatlinburg'),
(54, 532577143429545, 39, '2013-02-19 14:09:23', 'Visit Gatlinburg'),
(55, 532577153429544, 39, '2013-02-19 14:09:25', 'Visit Gatlinburg'),
(56, 532577173429542, 39, '2013-02-19 14:09:26', 'Visit Gatlinburg'),
(57, 532577213429538, 39, '2013-02-19 14:09:29', 'Visit Gatlinburg'),
(58, 532577226762870, 39, '2013-02-19 14:09:31', 'Visit Gatlinburg'),
(59, 532577250096201, 39, '2013-02-19 14:09:33', 'Visit Gatlinburg'),
(60, 532577276762865, 39, '2013-02-19 14:09:36', 'Visit Gatlinburg'),
(61, 532577290096197, 39, '2013-02-19 14:09:37', 'Visit Gatlinburg'),
(62, 532577303429529, 39, '2013-02-19 14:09:40', 'Visit Gatlinburg'),
(63, 532577336762859, 39, '2013-02-19 14:09:41', 'Visit Gatlinburg'),
(64, 532575090096417, 39, '2013-02-19 14:02:24', 'Daytona Beach, Florida'),
(65, 532575100096416, 39, '2013-02-19 14:02:26', 'Daytona Beach, Florida'),
(66, 532575106763082, 39, '2013-02-19 14:02:26', 'Daytona Beach, Florida'),
(67, 532575156763077, 39, '2013-02-19 14:02:31', 'Daytona Beach, Florida'),
(68, 532575173429742, 39, '2013-02-19 14:02:31', 'Daytona Beach, Florida'),
(69, 532575186763074, 39, '2013-02-19 14:02:33', 'Daytona Beach, Florida'),
(70, 532575220096404, 39, '2013-02-19 14:02:37', 'Daytona Beach, Florida'),
(71, 532575236763069, 39, '2013-02-19 14:02:41', 'Daytona Beach, Florida'),
(72, 532575250096401, 39, '2013-02-19 14:02:42', 'Daytona Beach, Florida'),
(73, 532575273429732, 39, '2013-02-19 14:02:44', 'Daytona Beach, Florida'),
(74, 532575306763062, 39, '2013-02-19 14:02:47', 'Daytona Beach, Florida'),
(75, 532575320096394, 39, '2013-02-19 14:02:47', 'Daytona Beach, Florida'),
(76, 532575333429726, 39, '2013-02-19 14:02:49', 'Daytona Beach, Florida'),
(77, 532575373429722, 39, '2013-02-19 14:02:53', 'Daytona Beach, Florida'),
(78, 532575380096388, 39, '2013-02-19 14:02:53', 'Daytona Beach, Florida'),
(79, 532575403429719, 39, '2013-02-19 14:02:55', 'Daytona Beach, Florida'),
(80, 532575426763050, 39, '2013-02-19 14:02:58', 'Daytona Beach, Florida'),
(81, 532575466763046, 39, '2013-02-19 14:03:03', 'Daytona Beach, Florida'),
(82, 532575456763047, 39, '2013-02-19 14:03:03', 'Daytona Beach, Florida'),
(83, 532575476763045, 39, '2013-02-19 14:03:04', 'Daytona Beach, Florida'),
(84, 532575500096376, 39, '2013-02-19 14:03:09', 'Daytona Beach, Florida'),
(85, 532575510096375, 39, '2013-02-19 14:03:12', 'Daytona Beach, Florida'),
(86, 532575530096373, 39, '2013-02-19 14:03:18', 'Daytona Beach, Florida'),
(87, 532575536763039, 39, '2013-02-19 14:03:18', 'Daytona Beach, Florida'),
(88, 532575550096371, 39, '2013-02-19 14:03:19', 'Daytona Beach, Florida'),
(89, 532575600096366, 39, '2013-02-19 14:03:27', 'Daytona Beach, Florida'),
(90, 532575616763031, 39, '2013-02-19 14:03:29', 'Daytona Beach, Florida'),
(91, 532575620096364, 39, '2013-02-19 14:03:29', 'Daytona Beach, Florida'),
(92, 532575643429695, 39, '2013-02-19 14:03:32', 'Daytona Beach, Florida'),
(93, 532575656763027, 39, '2013-02-19 14:03:35', 'Daytona Beach, Florida'),
(94, 532575666763026, 39, '2013-02-19 14:03:37', 'Daytona Beach, Florida'),
(95, 532575676763025, 39, '2013-02-19 14:03:38', 'Daytona Beach, Florida'),
(96, 532575703429689, 39, '2013-02-19 14:03:43', 'Daytona Beach, Florida'),
(97, 532575720096354, 39, '2013-02-19 14:03:45', 'Daytona Beach, Florida'),
(98, 532575740096352, 39, '2013-02-19 14:03:47', 'Daytona Beach, Florida'),
(99, 532571493430110, 39, '2013-02-19 13:52:24', 'Atlanta, Georgia'),
(100, 532571500096776, 39, '2013-02-19 13:52:26', 'Atlanta, Georgia'),
(101, 532571523430107, 39, '2013-02-19 13:52:26', 'Atlanta, Georgia'),
(102, 532571546763438, 39, '2013-02-19 13:52:31', 'Atlanta, Georgia'),
(103, 532571560096770, 39, '2013-02-19 13:52:32', 'Atlanta, Georgia'),
(104, 532571573430102, 39, '2013-02-19 13:52:33', 'Atlanta, Georgia'),
(105, 532571616763431, 39, '2013-02-19 13:52:37', 'Atlanta, Georgia'),
(106, 532571630096763, 39, '2013-02-19 13:52:38', 'Atlanta, Georgia'),
(107, 532571640096762, 39, '2013-02-19 13:52:39', 'Atlanta, Georgia'),
(108, 532571670096759, 39, '2013-02-19 13:52:44', 'Atlanta, Georgia'),
(109, 532571666763426, 39, '2013-02-19 13:52:43', 'Atlanta, Georgia'),
(110, 532571693430090, 39, '2013-02-19 13:52:45', 'Atlanta, Georgia'),
(111, 532571716763421, 39, '2013-02-19 13:52:48', 'Atlanta, Georgia'),
(112, 532571726763420, 39, '2013-02-19 13:52:49', 'Atlanta, Georgia'),
(113, 532571736763419, 39, '2013-02-19 13:52:51', 'Atlanta, Georgia'),
(114, 532571753430084, 39, '2013-02-19 13:52:54', 'Atlanta, Georgia'),
(115, 532571763430083, 39, '2013-02-19 13:52:55', 'Atlanta, Georgia'),
(116, 532571780096748, 39, '2013-02-19 13:52:57', 'Atlanta, Georgia'),
(117, 532571800096746, 39, '2013-02-19 13:52:59', 'Atlanta, Georgia'),
(118, 532571813430078, 39, '2013-02-19 13:53:00', 'Atlanta, Georgia'),
(119, 532571840096742, 39, '2013-02-19 13:53:03', 'Atlanta, Georgia'),
(120, 532571853430074, 39, '2013-02-19 13:53:05', 'Atlanta, Georgia'),
(121, 532571883430071, 39, '2013-02-19 13:53:08', 'Atlanta, Georgia'),
(122, 532571896763403, 39, '2013-02-19 13:53:08', 'Atlanta, Georgia'),
(123, 532571923430067, 39, '2013-02-19 13:53:11', 'Atlanta, Georgia'),
(124, 532571950096731, 39, '2013-02-19 13:53:13', 'Atlanta, Georgia'),
(125, 532571963430063, 39, '2013-02-19 13:53:15', 'Atlanta, Georgia'),
(126, 532571980096728, 39, '2013-02-19 13:53:17', 'Atlanta, Georgia'),
(127, 532572003430059, 39, '2013-02-19 13:53:19', 'Atlanta, Georgia'),
(128, 532572016763391, 39, '2013-02-19 13:53:20', 'Atlanta, Georgia'),
(129, 532572030096723, 39, '2013-02-19 13:53:22', 'Atlanta, Georgia'),
(130, 532572083430051, 39, '2013-02-19 13:53:25', 'Atlanta, Georgia'),
(131, 532572096763383, 39, '2013-02-19 13:53:27', 'Atlanta, Georgia'),
(132, 532572106763382, 39, '2013-02-19 13:53:28', 'Atlanta, Georgia'),
(133, 532572130096713, 39, '2013-02-19 13:53:30', 'Atlanta, Georgia'),
(134, 532572166763376, 39, '2013-02-19 13:53:34', 'Atlanta, Georgia'),
(135, 532572176763375, 39, '2013-02-19 13:53:37', 'Atlanta, Georgia'),
(136, 532572230096703, 39, '2013-02-19 13:53:42', 'Atlanta, Georgia'),
(137, 532572243430035, 39, '2013-02-19 13:53:42', 'Atlanta, Georgia'),
(138, 532572300096696, 39, '2013-02-19 13:53:51', 'Atlanta, Georgia'),
(139, 532572286763364, 39, '2013-02-19 13:53:51', 'Atlanta, Georgia'),
(140, 532572290096697, 39, '2013-02-19 13:53:51', 'Atlanta, Georgia'),
(141, 532572330096693, 39, '2013-02-19 13:53:56', 'Atlanta, Georgia'),
(142, 532572360096690, 39, '2013-02-19 13:54:03', 'Atlanta, Georgia'),
(143, 532572353430024, 39, '2013-02-19 13:54:00', 'Atlanta, Georgia'),
(144, 532572370096689, 39, '2013-02-19 13:54:03', 'Atlanta, Georgia'),
(145, 532572423430017, 39, '2013-02-19 13:54:07', 'Atlanta, Georgia'),
(146, 532572446763348, 39, '2013-02-19 13:54:13', 'Atlanta, Georgia'),
(147, 532572453430014, 39, '2013-02-19 13:54:11', 'Atlanta, Georgia'),
(148, 532572506763342, 39, '2013-02-19 13:54:16', 'Atlanta, Georgia'),
(149, 532572603429999, 39, '2013-02-19 13:54:28', 'Atlanta, Georgia'),
(150, 532572560096670, 39, '2013-02-19 13:54:23', 'Atlanta, Georgia'),
(151, 532572576763335, 39, '2013-02-19 13:54:25', 'Atlanta, Georgia'),
(152, 532572620096664, 39, '2013-02-19 13:54:30', 'Atlanta, Georgia'),
(153, 532572646763328, 39, '2013-02-19 13:54:32', 'Atlanta, Georgia'),
(154, 532572670096659, 39, '2013-02-19 13:54:37', 'Atlanta, Georgia'),
(155, 532572683429991, 39, '2013-02-19 13:54:38', 'Atlanta, Georgia'),
(156, 532572703429989, 39, '2013-02-19 13:54:43', 'Atlanta, Georgia'),
(157, 532572723429987, 39, '2013-02-19 13:54:42', 'Atlanta, Georgia'),
(158, 532572746763318, 39, '2013-02-19 13:54:47', 'Atlanta, Georgia'),
(159, 532572773429982, 39, '2013-02-19 13:54:49', 'Atlanta, Georgia'),
(160, 532567160097210, 39, '2013-02-19 13:36:14', 'Indiana University'),
(161, 532567170097209, 39, '2013-02-19 13:36:15', 'Indiana University'),
(162, 532567180097208, 39, '2013-02-19 13:36:17', 'Indiana University'),
(163, 532567200097206, 39, '2013-02-19 13:36:19', 'Indiana University'),
(164, 532567223430537, 39, '2013-02-19 13:36:21', 'Indiana University'),
(165, 532567240097202, 39, '2013-02-19 13:36:20', 'Indiana University'),
(166, 532567266763866, 39, '2013-02-19 13:36:24', 'Indiana University'),
(167, 532567270097199, 39, '2013-02-19 13:36:24', 'Indiana University'),
(168, 532567283430531, 39, '2013-02-19 13:36:25', 'Indiana University'),
(169, 532567316763861, 39, '2013-02-19 13:36:29', 'Indiana University'),
(170, 532567326763860, 39, '2013-02-19 13:36:29', 'Indiana University'),
(171, 532567350097191, 39, '2013-02-19 13:36:30', 'Indiana University'),
(172, 532567396763853, 39, '2013-02-19 13:36:35', 'Indiana University'),
(173, 532567383430521, 39, '2013-02-19 13:36:33', 'Indiana University'),
(174, 532567426763850, 39, '2013-02-19 13:36:36', 'Indiana University'),
(175, 532567440097182, 39, '2013-02-19 13:36:37', 'Indiana University'),
(176, 532567453430514, 39, '2013-02-19 13:36:39', 'Indiana University'),
(177, 532567470097179, 39, '2013-02-19 13:36:40', 'Indiana University'),
(178, 532567500097176, 39, '2013-02-19 13:36:41', 'Indiana University'),
(179, 532567516763841, 39, '2013-02-19 13:36:42', 'Indiana University'),
(180, 532567530097173, 39, '2013-02-19 13:36:44', 'Indiana University'),
(181, 532567556763837, 39, '2013-02-19 13:36:45', 'Indiana University'),
(182, 532567583430501, 39, '2013-02-19 13:36:47', 'Indiana University'),
(183, 532567593430500, 39, '2013-02-19 13:36:48', 'Indiana University'),
(184, 532567626763830, 39, '2013-02-19 13:36:50', 'Indiana University'),
(185, 532567633430496, 39, '2013-02-19 13:36:51', 'Indiana University'),
(186, 532567690097157, 39, '2013-02-19 13:36:56', 'Indiana University'),
(187, 532567676763825, 39, '2013-02-19 13:36:55', 'Indiana University'),
(188, 532567680097158, 39, '2013-02-19 13:36:55', 'Indiana University'),
(189, 532567723430487, 39, '2013-02-19 13:36:59', 'Indiana University'),
(190, 532567733430486, 39, '2013-02-19 13:37:00', 'Indiana University');

-- --------------------------------------------------------

--
-- Table structure for table `user_photo_comments`
--

CREATE TABLE IF NOT EXISTS `user_photo_comments` (
  `comment_id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `comment_fb_id` int(9) unsigned NOT NULL,
  `user_id` int(9) unsigned NOT NULL,
  `photo_id` int(9) unsigned NOT NULL,
  `created_time` datetime NOT NULL,
  `content` text NOT NULL,
  `like_count` int(9) unsigned NOT NULL,
  `user_likes` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_id`),
  KEY `user_id` (`user_id`),
  KEY `original_user_id` (`photo_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `user_photo_comments`
--

INSERT INTO `user_photo_comments` (`comment_id`, `comment_fb_id`, `user_id`, `photo_id`, `created_time`, `content`, `like_count`, `user_likes`) VALUES
(4, 1550824, 40, 28, '2013-02-19 11:46:50', '屌', 0, 0),
(5, 948329, 43, 30, '2012-04-03 00:44:22', '骚货', 0, 0),
(6, 949237, 39, 30, '2012-04-03 12:57:06', '干你！', 0, 0),
(7, 1563362, 40, 40, '2013-02-25 15:11:25', 'nice car', 0, 0),
(8, 1552771, 45, 183, '2013-02-20 11:40:45', '鹏叔，您这是毕业了？？考研了不？', 0, 0),
(9, 1552803, 39, 183, '2013-02-20 11:57:13', '是啊。上学期就毕业了。申请了，还在等信。。', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_photo_likes`
--

CREATE TABLE IF NOT EXISTS `user_photo_likes` (
  `like_id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(9) unsigned NOT NULL,
  `photo_id` int(9) unsigned NOT NULL,
  PRIMARY KEY (`like_id`),
  KEY `user_id` (`user_id`,`photo_id`),
  KEY `photo_id` (`photo_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `user_photo_likes`
--

INSERT INTO `user_photo_likes` (`like_id`, `user_id`, `photo_id`) VALUES
(2, 40, 28),
(3, 40, 40),
(4, 45, 183);

-- --------------------------------------------------------

--
-- Table structure for table `user_photo_tags`
--

CREATE TABLE IF NOT EXISTS `user_photo_tags` (
  `tag_id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(9) unsigned NOT NULL,
  `photo_id` int(9) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`),
  KEY `user_id` (`user_id`,`photo_id`),
  KEY `photo_id` (`photo_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

--
-- Dumping data for table `user_photo_tags`
--

INSERT INTO `user_photo_tags` (`tag_id`, `user_id`, `photo_id`) VALUES
(13, 39, 49),
(14, 39, 93),
(15, 39, 96),
(16, 39, 101),
(17, 39, 119),
(18, 39, 135),
(19, 39, 164),
(20, 39, 166),
(21, 39, 168),
(22, 44, 172);

-- --------------------------------------------------------

--
-- Table structure for table `user_statuses`
--

CREATE TABLE IF NOT EXISTS `user_statuses` (
  `status_id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `status_fb_id` bigint(19) unsigned NOT NULL,
  `user_id` int(9) unsigned NOT NULL,
  `content` text NOT NULL,
  `created_time` datetime NOT NULL,
  `place` varchar(100) NOT NULL,
  PRIMARY KEY (`status_id`),
  UNIQUE KEY `status_fb_id` (`status_fb_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

--
-- Dumping data for table `user_statuses`
--

INSERT INTO `user_statuses` (`status_id`, `status_fb_id`, `user_id`, `content`, `created_time`, `place`) VALUES
(15, 532511593436100, 39, 'Why is snow always right after rain here?', '2013-02-19 11:49:04', 'Bloomington, Indiana'),
(16, 520410084646251, 39, 'Get back to Facebook for academic purpose. lol', '2013-01-31 10:22:47', ''),
(17, 309073734353, 39, '自作孽不可活..', '2010-02-16 04:54:16', ''),
(18, 273941244269, 39, '很纠结～～～', '2010-01-28 20:04:24', '');

-- --------------------------------------------------------

--
-- Table structure for table `user_status_comments`
--

CREATE TABLE IF NOT EXISTS `user_status_comments` (
  `comment_id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `comment_fb_id` int(9) unsigned NOT NULL,
  `user_id` int(9) unsigned NOT NULL,
  `status_id` int(9) unsigned NOT NULL,
  `created_time` datetime NOT NULL,
  `content` text NOT NULL,
  `like_count` int(9) unsigned NOT NULL,
  `user_likes` tinyint(1) NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `user_id` (`user_id`,`status_id`),
  KEY `status_id` (`status_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=32 ;

--
-- Dumping data for table `user_status_comments`
--

INSERT INTO `user_status_comments` (`comment_id`, `comment_fb_id`, `user_id`, `status_id`, `created_time`, `content`, `like_count`, `user_likes`) VALUES
(28, 5846730, 40, 15, '2013-02-20 12:41:40', 'thats true', 1, 1),
(29, 10466956, 42, 18, '2010-01-28 20:26:18', '哈 怎么了你\n', 0, 0),
(30, 10467175, 39, 18, '2010-01-28 20:32:12', '没事啊，就是烦呀', 0, 0),
(31, 10469303, 42, 18, '2010-01-28 21:23:23', '恩恩 一样 理解啊', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_status_likes`
--

CREATE TABLE IF NOT EXISTS `user_status_likes` (
  `like_id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(9) unsigned NOT NULL,
  `status_id` int(9) unsigned NOT NULL,
  PRIMARY KEY (`like_id`),
  KEY `user_id` (`user_id`,`status_id`),
  KEY `status_id` (`status_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `user_status_likes`
--

INSERT INTO `user_status_likes` (`like_id`, `user_id`, `status_id`) VALUES
(6, 40, 15);

-- --------------------------------------------------------

--
-- Table structure for table `user_status_tags`
--

CREATE TABLE IF NOT EXISTS `user_status_tags` (
  `tag_id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(9) unsigned NOT NULL,
  `status_id` int(9) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`),
  KEY `user_id` (`user_id`,`status_id`),
  KEY `user_id_2` (`user_id`),
  KEY `status_id` (`status_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `user_status_tags`
--

INSERT INTO `user_status_tags` (`tag_id`, `user_id`, `status_id`) VALUES
(8, 40, 15),
(7, 41, 15);

-- --------------------------------------------------------

--
-- Table structure for table `user_work_history`
--

CREATE TABLE IF NOT EXISTS `user_work_history` (
  `user_id` int(9) unsigned NOT NULL,
  `employer` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `start_date` varchar(10) NOT NULL,
  `end_date` varchar(10) NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_work_history`
--

INSERT INTO `user_work_history` (`user_id`, `employer`, `location`, `position`, `start_date`, `end_date`) VALUES
(39, 'Indiana University', 'Bloomington, Indiana', 'Research Assistant', '2013-01', '');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_bio`
--
ALTER TABLE `user_bio`
  ADD CONSTRAINT `user_bio_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_education_history`
--
ALTER TABLE `user_education_history`
  ADD CONSTRAINT `user_education_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_family`
--
ALTER TABLE `user_family`
  ADD CONSTRAINT `user_family_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_family_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_photos`
--
ALTER TABLE `user_photos`
  ADD CONSTRAINT `user_photos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_photo_comments`
--
ALTER TABLE `user_photo_comments`
  ADD CONSTRAINT `user_photo_comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_photo_comments_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `user_photos` (`photo_id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_photo_likes`
--
ALTER TABLE `user_photo_likes`
  ADD CONSTRAINT `user_photo_likes_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `user_photos` (`photo_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_photo_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_photo_tags`
--
ALTER TABLE `user_photo_tags`
  ADD CONSTRAINT `user_photo_tags_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_photo_tags_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `user_photos` (`photo_id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_statuses`
--
ALTER TABLE `user_statuses`
  ADD CONSTRAINT `user_statuses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_status_comments`
--
ALTER TABLE `user_status_comments`
  ADD CONSTRAINT `user_status_comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_status_comments_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `user_statuses` (`status_id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_status_likes`
--
ALTER TABLE `user_status_likes`
  ADD CONSTRAINT `user_status_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_status_likes_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `user_statuses` (`status_id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_status_tags`
--
ALTER TABLE `user_status_tags`
  ADD CONSTRAINT `user_status_tags_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_status_tags_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `user_statuses` (`status_id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_work_history`
--
ALTER TABLE `user_work_history`
  ADD CONSTRAINT `user_work_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
