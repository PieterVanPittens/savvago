SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `attachments`;
CREATE TABLE IF NOT EXISTS `attachments` (
  `lesson_id` int(10) unsigned NOT NULL,
  `content_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`lesson_id`,`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `ranking` int(10) unsigned NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=20 ;

DROP TABLE IF EXISTS `content_objects`;
CREATE TABLE IF NOT EXISTS `content_objects` (
  `object_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int(10) unsigned NOT NULL,
  `content` varchar(2000) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`object_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=69 ;

DROP TABLE IF EXISTS `content_types`;
CREATE TABLE IF NOT EXISTS `content_types` (
  `type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`type_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `course_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `university_id` int(10) unsigned NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(2000) DEFAULT NULL,
  `subtitle` varchar(200) DEFAULT NULL,
  `image_name` varchar(100) NOT NULL,
  `video_id` int(10) unsigned DEFAULT NULL COMMENT 'promo video',
  `category_id` int(10) unsigned DEFAULT NULL,
  `num_sections` int(10) unsigned NOT NULL DEFAULT '0',
  `num_lessons` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `num_enrollments` int(10) unsigned NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`course_id`),
  UNIQUE KEY `name` (`name`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

DROP TABLE IF EXISTS `email_queue`;
CREATE TABLE IF NOT EXISTS `email_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from_name` varchar(64) DEFAULT NULL,
  `from_email` varchar(128) NOT NULL,
  `to_name` varchar(64) NOT NULL,
  `to_email` varchar(128) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `max_attempts` tinyint(3) unsigned NOT NULL DEFAULT '3',
  `num_attempts` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_sent` tinyint(1) NOT NULL DEFAULT '0',
  `date_created` int(11) unsigned DEFAULT NULL,
  `date_last_attempt` int(10) unsigned DEFAULT NULL,
  `date_sent` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `to_email` (`to_email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `enrollments`;
CREATE TABLE IF NOT EXISTS `enrollments` (
  `user_id` int(10) unsigned NOT NULL,
  `course_id` int(10) unsigned NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `lessons`;
CREATE TABLE IF NOT EXISTS `lessons` (
  `lesson_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `section_id` int(10) unsigned NOT NULL,
  `content_object_id` int(10) unsigned DEFAULT NULL,
  `course_id` int(10) unsigned NOT NULL,
  `rank` int(10) unsigned NOT NULL,
  `description` varchar(2000) DEFAULT NULL,
  `section_rank` int(10) unsigned NOT NULL,
  `image_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`lesson_id`),
  UNIQUE KEY `name` (`name`,`course_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=128 ;

DROP TABLE IF EXISTS `progress`;
CREATE TABLE IF NOT EXISTS `progress` (
  `user_id` int(10) unsigned NOT NULL,
  `reference_id` int(10) unsigned NOT NULL COMMENT 'ref to course, topic, object...',
  `timestamp` int(10) unsigned NOT NULL,
  `type` int(10) unsigned NOT NULL,
  `value` varchar(200) DEFAULT NULL,
  `course_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`reference_id`,`type`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `sections`;
CREATE TABLE IF NOT EXISTS `sections` (
  `section_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `course_id` int(10) unsigned NOT NULL,
  `title` varchar(50) NOT NULL,
  `rank` int(10) unsigned NOT NULL,
  `description` varchar(2000) DEFAULT NULL,
  `num_lessons` int(10) unsigned NOT NULL,
  PRIMARY KEY (`section_id`),
  UNIQUE KEY `name` (`name`,`course_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=115 ;

DROP TABLE IF EXISTS `service_cache`;
CREATE TABLE IF NOT EXISTS `service_cache` (
  `tag` varchar(50) NOT NULL,
  `model_type` int(10) unsigned NOT NULL,
  `model_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `universities`;
CREATE TABLE IF NOT EXISTS `universities` (
  `university_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(1000) NOT NULL,
  PRIMARY KEY (`university_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL COMMENT 'e.g. ''Software developer and teacher''',
  `display_name` varchar(100) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(255) NOT NULL,
  `info` text NOT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `verification_key` varchar(200) DEFAULT NULL,
  `type` int(10) unsigned NOT NULL DEFAULT '1',
  `is_active` int(10) unsigned NOT NULL DEFAULT '1',
  `password_recovery_key` varchar(32) DEFAULT NULL,
  `password_recovery_deadline` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;
