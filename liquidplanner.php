<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Library to provide simple access to the Liquid Planner API
 *
 * The public methods are structured to mimic the API routes published
 * by Liquid Planner at https://app.liquidplanner.com/api/help/urls.
 * Method names follow the route sequence with the common leading
 * elements generalised away, so for example this API route:
 *     api/workspaces/:workspace_id/tasks/:id/track_time
 * is exposed as a public method called:
 *     tasks_track_time()
 *
 * Convenience methods for "create" and "delete" are also provided
 * even though they are implied in the API routes and not explicitly
 * named, so for example to create or delete tasks you can simply call:
 *     tasks_create()
 *     tasks_delete()
 *
 * @author     Jonathan Oxer <jon.oxer@ivt.com.au>
 * @copyright  2011 Internet Vision Technologies <www.ivt.com.au>
 * @version    2011-08-08
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
     * Deletes a comment on a client from Liquid Planner
     *
     * Pass the ID of a comment in Liquid Planner into this method and it
     * will be deleted from the workspace. The raw response from the
     * web service is returned so you can examine the result.
     *
     * @param  int     $clientId  the ID of the client in Liquid Planner
     * @param  int     $commentId the ID of the comment in Liquid Planner
     *
     * @return string  raw response from the API
     *
     * @access public
     */
    public function clients_comments_delete($clientId, $commentId)
    {
        $url = $this->serviceurl.'/clients/'.$clientId.'/comments/'.$commentId;
        $response = $this->lp_delete($url);
        return($response);
    }

    /**
     * Creates a new task in Liquid Planner
     *
     * @param  array  $data   values to apply to the newly created task
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
    public function tasks_create(array $data)
    {
        $encodedTask = json_encode(array('task' => $data));
        $url = $this->serviceurl.'/tasks';
        $response = $this->lp_post($url, $encodedTask);
        return($response);
    }

    /**
     * Deletes a task from Liquid Planner
     *
     * Pass the ID of a task in Liquid Planner into this method and it
     * will be deleted from the workspace. The raw response from the
     * web service is returned so you can examine the result.
     *
     * @param  int     $id the ID of the task in Liquid Planner
     *
     * @return string  raw response from the API
     *
     * @access public
     */
    public function tasks_delete($id)
    {
        $url = $this->serviceurl.'/tasks/'.$id;
        $response = $this->lp_delete($url);
        return($response);
    }

    /**
     * Updates task time values, such as work completed and estimates
     *
     * @param  array  $data   values to apply to the specified task
     * @param  int    $taskid ID of Liquid Planner task to update
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
    public function tasks_track_time(array $data, $taskid)
    {
        $encodedTask = json_encode($data);
        $url = $this->serviceurl.'/tasks/'.$taskid.'/track_time';
        $response = $this->lp_post($url, $encodedTask);
        return($response);
    }

    /**
     * Creates a new comment on a task in Liquid Planner
     *
     * @param  array  $data   values to apply to the newly created comment
     * @param  int    $taskid ID of Liquid Planner task to link to comment
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
    public function tasks_comments_create(array $data, $taskid)
    {
        $encodedData = json_encode(array('comment' => $data));
        $url = $this->serviceurl.'/tasks/'.$taskid.'/comments';
        $response = $this->lp_post($url, $encodedData);
        return($response);
    }
	
	/**
     * Gets a list of clients from Liquid Planner
     *
     * @param  int    $clientid ID of Liquid Planner client to get 
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
	function clients($clientid=NULL)
    { 
		$url = $this->serviceurl.'/clients'.($clientid ? '/'.$clientid : '');
		return $this->lp_get($url);
	}

	/**
     * Gets a list of comments on a client from Liquid Planner
     *
     * @param  int    $clientid ID of Liquid Planner client to get comments from 
     * @param  int    $commentid ID of Liquid Planner client comment to get 
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
    function clients_comments($clientid=NULL, $commentid=NULL)
    { 
		$url = $this->serviceurl.'/clients/'.$clientid.'/comments'.($commentid ? '/'.$commentid : '');
		echo $url;
		return $this->lp_get($url);
	}

/**************************************************************/

    function activities(array $data, $id=NULL)
    { return array("Not yet implemented"); }

    function clients_dependencies(array $data, $id=NULL)
    { return array("Not yet implemented"); }

/**************************************************************/

    /**
     * Send data to the Liquid Planner API as a POST method with a
     * JSON-encoded payload
     */
    private function lp_post($url, $encodedTask)
    {
        /* Set up the CURL object and execute it */
        $conn = curl_init();
        curl_setopt($conn, CURLOPT_HEADER, false);                                       // Suppress display of the response header
        curl_setopt($conn, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); // Must submit as JSON
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);                                // Return result as a string
        curl_setopt($conn, CURLOPT_POST, true);                                          // Submit data as an HTTP POST
        curl_setopt($conn, CURLOPT_POSTFIELDS, $encodedTask);                            // Set the POST field values
        curl_setopt($conn, CURLOPT_ENCODING, "");                                        // Prevent GZIP compression of response from LP
        curl_setopt($conn, CURLOPT_USERPWD, $this->email.":".$this->password);           // Authenticate
        curl_setopt($conn, CURLOPT_URL, $url);                                           // Set the service URL
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);                               // Accept any SSL certificate
        $response = curl_exec($conn);
        curl_close($conn);

        /* The response is JSON, so decode it and return the result as an array */
        return(json_decode($response, true));
    }
	
	 /**
     * Send data to the Liquid Planner API as a GET method
     */
	private function lp_get($url)
    {
        /* Set up the CURL object and execute it */
        $conn = curl_init();
        curl_setopt($conn, CURLOPT_HEADER, false);                                       // Suppress display of the response header
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);                                // Return result as a string
        curl_setopt($conn, CURLOPT_ENCODING, "");                                        // Prevent GZIP compression of response from LP
        curl_setopt($conn, CURLOPT_USERPWD, $this->email.":".$this->password);           // Authenticate
        curl_setopt($conn, CURLOPT_URL, $url);                                           // Set the service URL
        $response = curl_exec($conn);
        curl_close($conn);

        /* The response is JSON, so decode it and return the result as an array */
        return(json_decode($response, true));
    }

    /**
     * Send data to the Liquid Planner API as a DELETE method
     */
    private function lp_delete($url)
    {
        /* Set up the CURL object and execute it */
        $conn = curl_init();
        curl_setopt($conn, CURLOPT_HEADER, false);                                       // Suppress display of the response header
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);                                // Return result as a string
        curl_setopt($conn, CURLOPT_CUSTOMREQUEST, "DELETE");                             // Connect as an HTTP DELETE
        curl_setopt($conn, CURLOPT_ENCODING, "");                                        // Prevent GZIP compression of response from LP
        curl_setopt($conn, CURLOPT_USERPWD, $this->email.":".$this->password);           // Authenticate
        curl_setopt($conn, CURLOPT_URL, $url);                                           // Set the service URL
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);                               // Accept any SSL certificate
        $response = curl_exec($conn);
        curl_close($conn);

        return($response);
    }
}
