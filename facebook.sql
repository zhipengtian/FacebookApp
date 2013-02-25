-- phpMyAdmin SQL Dump
-- version 3.5.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 25, 2013 at 12:54 PM
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=35 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_bio`
--

CREATE TABLE IF NOT EXISTS `user_bio` (
  `user_id` int(9) unsigned NOT NULL,
  `gender` varchar(6) NOT NULL,
  `birthday` date NOT NULL,
  `email` varchar(255) NOT NULL,
  `hometown` varchar(255) NOT NULL,
  `language` varchar(255) NOT NULL,
  `politics` varchar(255) NOT NULL,
  `religion` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_education_history`
--

CREATE TABLE IF NOT EXISTS `user_education_history` (
  `user_id` int(9) unsigned NOT NULL,
  `school` varchar(255) NOT NULL,
  `year` year(4) NOT NULL,
  `type` varchar(255) NOT NULL,
  KEY `use_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_family`
--

CREATE TABLE IF NOT EXISTS `user_family` (
  `user1_id` int(9) unsigned NOT NULL,
  `user2_id` int(9) unsigned NOT NULL,
  `relationship` varchar(255) NOT NULL,
  KEY `user1_id` (`user1_id`,`user2_id`),
  KEY `user2_id` (`user2_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_likes`
--

CREATE TABLE IF NOT EXISTS `user_likes` (
  `like_id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `user_id` int(9) unsigned NOT NULL,
  `created_time` datetime NOT NULL,
  PRIMARY KEY (`like_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=46 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_locations`
--

CREATE TABLE IF NOT EXISTS `user_locations` (
  `user_id` int(9) unsigned NOT NULL,
  `moved_time` datetime NOT NULL,
  `location` varchar(255) COLLATE utf8_bin NOT NULL,
  `timezone` int(5) NOT NULL COMMENT 'timezone of the location',
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
  KEY `user_id_2` (`user_id`),
  KEY `photo_id` (`photo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

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
-- Constraints for table `user_locations`
--
ALTER TABLE `user_locations`
  ADD CONSTRAINT `user_locations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

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
