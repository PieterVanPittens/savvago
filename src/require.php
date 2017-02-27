<?php
// load savvago stuff
require __DIR__ . '/imodel.php';
require __DIR__ . '/basemodel.php';
require __DIR__ . '/icachable.php';
require __DIR__ . '/basemanager.php';
require __DIR__ . '/baseservice.php';
require __DIR__ . '/basepdorepository.php';
require __DIR__ . '/model.php';
require __DIR__ . '/ImageManager.php';

require __DIR__ . '/UserService/helper.php';
require __DIR__ . '/UserService/manager.php';
require __DIR__ . '/UserService/repository.php';
require __DIR__ . '/UserService/role.php';
require __DIR__ . '/UserService/service.php';
require __DIR__ . '/UserService/user.php';
require __DIR__ . '/UserService/usertypes.php';
require __DIR__ . '/UserService/usercontainer.php';

require __DIR__ . '/ContentService/contentobject.php';
require __DIR__ . '/ContentService/contenttype.php';
require __DIR__ . '/ContentService/manager.php';
require __DIR__ . '/ContentService/repository.php';

require __DIR__ . '/UniversityService/manager.php';
require __DIR__ . '/UniversityService/repository.php';
require __DIR__ . '/UniversityService/service.php';
require __DIR__ . '/UniversityService/university.php';

require __DIR__ . '/CourseService/category.php';
require __DIR__ . '/CourseService/course.php';
require __DIR__ . '/CourseService/enrollment.php';
require __DIR__ . '/CourseService/lesson.php';
require __DIR__ . '/CourseService/manager.php';
require __DIR__ . '/CourseService/progress.php';
require __DIR__ . '/CourseService/progresstypes.php';
require __DIR__ . '/CourseService/repository.php';
require __DIR__ . '/CourseService/section.php';
require __DIR__ . '/CourseService/service.php';

require __DIR__ . '/ServiceCache/manager.php';
require __DIR__ . '/ServiceCache/repository.php';


require __DIR__ . '/MailService/mail.php';

require __DIR__ . '/AppService/app.php';
require __DIR__ . '/AppService/manager.php';
require __DIR__ . '/AppService/repository.php';
require __DIR__ . '/AppService/service.php';

require __DIR__ . '/displayUser.php';
require __DIR__ . '/mvc.php';
require __DIR__ . '/helpers.php';


// create container for business logic
require __DIR__ . '/serviceContainer.php';

?>