<?php
namespace GraphModel\Models;
use GraphModel\GraphModelException;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * User model for Graph DB
 * @author: Ben Fonarov
 * Node
**/
class oUser extends \GraphModel\Nodes
{
	// id : node(id)
	//Basic Credentials
	protected $username = null; // username : varchar(20)
	protected $email = null; // email : varchar(255)
	protected $fbuid = null; // oauth_fb : long double | Holds Facebook ID
	protected $fb_token = null; // fb_token : varchar(255) | Holds Facebook Auth Token
	//Security
	protected $password = null; // password : char(40)
	protected $salt = null; // salt : int(11) | A number added to the hash function in order for it to be more secure
	//Profile
	protected $first_name = null; // first_name : varchar(40)
	protected $middle_name = null; // middle_name : varchar(20)
	protected $last_name = null; // last_name : varchar(40)
	protected $sex = null; // sex : varchar(6)
	//protected $options = array(); // options : varchar(?) // CURRENTLY NOOP

	//Until MediaManager is built:
	protected $pic = null; // pic : varchar(255)
	protected $pic_square = null; // pic_square : varchar(255)

	//Internal
	protected $status = null; // status : binary
	protected $fb_sync = null; // fb_sync : binary
	protected $logged_in = false;
	protected $init_lists = null; // boolean
	//Helpers
	protected $type = 'user'; // type : set('user', 'list', 'item', 'og_type', 'social')
	protected $update_time = null; // update_time : timestamp
	protected $create_time = null; // create_time : timestamp
	protected $connections = array('FRIEND','LIST','SOCIAL','ITEM');
	protected $_getters = array('username', 'email', 'fbuid', 'fb_token', 'password', 'first_name', 'middle_name', 'last_name', 'sex', 'pic', 'pic_square', 'status', 'fb_sync', 'init_lists');
	protected $_setters = array('username', 'email', 'fbuid', 'fb_token', 'password', 'first_name', 'middle_name', 'last_name', 'sex', 'pic', 'pic_square', 'status', 'fb_sync');
	protected $_unique = array('username', 'email', 'fbuid');


	/**
	 * Gets the user object by username
	 * @abstract This function and __call in Nodes class, assumes unique properties are indexed automatically
	 * @param string $username
	 * @return boolean (after initializing the current object)
	 */

	protected function _getby_username($username)
	{
		$userIndex = new \Everyman\Neo4j\Index\NodeIndex($this->client, 'node_auto_index');
		$match = $userIndex->findOne('username', $username[0]);
		$this->_initialize($match->getId());
		return true; // @TODO: Should really think about what to do here?
	}

	protected function _getby_fbuid($fbuid)
	{
		$userIndex = new \Everyman\Neo4j\Index\NodeIndex($this->client, 'node_auto_index');
		$match = $userIndex->findOne('fbuid', $fbuid[0]);
		$this->_initialize(($match == null)?$match:$match->getId());
		return true; // @TODO: Should really think about what to do here?
	}

	/**
	 * Makes the password hash
	 * @param string $password
	 * @param float $salt
	 * @return Array <salt, password hash> string
	 */

	private function _make_password($password, $salt = NULL)
	{
		if (!isset($salt))
		{
			$salt = rand(10000000000,99999999999);
		}
		$sha_pwd = sha1($password . $salt);
		$hashed_pwd = array(
				'salt' => $salt,
				'sha_pwd' => $sha_pwd
		);
		return $hashed_pwd;
	}
}