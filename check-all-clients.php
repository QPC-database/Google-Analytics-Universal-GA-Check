<?php

if (php_sapi_name() == "cli") {
    $eol = "\n";
	$cli = true;
} else {
    $eol = "<br />\n";
	$cli = false;
}


$list = readFileToArray("clientlist");
if ($cli) echo "Running Universal GA Check on client list" . $eol . ". = good site   x = outdated site" . $eol;
else echo "<h1>Universal GA Check</h1>";

$good = array();
$bad = array();

foreach ($list as $line) {
	if (checkUniversalGa($line)) {
		$good[] = $line;
		if ($cli) echo ".";
	} else {
		$bad[] = $line;
		if ($cli) echo "x";
	}
}

if ($cli) {
	echo $eol . $eol;
	echo "Results:" . $eol;
	echo "Sites with proper code: " . count($good) . $eol;
	echo "Sites with outdated code: " . count($bad) . $eol . $eol;
} else {
	echo "Results:" . $eol;
	echo "Sites with proper code: " . count($good) . $eol;
	echo "Sites with outdated code: " . count($bad) . $eol . $eol;

	echo "<h2>Sites with outdated GA code</h2><ul>";
	foreach($bad as $line) {
		echo "<li style=\"color:red\">" . $line . "	</li>" . $eol;
	}
	echo "</ul>";

	echo "<h2>Sites with good GA code</h2><ul>";
	foreach($good as $line) {
		echo "<li style=\"color:green\"> " . $line . "</li>" . $eol;
	}
	echo "</ul>";

}


// Write output to files
writeOutputToFile($good, $bad);


/*****************
    Functions
*****************/

function checkUniversalGa($url) {
	$pageContent = get_data($url);

	$good[] = "www.google-analytics.com/analytics.js";
	$good[] = "ga('create',";

	$bad[] = "google-analytics.com/ga.js";
	$bad[] = "_gaq.push(['_setAccount'";

	//set to true to use first search method
	if (false) {

	if (contains($bad[0], $pageContent)) {
		return false;
		//echo "FALSE: $pageContent";
	} else if (contains($good[0], $pageContent)) {
		return true;
		//echo "TRUE: $pageContent";
	}
	return false;

	}

	foreach($bad as $badLine) {
		if (contains($badLine, $pageContent)) {
			return false;
		}
	}

	foreach($good as $goodLine) {
		if (contains($goodLine, $pageContent)) {
			return true;
		}
	}
	return true;
//	return false;
}


function writeOutputToFile($good, $bad, $filename = "results.txt") {
	$text = "Sites with proper code: " . count($good) . "\n";
	$text .= "Sites with outdated code: " . count($bad) . "\n\n";


	$text .= "*****Clients Without Proper GA Code *****" . "\n";
	foreach($bad as $key) {
    	$text .= $key . "\n";
	}

	$text .= "*****Clients With Proper GA Code *****" . "\n";
	foreach($good as $key) {
    	$text .= $key . "\n";
	}

	$fh = fopen($filename, "w") or die("Could not open log file.");
	fwrite($fh, $text) or die("Could not write file!");
	fclose($fh);

}

function contains($needle, $haystack)
{
    return (strpos($haystack, $needle) !== false);
}

function get_data($url) {
	$ch = curl_init();
	$timeout = 10;

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

	$data = curl_exec($ch);
	curl_close($ch);

	return $data;
}


function readFileToArray($filename) {
	global $eol;

	$outputArray = array();


	$file=file($filename);
	// To check the number of lines
	foreach($file as $line) {
		$outputArray[] = trim($line);
	}

	return array_unique($outputArray);

}

?>
