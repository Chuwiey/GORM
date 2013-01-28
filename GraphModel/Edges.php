<?php
namespace GraphModel;
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Edges Model definition
*/
abstract class Edges extends \GraphModel implements \Iterator
{
	/**
	 * Class Constants
	 * @var Constants
	 */
	const DOUT = 'out';
	const DIN = 'in';
	const DBOTH = 'both';

	/**
	 * Edges Class Internal Properties
	 * @var protected properties
	 */
	protected $edges;
	protected $type;
	protected $direction;
	protected $map = array();
	protected $_setters = array();
	protected $_getters = array();

	// Iterator helpers
	protected $position = 0;
	protected $relationships;

	/**
	 *
	 * @param \Everyman\Neo4j\Node $node
	 * @param String $type
	 * @return \GraphModel\Edges
	 */
	public function __construct($node, $type)
	{
		$this->position = 0;
		$this->node = $node;
		$this->type = $type;
		$this->client = $this->getClient();
		$this->_initialize();
		return $this;
	}

	/**
	 * Initialize the edges object
	 * @throws GraphModelException
	 * @return boolean
	 */
	protected function _initialize()
	{
		try {
			$this->relationships = $this->node->getRelationships(array($this->type));
		} catch (\Everyman\Neo4j\Exception $e) {
			throw new GraphModelException("No " . $this->type . " at id: " . $this->node->getId()); return false;
		}
		$node_dir = $this->dir();
		foreach ($this->relationships as $key => $value)
		{
			$edge_type = $value->getType();
			$end_node = $value->$node_dir();
			$this->edges[$end_node->getId()]['edge'] = $value;
			$this->edges[$end_node->getId()]['end_node'] = $end_node;
		}
		return true;
	}


	/**
	 * Set start_node or end_node based on class direction
	 * @return string
	 */
	public function dir()
	{
		switch ($this->direction)
		{
			case $this::DOUT:
				return 'getStartNode';
			case $this::DIN:
				return 'getEndNode';
			default:
				return 'getEndNode';
		}
	}



	/**
	 * Load a Edge Type object
	 * @param int $id
	 * @return Edge type object
	 */
	public function find($id)
	{
		$object_name = str_replace('_','o',get_class($this));
		return new $object_name($id);
	}


	/**
	 * Deliver all edges array - map()
	 * @param string $callback
	 * @return array:
	 */
	public function map($callback=null)
	{
		if(count($this->edges) > 0)
		{
			foreach ($this->edges as $id=>$rel)
			{
				$this->map[$id]['edge'] = $rel['edge']->getProperties();
				$this->map[$id][$rel['edge']->getType()] = $rel['end_node']->getProperties();
			}
			if(isset($callback))
			{
				$this->map = $this->{$callback}($this->map);
			}
		}
		return $this->map;
	}

	/**
	 * @TODO: FIXME __invoke function in Edges
	 * @return Ambigous <\GraphModel\array:, multitype:>
	 */
	public function __invoke()
	{
		return $this->map();
	}

	/**
	 * Create a unique edge between two nodes
	 * @param Object $other_node
	 * @param String $rel_type
	 * @return \Everyman\Neo4j\
	 */
	public function create_unique_edge($other_node, $id)
	{
		switch ($this->direction)
		{
			case $this::DOUT:
				$structure = "n1<-[r:{$this->type}]-n2";
				break;
			case $this::DIN:
				$structure = "n1-[r:{$this->type}]->n2";
				break;
			case $this::DBOTH:
				$structure = "n1-[r:{$this->type}]-n2";
				break;
		}
		try {
			$cypher = "START n1=node({$this->node->getId()}), n2=node({$id})".
					" CREATE UNIQUE {$structure}".
					" RETURN id(r)";
			$result = $this->cypher_query($cypher);
			$this->edges[$id]["edge"] = $this->client->getRelationship($result[0]['id(r)']);
			$this->edges[$id]["end_node"] = $other_node;
			return TRUE;
		}
		catch (Everyman\Neo4j\Exception $e)
		{
			throw new GraphModelException('Couldn\'t connect n1 to n2 as: ' . $this->type);
			return FALSE;
		}
	}

	/** Check if a property is unique or not
	 * @param String $property
	 * @param String $value
	 * @return boolean
	 */
	public function unique($property,$value)
	{
		$userIndex = new \Everyman\Neo4j\Index\NodeIndex($this->client, 'node_auto_index');
		$match = $userIndex->findOne($property, $value);
		if ($match != null)
			return false;
		else
			return true;
	}

	/**
	 * Connect a node object to another existing node object
	 * @param User Object $other
	 * @param string $properties
	 * @return \GraphModel\Models\oUser
	 */
	public function add($other, $properties=null)
	{
		$id = $other->id();
		if(!isset($this->edges[$id])) {
			if($this->create_unique_edge($other, $id))
			{
				$this->edges[$id]["edge"]->setProperty('create_time', time());
				if($properties != null)
				{
					foreach($properties as $key=>$value)
					{
						$this->edges[$id]["edge"]->setProperty($key, $value);
					}
				}
				$this->edges[$id]["edge"]->save();
			}
			else
				throw new GraphModelException($this->type . ' add failed');
		}
		else
			throw new GraphModelException('A connection of type: ' . $this->type . ' already exists');
		return $this;
	}

	/**
	 * Iterator Functions
	 */
	public function rewind() {
		reset($this->edges);
	}

	public function current() {
		return current($this->edges);
	}

	public function key() {
		return key($this->edges);
	}

	public function next() {
		next($this->edges);
	}

	public function valid() {
		return isset($this->edges[$this->key()]);
	}
}
