<?php defined('SYSPATH') or die('No direct script access.');

/**
 * The SearchBlox_Query class
 *
 * @package		SearchBlox
 * @author		Michał Musiał
 * @copyright	(c) 2011 Michał Musiał
 */
class SearchBlox_Query
{
	const DATE_FORMAT = 'YMdHis';
	const QUERY_EMPTY = 1;
	const SERVER_URL_NOT_VALID = 2;
	const SERVER_UNAVAILABLE = 4;
	const SERVER_TIMEOUT = 8;

	/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
	
	/**
	 * The SearchBlox server url with protocol and port if not standard
	 * @var string
	 */
	public $url_server;
	
	/**
	 * Full search query
	 * @var string
	 */
	public $url_query;
	
	/**
	 * Encoding?
	 * @var string 
	 */
	public $fe = 'utf-8';
	
	/**
	 * Selected collection(s)
	 * - int if a single collection
	 * - array of ints if more
	 * @var mixed
	 */
	public $col;

	/**
	 * Startdate for daterange query. Possible formats:
	 * - n - number of months for startdate only searches
	 * - YYYYMMDDHHMMSS
	 * @var mixed
	 */
	public $startdate;
	
	/**
	 * Enddate for daterange query in YYYYMMDDHHMMSS format.
	 * @var int
	 */
	public $enddate;
	
	/**
	 * Document content type. 
	 * Possible values: pdf | word | excel | ppt | rtf | text
	 * @var string
	 */
	public $contenttype;
	
	/**
	 * Occurance - where the searched string should be found.
	 * Possible values: all | title | content | keywords | description | url
	 * @var string
	 */
	public $oc = 'all';
	
	/**
	 * Number of documents per resultset
	 * @var int
	 */
	public $pagesize = 10;
	
	/**
	 * Requested page (resultset)
	 * @var int
	 */
	public $page = 1;
	
	/**
	 * Order by which the results are sorted.
	 * Possible values: relevance | date | alpha
	 * @var type 
	 */
	public $sort = 'relevance';

	/**
	 * Results stylesheet.
	 * @var string
	 */
	public $xsl = 'xml';
	
	/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

	/**
	 * Object constructor.
	 * Optionally allows defining server url.
	 *
	 * @param string $url Url of the SearchBlox server inc. protocl and port if not standard.
	 */
	public function __construct($url = '')
	{
		$this->url_server = $url;
	}

	/**
	 * Adds parameters to query string
	 * 
	 * @param string $name
	 * @param string $value 
	 * @return bool Returns TRUE if can assign param, FALSE otherwise.
	 */
	public function add_param($name, $value)
	{
		if (property_exists($this, $name) AND is_scalar($value))
		{
			$this->{$name} = $value;
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Make the actual query
	 *
	 * @param string $query
	 * @param array $params
	 * @return SearchBlox_Result Returns SearchBlox_Result if query made successfully, FALSE otherwise.
	 */
	public function search($query, array $params = array())
	{
		$query = trim($query);
		
		// Make sure the query is not empty
		if (empty($query))
		{
			$this->error_code = self::QUERY_EMPTY;
			return FALSE;
		}
		
		// Validate server URL
		if ( ! $this->_validate_server_url($this->url_server))
		{
			$this->error_code = self::SERVER_URL_NOT_VALID;
			return FALSE;
		}
		
		// Parse query params if provided
		if ( ! empty($params))
		{
			foreach ($params as $name => $value)
			{
				$this->add_param($name, $value);
			}
		}
		
		// Make the query
		$this->url_query = $this->_build_url($query);
		Kohana::$log->add(Log::INFO, 'SearchBlox query URL: :url', array(':url' => $this->url_query));
		$xml_str = '';
		
		try
		{
			$xml_str = Request::factory($this->url_query)->execute()->body();
		}
		catch (Kohana_Exception $e)
		{
			$this->error_code = self::SERVER_UNAVAILABLE;
			return FALSE;
		}
		
		// Parse the query
		require Kohana::find_file('vendor/SimpleDOM', 'SimpleDOM');
		$xml = simpledom_load_string($xml_str);
		
		$query_result = new SearchBlox_Result();
		$query_result->raw		= $xml_str;
		$query_result->hits		= (int) $xml->results['hits'];
		$query_result->time		= (float) $xml->results['time'];
		$query_result->query	= (string) $xml->results['query'];
		$query_result->suggest	= (string) $xml->results['suggest'];
		$query_result->start	= (int) $xml->results['start'];
		$query_result->end		= (int) $xml->results['end'];
		$query_result->currentpage	= (int) $xml->results['currentpage'];
		$query_result->lastpage	= (int) $xml->results['lastpage'];
		$query_result->startdate	= (string) $xml->results['startdate'];
		$query_result->enddate	= (string) $xml->results['enddate'];
		
		// Gather results
		foreach ($xml->results->result as $xml_document)
		{
			$no = (int) $xml_document['no'];
			$result_props = array(
				'no'			=> $no,
				'url'			=> (string) $xml_document->url,
				'col'			=> (int) $xml_document->col,
				'filename'		=> (string) $xml_document->filename,
				'lastmodified'	=> (string) $xml_document->lastmodified,
				'indexdate'		=> (string) $xml_document->indexdate,
				'size'			=> (int) $xml_document->size,
				'title'			=> str_replace('highlight', 'em', $xml_document->title->innerXML()),
				'alpha'			=> (string) $xml_document->alpha,
				'contenttype'	=> (string) $xml_document->contenttype,
				'generator'		=> (string) $xml_document->generator,
				'context'		=> str_replace('highlight', 'em', $xml_document->context->innerXML()),
				'description'	=> str_replace('highlight', 'em', $xml_document->description->innerXML()),
				'score'			=> (int) $xml_document->score,
			);
			$result = new SearchBlox_Document($result_props);
			$query_result->results[$no] = $result;
		}
		
		// Gather collections
		foreach ($xml->searchform->collections->collection as $xml_collection)
		{
			$id = (int) $xml_collection['id'];
			$query_result->collections[$id] = (string) $xml_collection['name'];
		}
		
		return $query_result;
	}

	/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
	
	/**
	 * Builds query URL based on given parameters
	 * 
	 * @param string $query
	 * @return string 
	 */
	protected function _build_url($query)
	{
		$params = array(
			'fe'			=> $this->fe,
			'query'			=> $query,
			'startdate'		=> $this->startdate,
			'enddate'		=> $this->enddate,
			'contenttype'	=> $this->contenttype,
			'oc'			=> $this->oc,
			'pagesize'		=> $this->pagesize,
			'page'			=> $this->page,
			'sort'			=> $this->sort,
			'xsl'			=> $this->xsl,
		);
		
		$url = $this->url_server . '?' . http_build_query($params);
		
		if ( ! empty($this->col))
		{
			if (is_array($this->col))
			{
				foreach ($this->col as $col_id)
				{
					$url .= '&col=' . $col_id;
				}
			}
			else
			{
				$url .= '&col=' . $this->col;
			}
		}
		
		return $url;
	}


	/**
	 * Validates the server URL
	 * 
	 * @param string $url Server URL
	 * @return bool Returns TRUE if URL is a valid server URL, FALSE otherwise.
	 */
	protected function _validate_server_url($url)
	{
		return (preg_match('|^https?://|i', $url) AND filter_var($url, FILTER_VALIDATE_URL));
	}
}
