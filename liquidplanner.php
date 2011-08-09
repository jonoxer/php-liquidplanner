<?php
/**
 * @author Jonathan Oxer <jon.oxer@ivt.com.au>
 * @version 2011-08-08
 */

class LiquidPlanner
{

	private $email = '';
	private $password = '';
	private $serviceurl = '';

	/**
	 * Constructor
	 */
	public function __construct($workspaceID, $email, $password)
	{
		$this->email      = $email;
		$this->password   = $password;
		$this->serviceurl = "https://app.liquidplanner.com/api/workspaces/".$workspaceID;
	}
	
	/**
	 * $ticket['name']
	 * description
	 * parent_id
	 */
	public function create_task(array $task)
	{
		$encodedTask = json_encode(
			array('task' =>
				array(
					'name'        => $task['name'],
					'description' => $task['description'],
					'parent_id'   => $task['parent_id']
				)
			)
		);
		$url = $this->serviceurl.'/tasks';
		$response = $this->execute_connection($url, $encodedTask);
		return($response);
	}

	/**
	 * Updates the low and high time estimates for a specifed task.
	 *
	 * @param int $taskid ID of Liquid Planner task to update
	 * @param array $data Values to apply to the specified task
	 *  - 'low': low estimated time (float)
	 *  - 'high': high estimated time (float)
	 * @return array Response from Liquid Planner
	 */
	public function estimate($taskid, array $data)
	{
		$encodedTask = json_encode($data);
		$url = $this->serviceurl.'/treeitems/'.$taskid.'/estimates';
		$response = $this->execute_connection($url, $encodedTask);
		return($response);
	}

	/**
	 * Updates the time values (both work completed and estimates)
	 * of tasks.
	 * @param int $taskid ID of Liquid Planner task to update
	 * @param array $data Values to apply to the specified task
	 *  - 'member_id': ID of member to track work against. Defaults to
	       current user (int) (optional)
	 *  - 'work': Hours worked (float) (optional)
	 *  - 'low': low estimated time (float) (optional)
	 *  - 'high': high estimated time (float) (optional)
	 * @return array Response from Liquid Planner
	 */
	public function track_time($taskid, array $data)
	{
		$encodedTask = json_encode($data);
		$url = $this->serviceurl.'/tasks/'.$taskid.'/track_time';
		$response = $this->execute_connection($url, $encodedTask);
		return($response);
	}

	/**
	 *
	 */
	private function execute_connection($url, $encodedTask)
	{
		/* Set up the CURL object and execute it */
		$conn = curl_init();
		curl_setopt($conn, CURLOPT_HEADER, FALSE);                                       // Suppress display of the response header
		curl_setopt($conn, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); // Must submit as JSON
		curl_setopt($conn, CURLOPT_RETURNTRANSFER, TRUE);                                // Return result as a string
		curl_setopt($conn, CURLOPT_POST, TRUE);                                          // Submit data as an HTTP POST
		curl_setopt($conn, CURLOPT_POSTFIELDS, $encodedTask);                            // Set the POST field values
		curl_setopt($conn, CURLOPT_ENCODING, "");                                        // Prevent GZIP compression of response from LP
		curl_setopt($conn, CURLOPT_USERPWD, $this->email.":".$this->password);           // Authenticate
		curl_setopt($conn, CURLOPT_URL, $url);                                           // Set the service URL
		$response = curl_exec($conn);
		curl_close($conn);

		/* The response is JSON, so decode it and return the result as an array */
		return(json_decode($response, TRUE));
	}
}
