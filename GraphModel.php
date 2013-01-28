<?php 

use Everyman\Neo4j\Client;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class GraphModel
{
	protected static $client;

	public function __construct()
	{
		spl_autoload_register(array($this,'autoload'));
		self::$client = new Client();
		return $this; //client should change to singleton in future versions
	}

	private function autoload($sClass)
	{
		$sLibPath = __DIR__.DIRECTORY_SEPARATOR;
		$sClassFile = str_replace('\\',DIRECTORY_SEPARATOR,$sClass).'.php';
		$sClassPath = $sLibPath.$sClassFile;
		if (file_exists($sClassPath)) {
			require($sClassPath);
		}
	}

	public function getClient() {
		return self::$client;
	}

	public function startBatch() {
		return self::$client->startBatch();
	}

	/**
	 * Query via Gremlin
	 * @param string $gremlin_query_string
	 * @param string $named_array
	 * @return \Everyman\Neo4j\Query\ResultSet
	 */
	public function gremlin_query($gremlin_query_string, $named_array=null)
	{
		if ($named_array != null)
		{
			$query = new \Everyman\Neo4j\Gremlin\Query(self::$client, $gremlin_query_string, $named_array);
		}
		else
		{
			$query = new \Everyman\Neo4j\Gremlin\Query(self::$client, $gremlin_query_string);
		}
		$result = $query->getResultSet();
		return $result;
	}

	/**
	 * Query via Cypher
	 * @param string $cypher_query_string
	 * @param string $named_array
	 * @return multitype:
	 */
	public function cypher_query($cypher_query_string, $named_array=null, $merge=TRUE, $batch=FALSE)
	{
		try {
			$query = new \Everyman\Neo4j\Cypher\Query(self::$client, $cypher_query_string, $named_array, $batch);
		}
		catch (Everyman\Neo4j\Exception $e)
		{
			throw new GraphModelException('Unable to execute query: ' . $e);
		}
		if ($merge)
			$result = $query->getResultSet()->merge_column_data();
		else
			$result = $query->getResultSet();
		return $result;
	}

	/**
	 * Combines column and data from graph query responses
	 * @param object $result_set
	 * @return array:
	 */
	private function _merge_column_data($result_set)
	{
		$data = $result_set->data;
		$columns = $result_set->columns;
		$array = array();
		foreach ($data as $key=>$row)
		{
			$array[$key] = array_combine($columns, $row);
		}
		return $array;
	}
}