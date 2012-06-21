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
    private $throttlewait = 15;
    public  $debug = false;

    /**
     * Constructor
     */
    public function __construct($workspaceID, $email, $password)
    {
        $this->email      = $email;
        $this->password   = $password;
        $this->baseurl	  = "https://app.liquidplanner.com/api";
        $this->serviceurl = $this->baseurl . "/workspaces/".$workspaceID;
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
     * Retrieves the specified task or a list of all tasks
     *
     * @param  int    $taskid ID of task.
     * @param  array  Parameters to send such as date and count limiters.
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
    public function tasks($taskid=NULL, $params=array())
    { 
        $url = $this->serviceurl.'/tasks'.($taskid ? '/'.$taskid : '').($params ? '?'.http_build_query($params) : '');
        $response = $this->lp_get($url);
        return($response);    
    }
    
    /**
     * Retrieves timesheets optionally filtered by parameters.
     *
     * @param  array  Parameters to send such as date and count limiters.
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
    public function timesheets($params=array())
    { 
        $url = $this->serviceurl.'/timesheets/'.($params ? '?'.http_build_query($params) : '');
        $response = $this->lp_get($url);
        return($response);    
    }

    /**
     * Retrieves timesheet entries optionally filtered by parameters.
     *
     * @param  array  Parameters to send such as date and count limiters. Documentation here: http://www.liquidplanner.com/api-guide/technical-reference/filtering-timesheet-entries.html
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
    public function timesheet_entries($timesheetid=NULL, $params=array())
    { 
        $url = $this->serviceurl.($timesheetid ? '/timesheets/'.$timesheetid : '').'/timesheet_entries'.($params ? '?'.http_build_query($params) : '');
        $response = $this->lp_get($url);
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
     * Retrieves the logged in user's account information.
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
    public function account()
    {
        $url = $this->baseurl.'/account';
        $response = $this->lp_get($url);
        return($response);    
    }

    /**
     * Retrieves the current workspace details.
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
    public function workspace()
    {
        $url = $this->serviceurl;
        $response = $this->lp_get($url);
        return($response);    
    }

    /**
     * Retrieves the specified client or a list of clients
     *
     * @param  int    $clientid ID of client.
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
    public function clients($clientid=NULL)
    { 
		$url = $this->serviceurl.'/clients'.($clientid ? '/'.$clientid : '');
        $response = $this->lp_get($url);
        return($response);    
    }

    /**
     * Creates a new client in Liquid Planner
     *
     * @param  string  $name          name of this client
     * @param  string  $description   plain-text description of the client
     * @param  string  $external_ref  arbitrary string; use e.g. to store a reference ID from an external system
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
    public function clients_create($name, $description = '', $external_ref = '')
    {
        $encodedClient = json_encode(array('client' => array(
        	'name' => $name,
        	'description' => $description,
        	'external_reference' => $external_ref
        )));
        $url = $this->serviceurl.'/clients';
        $response = $this->lp_post($url, $encodedClient);
        return($response);
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

    /**
     * Retrieves the specified member or a list of members
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
    public function members($memberid=NULL)
    {
        $url = $this->serviceurl.'/members'.($memberid? '/'.$memberid : '');
        $response = $this->lp_get($url);
        return($response);
    }

    /**
     * Retrieves one member
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
    public function member($memberid)
    {
        return($this->members($memberid));
    }

    /**
     * Creates a new project in Liquid Planner
     *
     * @param  string  $name          name of this project
     * @param  int	   $client_id     client ID associated with project
     * @param  int	   $parent_id     parent ID associated with project
     * @param  string  $description   plain-text description of the project
     * @param  bool    $is_done       whether the project is done or not
     * @param  string  $done_on       date the project was done on
     * @param  string  $external_reference       
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
    public function projects_create($name, $client_id, $parent_id, $description = '', $is_done = false, $done_on = '', $external_reference = '')
    {
        $encodedClient = json_encode(array('project' => array(
        	'name' => $name, 
        	'client_id' => $client_id,
        	'parent_id' => $parent_id,
        	'description' => $description,
        	'is_done' => $is_done,
        	'done_on' => $done_on,
        	'external_reference' => $external_reference
        )));
        $url = $this->serviceurl.'/projects';
        $response = $this->lp_post($url, $encodedClient);
        return($response);
    }

    /**
     * Retrieves the specified project or a list of projects
     *
     * @param  int    $projectid ID of project
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
    public function projects($projectid=NULL)
    { 
		$url = $this->serviceurl.'/projects'.($projectid ? '/'.$projectid : '');
        $response = $this->lp_get($url);
        return($response);    
    }

	/**
     * Retrieves the specified activity or a list of activities
     *
     * @param  int    $activityid ID of activity.
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
	function activities($activityid=NULL)
    {
		$url = $this->serviceurl.'/activities'.($activityid ? '/'.$activityid : '');
        $response = $this->lp_get($url);
        return($response);    
	}
	
	/**
     * Creates a new activity in Liquid Planner
     *
     * @param  array  $data   Values to apply to the newly created activity
     *
     * @return array  Response from Liquid Planner
     *
     * @access public
     */
    public function activities_create(array $data)
    {
    	return array("Not yet implemented - expected soon");
        $encodedActivity = json_encode(array('activity' => $data));
        $url = $this->serviceurl.'/activities';
        $response = $this->lp_post($url, $encodedActivity);
        return($response);
    }

/**************************************************************/

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
        $results = json_decode($response, true);
        
        /* Check for Throttling from the API */
        if((isset($results['type']) && $results['type'] == "Error") && (isset($results['error']) && $results['error'] == "Throttled"))
        {
        	//We're being throttled. Waith 15 seconds and call it again.
			$this->throttle_message();
			sleep($this->throttlewait);
        	return $this->lp_post($url, $encodedTask);
        }
        
        return $results;
    }
	
	 /**
     * Send data to the Liquid Planner API as a GET method
     */
    private function lp_get($url)
    {
        /* Set up the CURL object and execute it */
        $conn = curl_init();
        curl_setopt($conn, CURLOPT_HEADER, false);                                       // Suppress display of the response header
        curl_setopt($conn, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); // Must submit as JSON
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);                                // Return result as a string
        curl_setopt($conn, CURLOPT_POST, false);                                          // Submit data as an HTTP POST
        curl_setopt($conn, CURLOPT_ENCODING, "");                                        // Prevent GZIP compression of response from LP
        curl_setopt($conn, CURLOPT_USERPWD, $this->email.":".$this->password);           // Authenticate
        curl_setopt($conn, CURLOPT_URL, $url);                                           // Set the service URL
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);                               // Accept any SSL certificate
        $response = curl_exec($conn);
        curl_close($conn);

        /* The response is JSON, so decode it and return the result as an array */
        $results = json_decode($response, true);
        
        /* Check for Throttling from the API */
        if((isset($results['type']) && $results['type'] == "Error") && (isset($results['error']) && $results['error'] == "Throttled"))
        {
        	//We're being throttled. Wait 15 seconds and call it again.
			$this->throttle_message();
			sleep($this->throttlewait);
        	return $this->lp_get($url);
        }
        
        return $results;
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

        $results = json_decode($response, true);
        
        /* Check for Throttling from the API */
        if((isset($results['type']) && $results['type'] == "Error") && (isset($results['error']) && $results['error'] == "Throttled"))
        {
        	//We're being throttled. Waith 15 seconds and call it again.
			$this->throttle_message();
			sleep($this->throttlewait);
        	return $this->lp_delete($url);
        }
        
        return $results;
    }
    
    private function throttle_message()
    {
		if($this->debug === true)
		{
			echo '<p class="throttled">API Throttling in effect. Waiting ' . $this->throttlewait . ' seconds before trying again.</p>';

			/* Clear the output buffer if it's turned on. */
			if(ob_get_level() !== 0)
			{
		        ob_flush();
		        flush();
			}
		}
    }
}
