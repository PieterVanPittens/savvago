<html>
<head>
<title>Import content</title>
</head>
<body>

<?php
set_time_limit(0);

class ProtocolMessage {
	public $text;
	public $isError;
}
class Protocol {
	public $messages = array();
	
	function addError($text) {
		$message = new ProtocolMessage();
		$message->text = $text;
		$message->isError = true;
		$this->messages[] = $message;
	}
	function addSuccess($text) {
		$message = new ProtocolMessage();
		$message->text = $text;
		$message->isError = false;
		$this->messages[] = $message;
	}
}
$protocol = new Protocol();

require __DIR__ . '/../vendor/pimple/pimple/src/Pimple/Container.php';
require __DIR__ . '/../vendor/slim/php-view/src/PhpRenderer.php';
require __DIR__ . '/../vendor/psr/http-message/src/MessageInterface.php';
require __DIR__ . '/../vendor/psr/http-message/src/ResponseInterface.php';
require __DIR__ . '/../vendor/psr/http-message/src/StreamInterface.php';
require __DIR__ . '/../vendor/slim/slim/Slim/Interfaces/CollectionInterface.php';
require __DIR__ . '/../vendor/slim/slim/Slim/Interfaces/Http/HeadersInterface.php';
require __DIR__ . '/../vendor/slim/slim/Slim/Collection.php';
require __DIR__ . '/../vendor/slim/slim/Slim/Http/Stream.php';
require __DIR__ . '/../vendor/slim/slim/Slim/Http/Body.php';
require __DIR__ . '/../vendor/slim/slim/Slim/Http/Headers.php';
require __DIR__ . '/../vendor/slim/slim/Slim/Http/Message.php';
require __DIR__ . '/../vendor/slim/slim/Slim/Http/Response.php';



require __DIR__ . '/../vendor/url_slug/url_slug.php';


require __DIR__ . '/../src/repository.php';


$settings = require __DIR__ . '/../src/settings.php';

require __DIR__ . '/../src/serviceContainer.php';
$serviceContainer['settings'] = $settings['settings'];

require __DIR__ . '/../src/model.php';
require __DIR__ . '/../src/manager.php';
require __DIR__ . '/../src/university.php';
require __DIR__ . '/../src/user.php';
require __DIR__ . '/../src/content.php';
require __DIR__ . '/../src/mail.php';
require __DIR__ . '/../src/ImageManager.php';
require __DIR__ . '/../src/mvc.php';

error_reporting(E_ALL);


$serviceContainer['contextUser'] = function ($c) {
	$user = new User();
	$user->userId = 1;
	$user->name = "admin";
	return $user;
};



$courseManager = $serviceContainer['courseManager'];
$courseService = $serviceContainer['courseService'];

$path = __DIR__ . '/../content/';
$files = scandir($path);
$files = array_diff(scandir($path), array('.', '..'));

$user = new User();
$user->userId = 1;
$user->name = "user";

$files = array();
//$files[] = "minus 3 lessons.zip";
//$files[] = "plus 2 sections.zip";
//$files[] = "plus 2 lessons.zip";
//$files[] = "move lessons1.zip";
$files[] = "minus complete section.zip";

foreach($files as $contentFilename) {
	$filename = __DIR__ . '/../content/' . $contentFilename;
	try {
		$result = $courseService->importCourse($user, 1, $filename);
		if ($result->message->type == MessageTypes::Success) {
			//$courseManager->publishCourse($result->object, true);
			$protocol->addSuccess("course imported: " . $result->object->name);
		} else {
			$text = $result->message->text;
			if (is_array($result->object)) {
				$text .= " ";
				foreach($result->object as $s) {
					$text .= "$s, ";
				}
			}

			$protocol->addError($text);
		}
		
	} catch (GrapesException $ex) {
		$protocol->addError($ex->apiMessage->text);
	}
}
?>

<table border=1>
<?php
foreach($protocol->messages as $message) {
	?>
	<tr>
		<td><?= $message->isError ? ":-(" : ":-)"; ?></td>
		<td><?= $message->text; ?></td>
	</tr>
	<?php
}
?>
</table>
<?php
echo "MEM Peak: " .(memory_get_peak_usage(true) / 1024/1024). " MB\n";
?>