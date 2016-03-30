-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 18, 2016 at 07:41 PM
-- Server version: 5.6.27-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.14

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `auction`
--

-- --------------------------------------------------------

--
-- Table structure for table `Actions`
--

CREATE TABLE IF NOT EXISTS `Actions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `action_id` int(10) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action_id` (`action_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ActionTypes`
--

CREATE TABLE IF NOT EXISTS `ActionTypes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(10) unsigned NOT NULL,
  `action_type` enum('create','comment','bid','reply','change_description','delete','item_expire') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Bids`
--

CREATE TABLE IF NOT EXISTS `Bids` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `amount` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Categories`
--

CREATE TABLE IF NOT EXISTS `Categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(75) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`category`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `CategoryAffinities`
--

CREATE TABLE IF NOT EXISTS `CategoryAffinities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `affinity_score` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `USER_CATEGORY` (`user_id`,`category_id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Comments`
--

CREATE TABLE IF NOT EXISTS `Comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `text` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `item_id` int(11) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Items`
--

CREATE TABLE IF NOT EXISTS `Items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `starting_price` int(10) unsigned NOT NULL,
  `increment` int(10) unsigned NOT NULL,
  `filename` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  FULLTEXT KEY `name, description` (`name`,`description`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Newsfeed`
--

CREATE TABLE IF NOT EXISTS `Newsfeed` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `action_id` int(10) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `user_id` (`user_id`),
  KEY `action_id` (`action_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `NewsfeedUsers`
--

CREATE TABLE IF NOT EXISTS `NewsfeedUsers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `story_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `story_id` (`story_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Notifications`
--

CREATE TABLE IF NOT EXISTS `Notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `action_id` int(10) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `action_id` (`action_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `NotificationUsers`
--

CREATE TABLE IF NOT EXISTS `NotificationUsers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `notification_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `notification_id` (`notification_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `UserAffinities`
--

CREATE TABLE IF NOT EXISTS `UserAffinities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `friend_id` bigint(20) unsigned NOT NULL,
  `affinity_score` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `USER_FRIEND` (`user_id`,`friend_id`),
  KEY `friend_id` (`friend_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE IF NOT EXISTS `Users` (
  `fb_id` bigint(20) unsigned NOT NULL,
  `first_name` varchar(75) NOT NULL,
  `last_name` varchar(75) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `profile_small` varchar(400) NOT NULL,
  `profile_large` varchar(400) NOT NULL,
  `last_notification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_newsfeed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fb_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Watchlist`
--

CREATE TABLE IF NOT EXISTS `Watchlist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `is_user_created` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Actions`
--
ALTER TABLE `Actions`
  ADD CONSTRAINT `Actions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`fb_id`),
  ADD CONSTRAINT `Actions_ibfk_3` FOREIGN KEY (`action_id`) REFERENCES `ActionTypes` (`id`);

--
-- Constraints for table `ActionTypes`
--
ALTER TABLE `ActionTypes`
  ADD CONSTRAINT `ActionTypes_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `Items` (`id`);

--
-- Constraints for table `Bids`
--
ALTER TABLE `Bids`
  ADD CONSTRAINT `Bids_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`fb_id`),
  ADD CONSTRAINT `Bids_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `Items` (`id`);

--
-- Constraints for table `CategoryAffinities`
--
ALTER TABLE `CategoryAffinities`
  ADD CONSTRAINT `CategoryAffinities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`fb_id`),
  ADD CONSTRAINT `CategoryAffinities_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `Categories` (`id`);

--
-- Constraints for table `Comments`
--
ALTER TABLE `Comments`
  ADD CONSTRAINT `Comments_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `Items` (`id`),
  ADD CONSTRAINT `Comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`fb_id`);

--
-- Constraints for table `Items`
--
ALTER TABLE `Items`
  ADD CONSTRAINT `Items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`fb_id`),
  ADD CONSTRAINT `Items_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `Categories` (`id`);

--
-- Constraints for table `Newsfeed`
--
ALTER TABLE `Newsfeed`
  ADD CONSTRAINT `Newsfeed_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `Items` (`id`),
  ADD CONSTRAINT `Newsfeed_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`fb_id`),
  ADD CONSTRAINT `Newsfeed_ibfk_3` FOREIGN KEY (`action_id`) REFERENCES `ActionTypes` (`id`);

--
-- Constraints for table `NewsfeedUsers`
--
ALTER TABLE `NewsfeedUsers`
  ADD CONSTRAINT `NewsfeedUsers_ibfk_1` FOREIGN KEY (`story_id`) REFERENCES `Newsfeed` (`id`),
  ADD CONSTRAINT `NewsfeedUsers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`fb_id`);

--
-- Constraints for table `Notifications`
--
ALTER TABLE `Notifications`
  ADD CONSTRAINT `Notifications_ibfk_2` FOREIGN KEY (`action_id`) REFERENCES `Actions` (`id`),
  ADD CONSTRAINT `Notifications_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `Users` (`fb_id`);

--
-- Constraints for table `NotificationUsers`
--
ALTER TABLE `NotificationUsers`
  ADD CONSTRAINT `NotificationUsers_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `Notifications` (`id`),
  ADD CONSTRAINT `NotificationUsers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`fb_id`);

--
-- Constraints for table `UserAffinities`
--
ALTER TABLE `UserAffinities`
  ADD CONSTRAINT `UserAffinities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`fb_id`),
  ADD CONSTRAINT `UserAffinities_ibfk_2` FOREIGN KEY (`friend_id`) REFERENCES `Users` (`fb_id`);

--
-- Constraints for table `Watchlist`
--
ALTER TABLE `Watchlist`
  ADD CONSTRAINT `Watchlist_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `Items` (`id`),
  ADD CONSTRAINT `Watchlist_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`fb_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
