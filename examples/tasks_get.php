#!/usr/bin/php
<?php
/**
 * @author Mark Rickert <mjar81@gmail.com>
 * @version 2012-06-21
 *
 * This example is designed to be run from the command line using
 * PHP-CLI, and is a minimal example of using the php-liquidplanner
 * library to create a new task in your workspace. You must configure
 * it to use your own workspace ID, email address, and Liquid Planner
 * password, and set a parent_id value of one of your existing
 * projects or project folders.
 */
require_once '../liquidplanner.php';

/* Create an instance of the Liquid Planner object */
$lp = new LiquidPlanner("12345", "you@example.com", "yourLPpassword");

/*
	You can see what params are supported in the API guide
	in the section: "Filtering Items"
	http://www.liquidplanner.com/storage/help/liquidplanner_API.pdf
*/
$params = array(
	'from_date' => date("m/d/Y", strtotime('-2 weeks')),
	'to_date' => date("m/d/Y", strtotime('today')),
	'limit' => 10,
	'filter' => array('is_done is false')
);

/* Get specified tasks Liquid Planner */
$response = $lp->tasks(NULL, $params);

var_dump($response);

exit;
