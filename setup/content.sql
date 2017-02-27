
--
-- Dumping data for table `apps`
--

INSERT INTO `apps` (`app_id`, `name`, `title`, `description`, `is_active`) VALUES
(1, 'app-management', 'App Management', 'Manage Apps', 1),
(2, 'content-management', 'Content Management', 'Manage content across all courses', 1),
(3, 'course-editor', 'Course Editor', 'Craft beautiful courses', 1),
(4, 'user-management', 'User Management', 'De/activate Users, assign Roles', 1),
(5, 'course-management', 'Course Management', 'Delete, create, un/publish Courses', 1);

--
-- Dumping data for table `content_types`
--

INSERT INTO `content_types` (`type_id`, `name`, `is_internal`, `extension`) VALUES
(1, 'youtube', 0, NULL),
(2, 'pdf', 1, 'pdf'),
(3, 'yaml', 1, 'yaml'),
(5, 'png', 1, 'png'),
(6, 'zip', 1, 'zip');


--
-- Dumping data for table `role_apps`
--

INSERT INTO `role_apps` (`role_id`, `app_id`) VALUES
(3, 2),
(3, 3),
(3, 5),
(4, 1),
(4, 4);