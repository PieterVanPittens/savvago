<html>
<head>
<title>savvago - Installer</title>
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

<?php } ?>



PauL  2.0

mariel brian
klaudia x
robin s katja
moritz s katja
robin k x
eric jörb


<?php

die();
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

$databaseName = "savvago_installation";
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
$dirs[] = __DIR__ . '/../cache';
$dirs[] = __DIR__ . '/../cache/service';
foreach($dirs as $dir) {
	if (!is_dir($dir)) {
		mkdir($dir);
	}
}

$protocol->addSuccess("Created Dirs");

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