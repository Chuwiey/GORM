<?php
namespace GraphModel\Models;
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Friend model for Graph DB
 * Node
**/
class _Friend extends \GraphModel\Edges
{
	// id : edge(id)

	protected $direction = 'both';
	protected $_getters = array();
	protected $_setters = array();

}