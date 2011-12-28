<?php defined('SYSPATH') or die('No direct script access.');

/**
 * The SearchBlox_Document class
 *
 * @package		SearchBlox
 * @author		Michał Musiał
 * @copyright	(c) 2011 Michał Musiał
 */
class SearchBlox_Document
{
	/**
	 * Result order number in resultset
	 * @var int
	 */
	public $no;
	
	/**
	 * URL of the document
	 * @var string
	 */
	public $url;

	/**
	 * SearchBlox collection number
	 * @var int
	 */
	public $col;
	
	/**
	 * Document filename
	 * @var string
	 */
	public $filename;
	
	/**
	 * Document last modification date
	 * @var string
	 */
	public $lastmodified;
	
	/**
	 * Document indexed date
	 * @var string
	 */
	public $indexdate;
	
	/**
	 * Document filesize in bytes
	 * @var int
	 */
	public $size;
	
	/**
	 * Document title with highlighting (if enabled in SearchBlox server
	 * @var string
	 */
	public $title;
	
	/**
	 * Document title without highlighting
	 * @var string
	 */
	public $alpha;
	
	/**
	 * Document content type (not mime!)
	 * @var string
	 */
	public $contenttype;
	
	/**
	 * Document generator name
	 * @var string
	 */
	public $generator;
	
	/**
	 * Context with highlighting (or complete document if keyword in context not enabled on server)
	 * @var string
	 */
	public $context;
	
	/**
	 * Document description
	 * @var string
	 */
	public $description;
	
	/**
	 * Documents query score percentage
	 * @var int
	 */
	public $score;

	/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
	
	/**
	 * Object constructor
	 *
	 * @param array $props 
	 */
	public function __construct(array $props = array())
	{
		$this->set_props($props);
	}

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
	
	/**
	 * Set a bundle of properties in one go
	 *
	 * @param array $props 
	 */
	public function set_props(array $props)
	{
		if ( ! empty($props))
		{
			foreach ($props as $name => $value)
			{
				if (property_exists($this, $name) AND is_scalar($value))
				{
					$this->$name = $value;
				}
			}
		}
	}
}
