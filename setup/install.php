<?php
/**
 * imports sql dump to database
 * @param string $filename
 * @param string $link
 */
function importDatabaseDump($filename, $link) {
	$templine = '';
	$lines = file($filename);
	foreach ($lines as $line) {
		// Skip it if it's a comment
		if (substr($line, 0, 2) == '--' || $line == '') continue;
		// Add this line to the current segment
		$templine .= $line;
		// If it has a semicolon at the end, it's the end of the query
		if (substr(trim($line), -1, 1) == ';') {
			// Perform the query
			mysqli_query($link, $templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysqli_error($link) . '<br /><br />');
			// Reset temp variable to empty
			$templine = '';
		}
	}
}


class Protocol {
	public $messages = array();
	
	function addError($text) {
		$message = new Message();
		$message->text = $text;
		$message->isError = true;
		$this->messages[] = $message;
	}
	function addSuccess($text) {
		$message = new Message();
		$message->text = $text;
		$message->isError = false;
		$this->messages[] = $message;
	}
}

?>
<html>
<head>
<title>savvago - Installer</title>
<style>
body {
	font-family: sans-serif;
	}

</style>
</head>
<body>
<h1>savvago - Installer</h1>

<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/url_slug/url_slug.php';
require '../src/require.php';

$databaseUser = null;
$databasePw = null;
$databaseHost = null;
$databaseName = null;
$baseUri = 'http://localhost/';

$databaseName = "deleteme4";
$databaseUser = "root";
$databasePw = "";
$databaseHost = $_SERVER['SERVER_NAME'];

$letsGo = false;

if (isset($_POST["dbName"])) {
	$databaseName = $_POST["dbName"];
	$letsGo = true;
}
if (isset($_POST["dbUser"])) {
	$databaseUser = $_POST["dbUser"];
}
if (isset($_POST["dbPw"])) {
	$databasePw = $_POST["dbPw"];
}
if (isset($_POST["dbHost"])) {
	$databaseHost = $_POST["dbHost"];
}
if (isset($_POST["baseUri"])) {
	$baseUri = $_POST["baseUri"];
}
?>


<?php if (!$letsGo) {?>
	<form method="post">
	<h2>MySQL Database</h2>
	<p>Name <input type="text" name="dbName" placeholder="database name" value="<?= $databaseName; ?>"/></p>
	<p>Host <input type="text" name="dbHost" placeholder="database host" value="<?= $databaseHost; ?>"/></p>
	<p>User <input type="text" name="dbUser" placeholder="database user name" value="<?= $databaseUser; ?>"/></p>
	<p>Password <input type="password" name="dbPw" placeholder="database password" value="<?= $databasePw; ?>"/></p>
	<h2>Host</h2>
	<p>Base Uri <input type="text" name="baseUri" placeholder="Base Uri" value="<?= $baseUri; ?>"/></p>


	<p><input type="submit" value="Install"></p>
	</form>
<?php } else { 


set_time_limit(0);


$protocol = new Protocol();

$installationSteps = array(
		array('enabled' => true, 'title' => 'Installing Database')
		,array('enabled' => true, 'title' => 'Creating Directories')
		,array('enabled' => true, 'title' => 'Creating Configfile')
		,array('enabled' => true, 'title' => 'Initializing Database')
		,array('enabled' => true, 'title' => 'Creating Administrator Account')
);




$link = mysqli_connect($databaseHost, $databaseUser, $databasePw, $databaseName);
if ($link === false) {
	$protocol->addError(mysqli_error($link));
}
$protocol->addSuccess("Database exists");



$currStep = 0;
// step 1: create database contents
// assumption: database already exists
if ($installationSteps[$currStep]['enabled'] === true) {
	
	$dumpfilename = "savvago.sql";
	importDatabaseDump($dumpfilename, $link);
	$protocol->addSuccess("Tables created");
}



// step 2: create folders and check access
$currStep++;
if ($installationSteps[$currStep]['enabled'] === true) {
	$dirs[] = __DIR__ . '/../public/upload';
	$dirs[] = __DIR__ . '/../temp';
	$dirs[] = __DIR__ . '/../log';
	foreach($dirs as $dir) {
		if (!is_dir($dir)) {
			mkdir($dir);
		}
	}
	
	$protocol->addSuccess("Directories created");
}

// step 3: update config file
$currStep++;
if ($installationSteps[$currStep]['enabled'] === true) {

	$configFileDist = __DIR__ . '/../config/config-dist.php';
	$configFileTarget = __DIR__ . '/../config/config.php';
	
	if (!file_exists($configFileDist)) {
		die('Installation aborted. File '.$configFileDist.' does not exist. Check your installation folders.');
	}
	if (file_exists($configFileTarget)) {
		$protocol->addError('config.php already exists. Did not update this file');
	} else {
	
		$tokenKey = 'todo-make-me-secure'; // TODO: random crypto string needed
		$passwordSalt = 'todo-me-too'; // TODO: random crypto string needed
		
		$config['{{applicationName}}'] = 'savvago';
		$config['{{applicationClaim}}'] = 'you savvy?';
		$config['{{applicationBaseUri}}'] = $baseUri;
		$config['{{applicationApiUri}}'] = $baseUri . 'api/';
		$config['{{applicationSenderEmail}}'] = '';
		$config['{{dbHost}}'] = $databaseHost;
		$config['{{dbName}}'] = $databaseName;
		$config['{{dbUser}}'] = $databaseUser;
		$config['{{dbPass}}'] = $databasePw;
		$config['{{securityPasswordSalt}}'] = $passwordSalt;
		$config['{{securityTokenKey}}'] = $tokenKey;
		
		$content = file_get_contents($configFileDist);
		foreach($config as $key => $value) {
			$content = str_replace($key, $value, $content);
		}
		file_put_contents($configFileTarget, $content);
		
		$protocol->addSuccess('Config updated');
	}
}

// now we've got a configfile, so we can load it into the container
$settings = require __DIR__ . '/../config/config.php';
$serviceContainer['settings'] = $settings['settings'];


// step 4: create initial database content
$currStep++;
if ($installationSteps[$currStep]['enabled'] === true) {

	$dumpfilename = "content.sql";
	importDatabaseDump($dumpfilename, $link);
	$protocol->addSuccess("Database initialized");
}


// step 5: create admin user
$currStep++;
if ($installationSteps[$currStep]['enabled'] === true) {
	
	$adminName = 'Admin';
	$adminPw = 'MakeMeRandomAndSecure'; // TODO: random crypto string needed
	
	/**
	 * 
	 * @var UserManager $userManager
	 */
	$userManager = $serviceContainer['userManager'];
	
	/**
	 * 
	 * @var User $adminUser
	 */
	$adminUser = new User();
	$adminUser->displayName = 'Administrator';
	$adminUser->email = 'admin@domain.de';
	$adminUser->isActive = true;
	$adminUser->isVerified = true;
	$adminUser->password = $adminPw;
	$adminUser->type = UserTypes::Admin;
	$result = $userManager->registerUser($adminUser);
	$userManager->verifyEmail($result->object->email, $result->object->verificationKey);

	$protocol->addSuccess("Administrator created");
	
	?>
	<h1>Admin User Created</h1>
	<b>Attention: Note down this username and password. This is THE admin Login for your new savvago installation.</b>
	<h2>Username: <?php echo $adminName; ?></h2>
	<h2>Password: <?php echo $adminPw; ?></h2>
	<?php 	
}
?>

<table border="1">
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

} ?>

<body>
</html>