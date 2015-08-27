<?php
error_reporting(E_ALL);

if (isset($_POST['submit']) && $_POST['submit'] == 'submit') {
	// Hacking CI BASEPATH validation.
	define('BASEPATH', 'dummy');
	$database = file_get_contents(dirname(__FILE__) . '/cc.sql');
	include_once '../application/config/database.php';

	$mysqli = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);

	// Setup database and create user in database.
	$query = <<<PQR
INSERT INTO `user` (`username`, `name`, `email`, `password`, `accounts`, `principal_account`, `validated`, `ts`)
VALUES ('{$_POST['username']}', '{$_POST['name']}', '{$_POST['email']}', md5('{$_POST['password']}'), 'cc', 'cc', '1', CURRENT_TIMESTAMP);
PQR;
	$mysqli->multi_query($database . $query) or die($mysqli->error);

	// Add user as particiant into cc acount.
	$username_data = json_encode(array(
			'info' => array(
					'id' => $_POST['username'],
					'language' => 'english',
			),
                        'type' => 'administrator',
	));
	file_put_contents(dirname(__FILE__) . '/../_accounts/cc/_participants/' . $_POST['username'] . '.json', $username_data);

	exit('Setup successful. Please delete setup directory a <a href="..">go to Home</a>');
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>CarbonCopy - Setup</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<link rel="stylesheet" href="../pub/css/reset.css" />
		<link rel="stylesheet" href="../pub/formalize/css/formalize.css" />
		<link rel="stylesheet" href="../pub/web_tpl/css/main.css" />
		<style type="text/css">
			.content{
				text-align: left
			}
		</style>
	</head>
	<body>
		<div class="content" style="background: #fff; width: 740px; padding: 7px">
			<div id="cc">
				<a href="http://cc-dev/" title="CarbonCopy - Collaboration Manager"><span>CC</span></a>
				<h2>CarbonCopy</h2>
			</div>
			<div class="clear"></div>
			<hr />
			<h3>Configuration</h3>
			<hr />
			<i>CarbonCopy is developed using CodeIgniter 2.2.0 PHP Framework and wiredesignz HMVC.</i>
			<ol>
				<li>Rename file application/config/config-dist.php to application/config/config.php.</li>
				<li>Rename file application/config/database-dist.php to application/config/database.php.</li>
				<li>Rename file application/config/constants-dist.php to application/config/constants.php.</li>
				<li>Modify file application/config/config.php to set Base Site URL.</li>
				<li>Modify file application/config/database.php to set database conection settings.</li>
				<li>Create database using name used in database.php</li>
				<li>Make sure that _accounts/cc and application/logs is writeable recursively.</li>
				<li>If rewrite engine is not use, disable it in .htaccess.</li>
				<li>And add first user in next form</li>
			</ol>
			<h1>Setup firt user</h1>
			<form action="" method="post" accept-charset="utf-8" class="gf">
				<label><span>Name</span><input type="text" name="name" maxlength="75" required></label>
				<label><span>Username</span><input type="text" name="username" id="user" maxlength="50" required></label>
				<label><span>Email</span><input type="email" name="email" id="email" maxlength="254" required></label>
				<label><span>Password</span><input type="password" name="password" maxlength="75" required></label>
				<label><button type="submit" name="submit" value="submit">register</button></label>
			</form>
		</div>
	</body>
</html>