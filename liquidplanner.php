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
	public function create_task(array $data)
	{
		$encodedTask = json_encode(array('task' => $data));
		$url = $this->serviceurl.'/tasks';
		$response = $this->lp_post($url, $encodedTask);
		return($response);
	}

	/**
	 * Updates the low and high time estimates for a specifed task.
	 * NOTE: This method currently does not work. Do not use!
	 *
	 * @param array $data Values to apply to the specified task
	 *  - 'low': low estimated time (float)
	 *  - 'high': high estimated time (float)
	 * @param int $taskid ID of Liquid Planner task to update
	 * @return array Response from Liquid Planner
	 */
	public function estimate(array $data, $id)
	{
		$encodedTask = json_encode($data);
		$url = $this->serviceurl.'/treeitems/'.$id.'/estimates';
		$response = $this->lp_post($url, $encodedTask);
		return($response);
	}

	/**
	 * Updates the time values (both work completed and estimates)
	 * of tasks.
	 * @param array $data Values to apply to the specified task
	 * @param int $taskid ID of Liquid Planner task to update
	 * @return array Response from Liquid Planner
	 */
	public function tasks_track_time(array $data, $id)
	{
		$encodedTask = json_encode($data);
		$url = $this->serviceurl.'/tasks/'.$id.'/track_time';
		$response = $this->lp_post($url, $encodedTask);
		return($response);
	}


	function tasks_delete($id)
	{
		//$encodedTask = json_encode($data);
		$url = $this->serviceurl.'/tasks/'.$id;
		//$response = $this->lp_delete($url, $encodedTask);
		$response = $this->lp_delete($url);
		return($response);
	}

/**************************************************************/

	function activities(array $data, $id=NULL)
	{ return array("Not yet implemented"); }

	function clients(array $data)
	{ return array("Not yet implemented"); }

	function clients_comments(array $data, $id=NULL)
	{ return array("Not yet implemented"); }

	function clients_dependencies(array $data, $id=NULL)
	{ return array("Not yet implemented"); }

/**************************************************************/

	/**
	 *
	 */
	private function lp_post($url, $encodedTask)
	{
		//print_r($encodedTask);
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

	/**
	 *
	 */
	private function lp_delete($url)
	{
		/* Set up the CURL object and execute it */
		$conn = curl_init();
		curl_setopt($conn, CURLOPT_HEADER, FALSE);                                       // Suppress display of the response header
		curl_setopt($conn, CURLOPT_RETURNTRANSFER, TRUE);                                // Return result as a string
		curl_setopt($conn, CURLOPT_CUSTOMREQUEST, "DELETE");                             // Connect as an HTTP DELETE
		curl_setopt($conn, CURLOPT_ENCODING, "");                                        // Prevent GZIP compression of response from LP
		curl_setopt($conn, CURLOPT_USERPWD, $this->email.":".$this->password);           // Authenticate
		curl_setopt($conn, CURLOPT_URL, $url);                                           // Set the service URL
		$response = curl_exec($conn);
		curl_close($conn);

		return($response);
	}
}
