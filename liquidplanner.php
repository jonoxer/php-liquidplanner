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
		$this->serviceurl = "https://app.liquidplanner.com/api/workspaces/" . $workspaceID;
	}
	
	/**
	 * $ticket['name']
	 * description
	 * parent_id
	 */
	public function create_task(array $task)
	{
		$encoded_task = json_encode(
			array('task' =>
				array(
					'name'        => $task['name'],
					'description' => $task['description'],
					'parent_id'   => $task['parent_id']
				)
			)
		);

		/* Set up the CURL object and execute it */
		$conn = curl_init();
		curl_setopt($conn, CURLOPT_HEADER, FALSE);                                       // Suppress display of the response header
		curl_setopt($conn, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); // Must submit as JSON
		curl_setopt($conn, CURLOPT_RETURNTRANSFER, TRUE);                                // Return result as a string
		curl_setopt($conn, CURLOPT_POST, TRUE);                                          // Submit data as an HTTP POST
		curl_setopt($conn, CURLOPT_POSTFIELDS, $encoded_task);                           // Set the POST field values
		curl_setopt($conn, CURLOPT_ENCODING, "");                                        // Prevent GZIP compression of response from LP
		curl_setopt($conn, CURLOPT_USERPWD, $this->email.":".$this->password);           // Authenticate
		curl_setopt($conn, CURLOPT_URL, $this->serviceurl . '/tasks');                   // Set the service URL
		$response = curl_exec($conn);
		curl_close($conn);

		/* The response is JSON, so decode it and return the result as an array */
		return(json_decode($response, TRUE));
	}

	/**
	 */
	public function update_estimate($taskid, array $data)
	{
		$encoded_task = json_encode($data);

		/* Set up the CURL object and execute it */
		$conn = curl_init();
		curl_setopt($conn, CURLOPT_HEADER, FALSE);                                       // Suppress display of the response header
		curl_setopt($conn, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); // Must submit as JSON
		curl_setopt($conn, CURLOPT_RETURNTRANSFER, TRUE);                                // Return result as a string
		curl_setopt($conn, CURLOPT_POST, TRUE);                                          // Submit data as an HTTP POST
		curl_setopt($conn, CURLOPT_POSTFIELDS, $encoded_task);                           // Set the POST field values
		curl_setopt($conn, CURLOPT_ENCODING, "");                                        // Prevent GZIP compression of response from LP
		curl_setopt($conn, CURLOPT_USERPWD, $this->email.":".$this->password);           // Authenticate
		curl_setopt($conn, CURLOPT_URL, $this->serviceurl.'/tasks/'.$taskid.'/track_time'); // Set the service URL
		$response = curl_exec($conn);
		curl_close($conn);

		/* The response is JSON, so decode it and return the result as an array */
		return(json_decode($response, TRUE));
	}
}
