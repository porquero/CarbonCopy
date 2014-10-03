<?php

/**
 * Manage user table
 *
 * @package CarbonCopy
 * @subpackage cc
 * @author Cristian Riffo <criffoh@gmail.com>
 */
class m_user extends CI_Model {

	protected $_table_name = 'user';

	public function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('default', TRUE);
	}

	/**
	 * Verify if username exists
	 *
	 * @param string $username
	 *
	 * @return bool
	 */
	public function user_exists($username)
	{
		return $this->db->from($this->_table_name)->where('username', $username)->count_all_results() == 1;
	}

	/**
	 * Verify if email exists
	 *
	 * @param string $email
	 *
	 * @return bool
	 */
	public function email_exists($email)
	{
		return $this->db->from($this->_table_name)->where('email', $email)->count_all_results() == 1;
	}

	/**
	 * Insert new user in database.
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function create($data)
	{
		return $this->db->insert($this->_table_name, $data);
	}

	/**
	 * Validate user from database using user and password
	 *
	 * @param string $email
	 * @param string $psw md5
	 *
	 * @return boolean
	 */
	public function login($email, $psw)
	{
		$r = $this->db->select('username, principal_account')->from($this->_table_name)->where('email', $email)
				->where('password', md5($psw))->get()->row();

		if (isset($r->username)) {
			return $r;
		}

		return FALSE;
	}

	/**
	 * See if it possible validate an account
	 *
	 * @param string $token
	 *
	 * @return bool
	 */
	public function can_validate($token)
	{
		return $this->db->from($this->_table_name)->where('concat(password, md5(email)) = \'' . $token . '\'')
				->count_all_results() == 1;
	}

	/**
	 * Check if user has validated
	 *
	 * @param type $email
	 * @return type
	 */
	public function validated($email)
	{
		return $this->db->from($this->_table_name)->where('email', $email)->where('validated', 1)
				->count_all_results() == 1;
	}

	/**
	 * Validate account
	 *
	 * @param string $token
	 *
	 * @return bool
	 */
	public function validate($token)
	{
		$this->db->where('concat(password, md5(email)) = \'' . $token . '\'')->update($this->_table_name, array('validated' => 1));

		$r = $this->db->select('username')->from($this->_table_name)->where('concat(password, md5(email)) = \'' . $token . '\'')->get()->row();

		return $r->username;
	}

	/**
	 * Update user data
	 *
	 * @param string $user
	 * @param array $data
	 *
	 * @return bool
	 */
	public function update($user, $data)
	{
		return $this->db->where('username', $user)->update($this->_table_name, $data);
	}

	/**
	 * Get users filter by username list
	 *
	 * @param array $participants
	 *
	 * @return array list of users
	 */
	public function where_in($participants)
	{
		$result = array();
		$participants = implode("','", $participants);

		$r = $this->db->select('username, name, email, ts')->from($this->_table_name)
				->where('username in(\'' . $participants . '\')')->get();

		foreach ($r->result() as $user) {
			$result[] = $user;
		}

		return $result;
	}

	/**
	 * Get database info for user.
	 *
	 * @param string $username
	 *
	 * @return object
	 */
	public function data($username)
	{
		$r = $this->db->get_where($this->_table_name, array('username' => $username))->row();

		if (is_array($r)) {
			return (object) array(
						'username' => '',
						'name' => '',
						'email' => '',
						'password' => '',
						'accounts' => '',
						'principal_account' => '',
						'validated' => '',
						'ts' => '',
			);
		}
		else {
			return $r;
		}
	}

	/**
	 * Add account to user
	 *
	 * @param string $username
	 * @param string $account
	 *
	 * @return boolean
	 */
	public function add_account($username, $account)
	{
		if ($this->has_account($username, $account)) {
			return TRUE;
		}

		$user_data = $this->data($username);
		$data = array('accounts' => $user_data->accounts . '_' . $account);

		return $this->db->where('username', $username)->update($this->_table_name, $data);
	}

	/**
	 * Check if user has a account
	 * 
	 * @param string $username
	 * @param string $account
	 * 
	 * @return boolean
	 */
	public function has_account($username, $account)
	{
		$user_data = $this->data($username);
		if (strstr($user_data->accounts, '_') !== FALSE) {
			$accounts = explode('_', $user_data->accounts);
		}
		else {
			return $user_data->accounts === $account;
		}

		return in_array($account, $accounts);
	}

	/**
	 * Check if user has default account
	 *
	 * @param string $username
	 *
	 * @return boolean
	 */
	public function has_not_default_account($username)
	{
		$r = $this->db->select('principal_account')->from($this->_table_name)->where(array('username' => $username))->get()->row();

		return empty($r->principal_account);
	}

	/**
	 * Get username by email.
	 *
	 * @param string $email
	 *
	 * @return string
	 */
	public function username($email)
	{
		$r = $this->db->select('username')->from($this->_table_name)->where(array('email' => $email))->get()->row();

		return $r->username;
	}

	/**
	 * Return all user accounts with the info.
	 *
	 * @param string $username
	 *
	 * @return array
	 *
	 * @todo Validate if account exists!
	 */
	public function accounts($username)
	{
		$r = $this->db->select('accounts')->from($this->_table_name)->where('username', $username)->get()->row();

		if (strstr($r->accounts, '_') !== FALSE) {
			$accounts = explode('_', $r->accounts);
			$info_accounts = array();

			foreach ($accounts as $account) {
				$info_account_file = "_accounts/{$account}/info_account.json";
				if (is_file($info_account_file)) {
					$info_accounts[] = Modules::run('file/read/json_content', $info_account_file);
				}
				else {
					Plogger::log('Account file doesn\'t exists!: ' . $info_account_file);
				}
			}

			return $info_accounts;
		}
		else {
			return array(0 => Modules::run('file/read/json_content', "_accounts/{$r->accounts}/info_account.json"));
		}
	}

	/**
	 * Get timeline user actions
	 * 
	 * @param string $username
	 *
	 * @return array
	 */
	public function timeline($username)
	{
		$r = $this->db->get_where($this->_table_name, array('username' => $username));
		$result = array();

		foreach ($r->result() as $action) {
			$result[] = $action;
		}

		return $result;
	}

}
