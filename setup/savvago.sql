
-- --------------------------------------------------------

--
-- Table structure for table `apps`
--

CREATE TABLE `apps` (
  `app_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` varchar(300) NOT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `attachment_id` int(10) UNSIGNED NOT NULL,
  `lesson_id` int(10) UNSIGNED NOT NULL,
  `content_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `ranking` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `content_objects`
--

CREATE TABLE `content_objects` (
  `object_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `type_id` int(10) UNSIGNED NOT NULL,
  `content` varchar(2000) NOT NULL,
  `name` varchar(300) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `md5_hash` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `content_types`
--

CREATE TABLE `content_types` (
  `type_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `is_internal` tinyint(3) UNSIGNED NOT NULL COMMENT 'is hosted internally? or external link, like youtube',
  `extension` varchar(30) DEFAULT NULL COMMENT 'file extension'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(300) NOT NULL,
  `university_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(2000) DEFAULT NULL,
  `subtitle` varchar(200) DEFAULT NULL,
  `image_name` varchar(100) NOT NULL,
  `video_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'promo video',
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `num_sections` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `num_lessons` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_id` int(10) UNSIGNED NOT NULL,
  `num_enrollments` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int(10) UNSIGNED NOT NULL,
  `from_name` varchar(64) DEFAULT NULL,
  `from_email` varchar(128) NOT NULL,
  `to_name` varchar(64) NOT NULL,
  `to_email` varchar(128) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `max_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT '3',
  `num_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `is_sent` tinyint(1) NOT NULL DEFAULT '0',
  `date_created` int(11) UNSIGNED DEFAULT NULL,
  `date_last_attempt` int(10) UNSIGNED DEFAULT NULL,
  `date_sent` int(10) UNSIGNED DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `lesson_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(300) NOT NULL,
  `title` varchar(100) NOT NULL,
  `section_id` int(10) UNSIGNED NOT NULL,
  `content_object_id` int(10) UNSIGNED DEFAULT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `rank` int(10) UNSIGNED NOT NULL,
  `description` varchar(2000) DEFAULT NULL,
  `section_rank` int(10) UNSIGNED NOT NULL,
  `image_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `progress`
--

CREATE TABLE `progress` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `reference_id` int(10) UNSIGNED NOT NULL COMMENT 'ref to course, topic, object...',
  `timestamp` int(10) UNSIGNED NOT NULL,
  `type` int(10) UNSIGNED NOT NULL,
  `value` varchar(200) DEFAULT NULL,
  `course_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `role_apps`
--

CREATE TABLE `role_apps` (
  `role_id` int(10) UNSIGNED NOT NULL,
  `app_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(300) NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(50) NOT NULL,
  `rank` int(10) UNSIGNED NOT NULL,
  `description` varchar(2000) DEFAULT NULL,
  `num_lessons` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  
-- --------------------------------------------------------

--
-- Table structure for table `service_cache`
--

CREATE TABLE `service_cache` (
  `tag` varchar(50) NOT NULL,
  `model_type` int(10) UNSIGNED NOT NULL,
  `model_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `universities`
--

CREATE TABLE `universities` (
  `university_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL COMMENT 'e.g. ''Software developer and teacher''',
  `display_name` varchar(100) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(255) NOT NULL,
  `info` text NOT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `verification_key` varchar(200) DEFAULT NULL,
  `type` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `is_active` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `password_recovery_key` varchar(32) DEFAULT NULL,
  `password_recovery_deadline` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `apps`
--
ALTER TABLE `apps`
  ADD PRIMARY KEY (`app_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`attachment_id`),
  ADD UNIQUE KEY `lesson_id` (`lesson_id`,`content_id`)
	;

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `content_objects`
--
ALTER TABLE `content_objects`
  ADD PRIMARY KEY (`object_id`),
  ADD UNIQUE KEY `course_id` (`course_id`,`name`)
  ;

--
-- Indexes for table `content_types`
--
ALTER TABLE `content_types`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `name` (`name`)
	;

--
-- Indexes for table `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `to_email` (`to_email`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`user_id`,`course_id`)
	;

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`lesson_id`),
  ADD UNIQUE KEY `name` (`name`,`course_id`)
	;

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`user_id`,`reference_id`,`type`)
	;
	
--
-- Indexes for table `role_apps`
--
ALTER TABLE `role_apps`
  ADD PRIMARY KEY (`role_id`,`app_id`)
	;

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD UNIQUE KEY `name` (`name`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `service_cache`
--
ALTER TABLE `service_cache`
  ADD PRIMARY KEY (`tag`);

--
-- Indexes for table `universities`
--
ALTER TABLE `universities`
  ADD PRIMARY KEY (`university_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `type` (`type`);

--
-- AUTO_INCREMENT for table `apps`
--
ALTER TABLE `apps`
  MODIFY `app_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `attachment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `content_objects`
--
ALTER TABLE `content_objects`
  MODIFY `object_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `content_types`
--
ALTER TABLE `content_types`
  MODIFY `type_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;  
  
--
-- AUTO_INCREMENT for table `email_queue`
--
ALTER TABLE `email_queue`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `lesson_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
  
--
-- AUTO_INCREMENT for table `universities`
--
ALTER TABLE `universities`
  MODIFY `university_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

  
--
-- Foreign Keys for table `attachments`
--
ALTER TABLE `attachments`
  ADD FOREIGN KEY (`lesson_id`) REFERENCES lessons(`lesson_id`),
  ADD FOREIGN KEY (`content_id`) REFERENCES content_objects(`object_id`)
	;

--
-- Foreign Keys for table `content_objects`
--
ALTER TABLE `content_objects`
  ADD FOREIGN KEY (`course_id`) REFERENCES courses(`course_id`)
  ;

--
-- Foreign Keys for table `courses`
--
ALTER TABLE `courses`
  ADD FOREIGN KEY (`user_id`) REFERENCES Users(`user_id`)
	;

--
-- Foreign Keys for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD FOREIGN KEY (`course_id`) REFERENCES Courses(`course_id`),
  ADD FOREIGN KEY (`user_id`) REFERENCES Users(`user_id`)
	;

--
-- Foreign Keys for table `lessons`
--
ALTER TABLE `lessons`
  ADD FOREIGN KEY (`course_id`) REFERENCES Courses(`course_id`),
  ADD FOREIGN KEY (`content_object_id`) REFERENCES content_objects(`object_id`),
  ADD FOREIGN KEY (`section_id`) REFERENCES Sections(`section_id`)
	;

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD FOREIGN KEY (`user_id`) REFERENCES Users(`user_id`)
	;

--
-- Indexes for table `role_apps`
--
ALTER TABLE `role_apps`
  ADD FOREIGN KEY (`app_id`) REFERENCES Apps(`app_id`)
	;
	
--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD FOREIGN KEY (`course_id`) REFERENCES Courses(`course_id`)
	;

