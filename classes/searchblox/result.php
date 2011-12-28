<?php defined('SYSPATH') or die('No direct script access.');

/**
 * The SearchBlox_Query class
 *
 * @package		SearchBlox
 * @author		Michał Musiał
 * @copyright	(c) 2011 Michał Musiał
 */
class SearchBlox_Result
{
	/**
	 * Raw result from the server
	 * @var string
	 */
	public $raw;

	/**
	 * Number of hits (results) found
	 * @var int
	 */
	public $hits;
	
	/**
	 * Time in seconds it took to get results
	 * @var float
	 */
	public $time;
	
	/**
	 * Formatted query string as returned by the SearchBlox server
	 * @var string
	 */
	public $query;
	
	/**
	 * Suggested query
	 * @var string
	 */
	public $suggest;
	
	/**
	 * Number of the first result in this results set
	 * @var int
	 */
	public $start;
	
	/**
	 * Number of the last result in this results set
	 * @var int
	 */
	public $end;
	
	/**
	 * Current page
	 * @var int
	 */
	public $currentpage;
	
	/**
	 * Total number of pages
	 * @var int
	 */
	public $lastpage;
	
	/**
	 * Date from which the documents are supposed to be matched (unix timestamp format)
	 * @var int
	 */
	public $startdate;
	
	/**
	 * Date to which the documents are supposed to be matched (unix timestamps format)
	 * @var type 
	 */
	public $enddate;

	/**
	 * Array of results
	 * @var array
	 */
	public $results = array();
	
	/**
	 * Array of collections
	 * @var array
	 */
	public $collections = array();


	/**
	 * Error message code.
	 * @var int
	 */
	public $error_code;

	/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

	/**
	 * Returns current object with its all public (theoretically) properties in array form.
	 * Not recursive!
	 *
	 * @return array
	 */
	public function as_array()
	{
		$this_array = get_object_vars($this);
		
		foreach ($this_array as $name => $value)
		{
			if (strpos($name, '_') === 0)
			{
				unset($this_array[$name]);
			}
		}
		
		return $this_array;
	}
}
