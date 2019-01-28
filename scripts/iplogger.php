<?php
###################################################################################
##     IP Logging Script  (PHP + MYSQL)                                          ##
##     version 2.0                                                               ##
##     by Ben Christopher Tomlin                                                 ##
##     Copyright 2010                                                            ##
##     http://folk.uio.no/benct                                                  ##
###################################################################################
## This program is free software; you can redistribute it and/or modify it under ##
## the terms of the GNU General Public License as published by the Free Software ##
## Foundation (version 2 or later).                                              ##
##                                                                               ##
## This program is distributed in the hope that it is and will be useful, but    ##
## WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY  ##
## or FITNESS FOR A PARTICULAR PURPOSE.                                          ##
###################################################################################
##  SETUP GUIDE:                                                                 ##
##  Before you start using this script, run the setup function contained here.   ##
##  This is done by creating a new php file which contains this line of code:    ##
##  < ?php require("iplogger.php"); iplog_setup(); ? > This function creates a   ##
##  table in your MYSQL database for storing the IPs it logs. Make sure you've   ##
##  set the variables below to the correct values first!                         ##
##                                                                               ##
##  USAGE:                                                                       ##
##  Anywhere on the page where you want to log your visitors, insert this line   ##
##  of code: < ?php require("iplogger.php"); iplog_run(); ? >                    ##
##                                                                               ##
##  DISPLAY THE FINDINGS:                                                        ##
##  To print the log of registered IPs with hostname, date, time and count,      ##
##  insert the following line of code where you want it displayed:               ##
##  < ?php require("iplogger.php"); iplog_display(); ? >                         ##
###################################################################################

// DATABASE VARIABLES
$db_host = "localhost";
$db_user = "username";
$db_pass = "password";

$db_name = "database_name";
$db_table = "database_table";

$date_format = "d.m.y H:i:s";

function iplog_setup() {

	global $db_host, $db_user, $db_pass, $db_name, $db_table;

	$con = mysql_connect($db_host, $db_user, $db_pass) or die('Could not connect: ' . mysql_error());
	mysql_select_db($db_name) or die(mysql_error());

	mysql_query('CREATE TABLE ' . $db_table . ' (
				ID_IP int NOT NULL AUTO_INCREMENT,
				ip varchar(32) NOT NULL,
				hostname varchar(255) NOT NULL,
				referer varchar(255),
				count int NOT NULL,
				date varchar(24) NOT NULL,
				PRIMARY KEY (ID_IP)
				)') or die(mysql_error());

	echo 'Setup completed successfully.';

	mysql_close($con);
}

function iplog_run() {

	global $db_host, $db_user, $db_pass, $db_name, $db_table, $date_format;

	$visitor_ip = $_SERVER['REMOTE_ADDR'];
	$hostname = gethostbyaddr($visitor_ip);
	$referer = $_SERVER['HTTP_REFERER'];
	$date = date($date_format);

	$con = mysql_connect($db_host, $db_user, $db_pass) or die('Could not connect: ' . mysql_error());
	mysql_select_db($db_name) or die(mysql_error());

	$result = mysql_query('SELECT ip, count FROM ' . $db_table . ' WHERE ip = "' . $visitor_ip . '"') or die(mysql_error());

	if (mysql_num_rows($result) == 0) {
		mysql_query('INSERT INTO ' . $db_table . ' (ip, hostname, referer, count, date)
					VALUES ("' . $visitor_ip . '", "' . $hostname . '", "' . $referer . '", "1", "' . $date . '")') or die(mysql_error());
	} else {
		$row = mysql_fetch_array($result);
		$count = ($row['count'] + 1);
		mysql_query('UPDATE ' . $db_table . ' SET hostname = "' . $hostname . '", referer = "' . $referer . '", count = "' . $count . '", date = "' . $date . '"
					WHERE ip = "' . $visitor_ip . '"') or die(mysql_error());
	}

	mysql_close($con);
}

function iplog_display() {

	global $db_host, $db_user, $db_pass, $db_name, $db_table;

	$con = mysql_connect($db_host, $db_user, $db_pass) or die('Could not connect: ' . mysql_error());
	mysql_select_db($db_name) or die(mysql_error());

	$result = mysql_query('SELECT * FROM ' . $db_table . '') or die(mysql_error());

	echo '		<table style="border:1px dashed black;" cellpadding="3" rules="all">
		<tr><th>Date</th><th>Count</th><th>IP Address</th><th>Hostname</th><th>Referer</th></tr>
		';
	while ($row = mysql_fetch_array($result)) {
		echo '<tr><td nowrap>', $row['date'], '</td><td>', $row['count'], '</td><td>', $row['ip'], '</td><td nowrap>', $row['hostname'], '</td><td nowrap>', $row['referer'], '</td></tr>
		';
	}
	echo '</table>';

	mysql_close($con);
}
