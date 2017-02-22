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
$databaseUser = null;
$databasePw = null;
$databaseHost = null;
$databaseName = null;

if (isset($_POST["dbName"])) {
	$databaseName = $_POST["dbName"];
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
?>


<?php if ($databaseUser == null) {?>
<h2>MySQL Database</h2>
<form method="post">
<p>Name <input type="text" name="dbName" placeholder="database name"/></p>
<p>Host <input type="text" name="dbHost" placeholder="database host"/></p>
<p>User <input type="text" name="dbUser" placeholder="database user name"/></p>
<p>Password <input type="password" name="dbPw" placeholder="database password"/></p>
<p><input type="submit" value="Install"></p>
</form>
<?php } ?>

<?php


set_time_limit(0);

class Message {
	public $text;
	public $isError;
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
$protocol = new Protocol();


// step 1: create database contents
// assumption: database already exists

$databaseName = "savvago_installation2";
$databaseUser = "root";
$databasePw = "";
$databaseHost = "localhost";

$dumpfilename = "savvago.sql";


$link = mysqli_connect($databaseHost, $databaseUser, $databasePw, $databaseName);
if ($link === false) {
	$protocol->addError(mysqli_error($link));
}
$protocol->addSuccess("Database exists");
$templine = '';
$lines = file($dumpfilename);
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
$protocol->addSuccess("Tables created");

// step 2: create folders and check access
$dirs[] = __DIR__ . '/../public/upload';
$dirs[] = __DIR__ . '/../temp';
$dirs[] = __DIR__ . '/../log';
foreach($dirs as $dir) {
	if (!is_dir($dir)) {
		mkdir($dir);
	}
}

$protocol->addSuccess("Directories created");

// step 3: update config file

// todo: security: generate machine salt for password hashing
// todo: security: generate token key

$protocol->addSuccess("Config updated todo");

// ganz einfach: in die default settings kommen platzhalter rein
// dann einfach str_replace machen

// step 4: create initial database content
$protocol->addSuccess("Database initialized todo");

// step 5: create admin user
$protocol->addSuccess("Administrator created todo");

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