
--
-- Dumping data for table `apps`
--

INSERT INTO `apps` (`app_id`, `name`, `title`, `description`, `is_active`, `role_id`) VALUES
(1, 'app-management', 'Apps', 'Manage Apps', 1, 4),
(2, 'user-management', 'Users', 'Manage Users', 1, 4),
(3, 'lesson-management', 'Lessons', 'Manage Lessons, assign Roles', 1, 3),
(4, 'journey-management', 'Journeys', 'Manage journeys', 1, 3);

--
-- Dumping data for table `content_types`
--

INSERT INTO `content_types` (`type_id`, `name`, `source`, `extension`) VALUES
(1, 'jpg', 2, 'jpg'),
(2, 'youtube', 1, null);

