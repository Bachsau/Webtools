<?php

header('Content-Type: text/html; charset=UTF-8');
header('Expires: 0');
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');

if (isset($_GET['source'])) {
	highlight_file(__FILE__);
	exit();
}


/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

error_reporting(E_ALL);

function lohnsteuer($steuerwert, $grundfrei) {
	if ($steuerwert <= $grundfrei) {
	$lohnsteuer = 0;
	}
	elseif ($steuerwert < 12740) {
		$y = ($steuerwert-$grundfrei) / 10000;
		$lohnsteuer = (883.74 * $y + 1500) * $y;
	}
	elseif ($steuerwert < 52152) {
		$z = ($steuerwert-12793) / 10000;
		$lohnsteuer = (228.74 * $z + 2397) * $z + 989;
	}
	else {
		$lohnsteuer = 0.42 * $steuerwert - 7914;
	}
	return floor($lohnsteuer);
}

$error = false;
$message = array();

if (isset($_POST['submit'])) {
	
	// Daten vorbereiten
	$klasse = isset($_POST['klasse']) ? (int) $_POST['klasse'] : 0;
	$kiddies = isset($_POST['kiddies']) ? str_replace(',', '.', $_POST['kiddies']) : 0;
	$brutto = isset($_POST['lohn']) ? (float) str_replace(',', '.', $_POST['lohn']) : 0;
	$kirche = isset($_POST['kirche']) ? (bool) $_POST['kirche'] : true;

	// Daten pruefen
	if ( ($klasse < 1) OR ($klasse > 6) ) {
		$error = true;
		$message[] = 'Lohnsteuerklasse muss zwischen 1 und 6 liegen.';
	}
	if ( ctype_digit($kiddies) ) {
		$kiddies = intval($kiddies);
	}
	else {
		$error = true;
		$message[] = 'Deine Kinderzahl gefällt mir, aber damit kann ich nicht rechnen...';
	}
	if ( $kiddies < 0 ) {
		$error = true;
		$message[] = 'Negative Kinderzahl? Sachen gibts... *tztztz*';
	}
	elseif ( $kiddies > 99 ) {
		$error = true;
		$message[] = $kiddies . ' Kinder dürften schon rein biologisch unmöglich sein.';
	}
	if ( $klasse == 2 AND $kiddies == 0) {
		$error = true;
		$message[] = 'Ohne Kinder ist Lohnsteuerklasse II unmöglich!';
	}
	if ( $brutto < 1 ) {
		$error = true;
		$message[] = 'Cool, ' . $brutto . ' €, soviel will ich auch mal verdienen! ;)';
	}

	if (!$error) {
	
		// Klassen festlegen
		switch ($klasse) {
		case 1:
			$grundfrei = 7664;
			$arbeitnehmer = 920;
			$sonderausgaben = 36;
			$vorsorge = min($brutto * 0.11, 1500) + ($brutto * 0.02789);
			$alleinerz = 0;
			$kinderfrei = 5808 * $kiddies;
		break;
		case 2:
			$grundfrei = 7664;
			$arbeitnehmer = 920;
			$sonderausgaben = 36;
			$vorsorge = min($brutto * 0.11, 1500) + ($brutto * 0.02789);
			$alleinerz = 1308;
			$kinderfrei = 5808 * $kiddies;
		break;
		case 3:
			$grundfrei = 15328;
			$arbeitnehmer = 920;
			$sonderausgaben = 72;
			$vorsorge = min($brutto * 0.11, 1500) + ($brutto * 0.02789);
			$alleinerz = 0;
			$kinderfrei = 5808 * $kiddies;
		break;
		case 4:
			$grundfrei = 7664;
			$arbeitnehmer = 920;
			$sonderausgaben = 36;
			$vorsorge = min($brutto * 0.11, 1500) + ($brutto * 0.02789);
			$alleinerz = 0;
			$kinderfrei = 2904 * $kiddies;
		break;
		case 5:
			$grundfrei = 0;
			$arbeitnehmer = 920;
			$sonderausgaben = 0;
			$vorsorge = 0;
			$alleinerz = 0;
			$kinderfrei = 0;
		break;
		case 6:
			$grundfrei = 0;
			$arbeitnehmer = 0;
			$sonderausgaben = 0;
			$vorsorge = 0;
			$alleinerz = 0;
			$kinderfrei = 0;
		}
	
		// Absetzen
		$absetzen = $arbeitnehmer;
		$absetzen += $sonderausgaben;
		$absetzen += $vorsorge;
		$absetzen += $alleinerz;
		$absetzen += $kinderfrei;
		$absetzen = round($absetzen, 2);
		
		// Zu versteuerndes Einkommen
		$steuerwert = floor($brutto - $absetzen);
		$steuerwert = max($steuerwert, 0);
		
		// Lohnsteuer berechnen
		if ($klasse == 5 OR $klasse == 6) {
			$sw1 = floor(($brutto * 1.25) - $absetzen);
			$sw2 = floor(($brutto * 0.75) - $absetzen);
			
			$lst1 = lohnsteuer($sw1, $grundfrei);
			$lst2 = lohnsteuer($sw2, $grundfrei);
			
			$lohnsteuer = ($lst1 - $lst2) * 2;
			$lohnsteuer = max($brutto * 0.15, $lohnsteuer);
		}
		else {
			$lohnsteuer = lohnsteuer($steuerwert, $grundfrei);
		}
		$lohnsteuer = floor($lohnsteuer);
	
		// Restliche Steuern
		$rv = round(($brutto * 0.199 / 2), 2);
		$kv = round(($brutto * 0.149 / 2), 2);
		$av = round(($brutto * 0.03 / 2), 2);
		$pv = round(($brutto * 0.0195 / 2), 2); // Bei Arbeitnehmern. (http://de.wikipedia.org/wiki/Pflegeversicherung_(Deutschland)#Gesetzlich_Versicherte)
		$ks = round(($kirche ? ($lohnsteuer * 0.09) : 0), 2);
		$sz = round(($lohnsteuer * 0.055), 2);
	
		// Nettolohn
		$netto = $brutto;
		$netto -= $lohnsteuer;
		$netto -= $rv;
		$netto -= $kv;
		$netto -= $av;
		$netto -= $pv;
		$netto -= $ks;
		$netto -= $sz;
		$netto = round($netto, 2);
		
	}

}
else {

	// Daten vorbereiten
	$klasse = 1;
	$kiddies = 0;
	$brutto = 0;
	$kirche = true;

}

$title = (isset($_POST['submit']) AND (!$error)) ? 'Steuersystem: Ergebnisanzeige' : 'Steuersystem: Datenabfrage';


// Daten ausgeben
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo $title ?></title>

<!-- Ein Stylesheet verwenden? -->
<!-- link rel="stylesheet" type="text/css" href="style.css" -->

<script language="php">
	// HTML Ausgabe
	$kirche_html[0] = $kirche ? ' checked' : '';
	$kirche_html[1] = $kirche ? '' : ' checked';
	$kiddies_html = str_replace('.', ',', $kiddies);
	$brutto_html = str_replace('.', ',', $brutto);
	$klasse_html = $klasse ? $klasse : '';
</script>

</head>
<body style="width:750px;">

<h1><?php echo $title ?></h1>

<?php if ((!isset($_POST['submit'])) OR $error): ?>

<?php if ($error): ?>
	<h2>Eingabefehler:</h2>
	<ul>
	<?php foreach ($message as $msg): ?>
		<li><?php echo $msg ?></li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>

<h2>Was ich von dir brauche:</h2>
<form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
	<table cellpadding="4" cellspacing="0">
		<tr>
			<td>Lohnsteuerklasse:</td>
			<td><input type="text" name="klasse" size="1" maxlength="1" value="<?php echo $klasse_html ?>"></td>
		</tr>
		<tr>
			<td>Bruttojahreslohn:</td>
			<td><input type="text" name="lohn" value="<?php echo $brutto_html ?>"></td>
		</tr>
		<tr>
			<td>Kiddies:</td>
			<td><input type="text" name="kiddies" size="2" maxlength="2" value="<?php echo $kiddies_html ?>"></td>
		</tr>
		<tr>
			<td>Kirche:</td>
			<td><input id="kirche0" type="radio" name="kirche" value="1"<?php echo $kirche_html[0] ?>>&nbsp;<label for="kirche0">Ja</label> <input id="kirche1" type="radio" name="kirche" value="0"<?php echo $kirche_html[1] ?>>&nbsp;<label for="kirche1">Nein</label></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="Berechne mir den Shice!"></td>
		</tr>
	</table>
</form>
	
<?php else: ?>

<script language="php">
	// HTML Ausgabe
	$lohnsteuer_html = str_replace('.', ',', $lohnsteuer);
	$rv_html = str_replace('.', ',', $rv);
	$kv_html = str_replace('.', ',', $kv);
	$av_html = str_replace('.', ',', $av);
	$pv_html = str_replace('.', ',', $pv);
	$ks_html = str_replace('.', ',', $ks);
	$sz_html = str_replace('.', ',', $sz);
	$sz_html = str_replace('.', ',', $sz);
	$steuerwert_html = str_replace('.', ',', $steuerwert);
	$absetzen_html = str_replace('.', ',', $absetzen);
	$netto_html = str_replace('.', ',', round(($netto / 12), 2));
	$staat_html = str_replace('.', ',', round(($brutto - $netto), 2));
</script>

<h2>Berechnete Abzüge:</h2>
<ul>
	<li>Lohnsteuer: <?php echo $lohnsteuer_html ?> €</li>
	<li>Rentenversicherung: <?php echo $rv_html ?> €</li>
	<li>Krankenversicherung: <?php echo $kv_html ?> €</li>
	<li>Arbeitslosenversicherung: <?php echo $av_html ?> €</li>
	<li>Pflegeversicherung: <?php echo $pv_html ?> €</li>
	<li>Kirchensteuer: <?php echo $ks_html ?> €</li>
	<li>Solidaritätszuschlag: <?php echo $sz_html ?> €</li>
</ul>
<h2>Weitere Informationen:</h2>
<p>Du kannst bis zu <?php echo $absetzen_html ?> € steuerlich geltend machen,<br>
somit beträgt dein zu versteuerndes Einkommen effektiv <?php echo $steuerwert_html ?> €</p>
<p>Abzüglich aller Steuern und Beiträge bleiben dir lächerliche <?php echo $netto_html ?> € im Monat zum Leben,<br>
andere jedoch verdienen <?php echo $staat_html ?> € im Jahr, einfach weil es dich gibt!</p>
<p><br>
<input type="button" onclick="window.location.href='<?php echo $_SERVER['SCRIPT_NAME'] ?>'" value="Neue Abfrage"></p>
	
<?php endif; ?>

<p style="font-size:smaller;"><br>
Copyright &copy;2007-2009, Bachsau IT<br>
<a href="<?php echo $_SERVER['SCRIPT_NAME'] ?>?source">Quelltext anzeigen</a></p>
</body>
</html>
