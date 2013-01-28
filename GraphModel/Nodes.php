<?php
namespace GraphModel;
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Nodes Model definition
 * @author Ben Fonarov
*/
abstract class Nodes extends \GraphModel
{
	//Helpers
	protected $type;
	protected $map = array();
	protected $connections = array();
	protected $_setters = array();
	protected $_getters = array();
	protected $_unique = array();

	/**
	 * __Construct function
	 * @param string $id
	 * @return \GraphModel\Nodes
	*/
	public function __construct($id=null)
	{
		$this->client = $this->getClient();
		$this->_initialize($id);
		return $this;
	}
	/**
	 * Initialize model object
	 * I'm not sure if this should be private or not...
	 * @returns model object
	 */
	protected function _initialize($id)
	{
		if ($id != null)
		{
			try {
				$this->node = $this->client->getNode($id);
			} catch (\Everyman\Neo4j\Exception $e) {
				throw new GraphModelException("No " . $type . " at id: " . $id);  return false;
			}
			foreach ($this->node->getProperties() as $key => $value)
			{
				$this->{$key} = $value;
			}
			//Should call to get all edges attached to their individual types here...
			foreach ($this->connections as $rel_type)
			{
				$class_type = 'GraphModel\Models\_' . ucfirst(strtolower($rel_type));
				$this->{strtolower($rel_type) . 's'} = new $class_type($this->node, $rel_type);
			}
		}
		return true;
	}

	/**
	 * Reload the current node object after save() so you can access edges
	 */
	public function reload()
	{
		$this->_initialize($this->id());
	}

	/**
	 * Map the nodes properties
	 * @return Object Map <array>:
	 */

	public function map()
	{
		$this->map['id'] = $this->id();
		foreach ($this->node->getProperties() as $key => $value)
		{
			$this->map[$key] = $value;
		}
		return $this->map;
	}

	/**
	 * @property Nodes $this
	 * @param String $property
	 * @return mixed
	 */
	public function __get($property)
	{
		$property = str_replace('get','',$property,$count);
		if ($count >= 1){
			if (method_exists($this, 'get' . ucwords($property)))
				call_user_func(array($this, 'get' . ucwords($property)), $value);
			else if (in_array($property, $this->_getters))
				return $this->{$property};
			else
				echo 'Property "' . $property . '" is not read accessible.'; //should be throw new GraphModelException
		}
		else
			return $this->{$property};
	}

	/**
	 * Magic Method for Setters
	 * @property Nodes $this
	 * @param String $property
	 * @param Mixed $value
	 * @return mixed
	 */
	public function __set($property, $value) //TODO: properties need to change to protected. Only through setX should you be able to set something that is protected. Otherwise, just like usual...
	{
		$property = str_replace('set','',$property,$count);
		if ($count >= 1){
			if (method_exists($this, 'set' . ucwords($property)))
				call_user_func(array($this, 'set' . ucwords($property)), $value);
			else if (in_array($property, $this->_setters))
			{
				$this->node->setProperty($property, $value);
				$this->{$property} = $value;
			}
			else
				echo 'Property "' . $property . '" is not write accessible.'; //should be throw new GraphModelException
		}
		else
			$this->{$property} = $value;
	}

	/**
	 * Call Magic Method - load the object through a property
	 * @example $object->$property('value') >> _initialize(id(value))
	 * @param String $name
	 * @param Mixed $params
	 * @throws GraphModelException
	 * @return mixed
	 */
	public function __call($name, $params)
	{
		if(method_exists($this, '_getby_' . $name))
			return call_user_func(array($this, '_getby_' . $name),$params);
		else
			throw new GraphModelException('Can\'t get this object through: ' . $name);
	}

	/**
	 * Check if a property is unique or not
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
	 * Get the id of the node
	 *
	 * @returns int <id>
	 */
	public function id()
	{
		return $this->node->getId();
	}

	/**
	 * Create a node model object
	 * @throws GraphModelException
	 * @return \GraphModel\Nodes
	 */
	public function create()
	{
		try {
			$this->node = $this->client->makeNode();
			$this->node->setProperty('type',$this->type);
		}
		catch (Exception $e) {
			throw new GraphModelException('Could not create the node');
		}
		return $this;
	}

	/**
	 * Create a node model object quickly
	 * @param mixed $array
	 * @throws GraphModelException
	 * @return \GraphModel\Nodes
	 */
	public function quick_create($array)
	{
		try {
			$this->node = $this->client->makeNode();
			$this->node->setProperty('type',$this->type);
		}
		catch (Exception $e) {
			throw new GraphModelException('Could not create the node');
		}
		foreach ($array as $key=>$value)
		{
			if (in_array($key, $this->_unique))
			{
				if ($this->unique($key, $value))
					$this->node->setProperty($key, $value);
				else
				{
					throw new GraphModelException($key . ' isn\'t unique');
				}
			}
			else
				$this->node->setProperty($key, $value);
		}
		return $this;
	}

	/**
	 * Persist the node model object
	 * @return \GraphModel\Nodes
	 */
	public function save()
	{
		if(is_null($this->node->getProperty('create_time'))) {
			$this->node->setProperty('create_time', time());
		}
		$this->node->setProperty('update_time', time());
		$this->node->save();
		return $this;
	}
}