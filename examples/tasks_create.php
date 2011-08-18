#!/usr/bin/php
<?php
/**
 * @author Jonathan Oxer <jon.oxer@ivt.com.au>
 * @version 2011-08-08
 *
 * This example is designed to be run from the command line using
 * PHP-CLI, and is a minimal example of using the php-liquidplanner
 * library to create a new task in your workspace. You must configure
 * it to use your own workspace ID, email address, and Liquid Planner
 * password, and set a parent_id value of one of your existing
 * projects or project folders.
 */
require_once '../liquidplanner.php';

$task['name']        = "My LP ticket";
$task['parent_id']   = 123456;
$task['description'] = "Description of my LP ticket";

/* Create an instance of the Liquid Planner object */
$lp = new LiquidPlanner("12345", "you@example.com", "yourLPpassword");

/* Create a new task in Liquid Planner */
$response = $lp->tasks_create($task);
echo "ID of the new task: ".$response['id']."\n";

exit;
