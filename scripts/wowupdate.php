<?php
/*******************************************************************************
* wowoupdate.php                                                               *
********************************************************************************
* Parses the data grabbed from the World of Warcraft Armory and adds it to a   *
* specified MySQL database.                                                    *
* ============================================================================ *
* Version:                    2.0                                              *
* Software by:                Ben Tomlin                                       *
* Support, News, Updates at:  http://tomlin.no                                 *
********************************************************************************
* This program is free software, you may redistribute it and/or modify it as   *
* you wish. A reference to the author is appreciated though.                   *
*                                                                              *
* This program is distributed in the hope that it is and will be useful, but   *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY *
* or FITNESS FOR A PARTICULAR PURPOSE.                                         *
********************************************************************************
*    Usage/instructions:                                                       *
* Before using this script, you must run the setup function (ONLY ONCE)        *
* (Open link in browser) http://www.yoursite.com/wowupdate.php?setup           *
*                                                                              *
* Adding or removing characters are done by editing the list a bit further     *
* down in the script (the $characters array). Please follow the correct syntax *
* when updating the list, or else the script will not run.                     *
*                                                                              *
* You are then ready to run the update, whenever you want to do this           *
* (Open link in browser) http://www.yoursite.com/wowupdate.php                 *
*                                                                              *
* To display the data, you would want to write your own function/script to get *
* from the data from the database and display in in a proper way.              *
*******************************************************************************/

// Realm
define('REALM', 'Draenor');

// Database Settings
define('DB_HOST', 'host');
define('DB_USER', 'user');
define('DB_PASS', 'password');
define('DB_NAME', 'database');
define('DB_TABLE', 'tablename');

// Action to perform
if (isset($_GET['setup']))
	define('SETUP', 1);
else
	define('UPDATE', 1);

// Characters we will display
$characters = array(
    "name1",
    "name2",
    "name3",
);

// Race code with corresponding names
$races = array(
    1 => "Human",
    2 => "Orc",
    3 => "Dwarf",
    4 => "Night Elf",
    5 => "Undead",
    6 => "Tauren",
    7 => "Gnome",
    8 => "Troll",
    9 => "Goblin",
    10 => "Blood Elf",
    11 => "Draenei",
    22 => "Worgen",
);

// Class code with corresponding name and color
$classes = array(
    1 => array("name" => "Warrior", "color" => "C79C6E", "total" => 0),
    2 => array("name" => "Paladin", "color" => "F58CBA", "total" => 0),
    3 => array("name" => "Hunter", "color" => "ABD473", "total" => 0),
    4 => array("name" => "Rogue", "color" => "FFF569", "total" => 0),
    5 => array("name" => "Priest", "color" => "FFFFFF", "total" => 0),
    6 => array("name" => "Death Knight", "color" => "C41F3B", "total" => 0),
    7 => array("name" => "Shaman", "color" => "0070DE", "total" => 0),
    8 => array("name" => "Mage", "color" => "69CCF0", "total" => 0),
    9 => array("name" => "Warlock", "color" => "9482C9", "total" => 0),
    11 => array("name" => "Druid", "color" => "FF7D0A", "total" => 0),
);

// Connect to database
mysql_connect(DB_HOST, DB_USER, DB_PASS) or die('Could not connect: ' . mysql_error());
mysql_select_db(DB_NAME) or die(mysql_error());

// Initialize PHP Curl
if (defined('UPDATE')) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.2) Gecko/20070319 Firefox/2.0.0.3");
}

// Function to get a character using the API
function getCharAPI($name)
{
    global $ch;

    $url = 'eu.battle.net/api/wow/character/' . REALM . '/' . $name . '?fields=guild,talents,professions,stats,titles';
    
    curl_setopt($ch, CURLOPT_URL, $url);
    $string = curl_exec($ch);
    $array = json_decode($string, true);

    return $array;
}

// Function to get a character from the database
function getCharDB($name)
{
	$result = mysql_query('SELECT * FROM ' . DB_TABLE . ' WHERE name = "' . $name . '"') or die(mysql_error());;
	
	if (mysql_num_rows($result) != 1)
		return false;
	
	return mysql_fetch_array($result);
}

// Function to add a character to the database
function addChar($name)
{
	mysql_query('INSERT INTO ' . DB_TABLE . ' (name) VALUES ("' . $name . '")') or die(mysql_error());;
}

// Function to update character in the database
function updateChar($data)
{
	// If character does not exist in database, add it.
	$result = mysql_query('SELECT * FROM ' . DB_TABLE . ' WHERE name="' . utf8_decode($data['name']) . '"') or die(mysql_error());
	if (mysql_num_rows($result) == 0)
		addChar(utf8_decode($data['name']));
	
	// Get selected title if any
	foreach($data['titles'] as $tits) {
		if (isset($tits['selected']))
			$title = $tits['name'];
	}
	if (!isset($title))
		$title = "%s";
	
	// Set selected talent spec
	$selected = isset($data['talents'][0]['selected']) ? 1 : 2;

	// Run the update query
	$result = mysql_query('UPDATE ' . DB_TABLE . ' SET 
	
	title = "' . $title . '",

	gender = ' . $data['gender'] . ', 
	race = ' . $data['race'] . ', 
	class = ' . $data['class'] . ', 
	level = ' . $data['level'] . ', 
	
	guildName = "' . $data['guild']['name'] . '", 
	realm = "' . $data['realm'] . '", 
	
	health = ' . $data['stats']['health'] . ', 
	power = ' . $data['stats']['power'] . ', 
	type = "' . $data['stats']['powerType'] . '", 

	prof1 = "' . $data['professions']['primary'][0]['name'] . '", 
	p1val = ' . $data['professions']['primary'][0]['rank'] . ', 
	prof2 = "' . $data['professions']['primary'][1]['name'] . '", 
	p2val = ' . $data['professions']['primary'][1]['rank'] . ', 
	
	spec1 = "' . $data['talents'][0]['name'] . '", 
	s1t1 = ' . $data['talents'][0]['trees'][0]['total'] . ', 
	s1t2 = ' . $data['talents'][0]['trees'][1]['total'] . ', 
	s1t3 = ' . $data['talents'][0]['trees'][2]['total'] . ', 
	spec2 = "' . $data['talents'][1]['name'] . '", 
	s2t1 = ' . $data['talents'][1]['trees'][0]['total'] . ', 
	s2t2 = ' . $data['talents'][1]['trees'][1]['total'] . ', 
	s2t3 = ' . $data['talents'][1]['trees'][2]['total'] . ', 
	selected = ' . $selected . ', 
	
	armor = ' . $data['stats']['armor'] . ', 
	strength = ' . $data['stats']['str'] . ', 
	agility = ' . $data['stats']['agi'] . ', 
	stamina = ' . $data['stats']['sta'] . ', 
	intellect = ' . $data['stats']['int'] . ', 
	spirit = ' . $data['stats']['spr'] . ', 
	
	apoints = ' . $data['achievementPoints'] . ',
	mpower = ' . $data['stats']['attackPower'] . ', 
	rpower = ' . $data['stats']['rangedAttackPower'] . ', 
	spower = ' . $data['stats']['spellPower'] . ', 
	
	lastModified = ' . $data['lastModified'] . ',
	thumbnail = "' . $data['thumbnail'] . '"
	
	WHERE name = "' . utf8_decode($data['name']) . '"') or die(mysql_error());
	
	return true;
}

// Function to setup the database table
function setupChar()
{
	mysql_query('CREATE TABLE ' . DB_TABLE . ' (

	m_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(32) NOT NULL DEFAULT "Unknown",
	title VARCHAR(64) NOT NULL DEFAULT "",

	gender INT NOT NULL DEFAULT 0,
	race INT NOT NULL DEFAULT 0,
	class INT NOT NULL DEFAULT 0,
	level INT NOT NULL DEFAULT 0,

	guildName VARCHAR(50) NOT NULL DEFAULT "",
	realm VARCHAR(30) NOT NULL DEFAULT "",

	health INT NOT NULL DEFAULT 0,
	power INT NOT NULL DEFAULT 0,
	type VARCHAR(12) NOT NULL DEFAULT "mana",

	prof1 VARCHAR(20) NOT NULL DEFAULT "",
	p1val INT NOT NULL DEFAULT 0,
	prof2 VARCHAR(20) NOT NULL DEFAULT "",
	p2val INT NOT NULL DEFAULT 0,

	spec1 VARCHAR(20) NOT NULL DEFAULT "",
	s1t1 INT NOT NULL DEFAULT 0,
	s1t2 INT NOT NULL DEFAULT 0,
	s1t3 INT NOT NULL DEFAULT 0,
	spec2 VARCHAR(20) NOT NULL DEFAULT "",
	s2t1 INT NOT NULL DEFAULT 0,
	s2t2 INT NOT NULL DEFAULT 0,
	s2t3 INT NOT NULL DEFAULT 0,
	selected INT NOT NULL DEFAULT 0,

	armor INT NOT NULL DEFAULT 0,
	strength INT NOT NULL DEFAULT 0,
	agility INT NOT NULL DEFAULT 0,
	stamina INT NOT NULL DEFAULT 0,
	intellect INT NOT NULL DEFAULT 0,
	spirit INT NOT NULL DEFAULT 0,

	apoints INT NOT NULL DEFAULT 0,
	mpower INT NOT NULL DEFAULT 0,
	rpower INT NOT NULL DEFAULT 0,
	spower INT NOT NULL DEFAULT 0,

	lastModified BIGINT NOT NULL DEFAULT 0,
	thumbnail VARCHAR(64) NOT NULL DEFAULT "",
	color VARCHAR(16),
	
	UNIQUE (name)
	)');
}

// Update all specified characters...
if (defined('UPDATE')) {
	
	foreach($characters as $char) {
		// Get using API
		$chardata = getCharAPI($char);
		if (isset($chardata['status'])) {
			echo 'Could not update \'' . $char . '\'. Reason: ' . $chardata['reason'] . '<br/>';
			continue;
		}
		if ($chardata == NULL) {
			echo 'Could not update \'' . $char . '\'. Reason: API request failed.<br/>';
			continue;
		}
		// Update
		$retval = updateChar($chardata);
		if (!$retval)
			echo 'Could not update \'' . $char . '\'.<br/>';
	}
	echo 'Update Complete!';
	
	// Close curl resource
	curl_close($ch);
	
	// Stop the script here
	exit;
}

// ...or setup database table (ONLY DO THIS ONCE)
if (defined('SETUP')) {
	setupChar();
	echo 'Setup Complete!';
	exit;
}
