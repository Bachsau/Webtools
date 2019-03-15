<?php

if (isset($_FILES['userfile']) && is_uploaded_file($_FILES['userfile']['tmp_name']) && ($_FILES['userfile']['error'] == UPLOAD_ERR_OK))
{
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename=' . $_FILES['userfile']['name'] . '.txt');
	$file = file_get_contents($_FILES['userfile']['tmp_name']);
	$data = base64_encode($file);
	echo 'data:' . $_FILES['userfile']['type'] . ';base64,';
	echo $data;
}
else
{
	header('Content-Type: text/html; charset=UTF-8');
	if (isset($_FILES['userfile']))
	{
		switch ($_FILES['userfile']['error'])
		{
			case UPLOAD_ERR_INI_SIZE: $error = 'Die hochgeladene Datei überschreitet die in php.ini festgelegte Größe (>' . ini_get('upload_max_filesize') . ').';
			break;
			case UPLOAD_ERR_PARTIAL:  $error = 'Die Datei wurde nur teilweise hochgeladen.';
			break;
			case UPLOAD_ERR_NO_FILE:  $error = 'Es wurde keine Datei hochgeladen.';
			break;
			default:                  $error = 'Du sollst nicht hacken.';
		}
	}
	else
	{
		$error = false;
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Datei mit Base64 kodieren</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	</head>
	<body>
		<h1>Datei mit Base64 kodieren</h1>
		<?php if ($error) echo "<p>Fehler: $error</p>"; ?>
		<form enctype="multipart/form-data" action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
			<p><input name="userfile" type="file"><input type="submit" value="Kodieren"></p>
		</form>
	</body>
</html>

<?php
}
