<?php
namespace GraphModel;
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GraphModelException extends \Exception
{
	public function __construct($error)
	{
		/*echo($e->getMessage().'<pre>'.$e->getTraceAsString().'</pre>');
		while($e = $e->getPrevious())
			echo('Caused by: '.$e->getMessage().'<pre>'.$e->getTraceAsString().'</pre>');*/
		//echo $error;
	}
}