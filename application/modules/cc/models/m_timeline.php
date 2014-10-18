<?php

/**
 * Manage timeline table
 *
 * @package CarbonCopy
 * @subpackage cc
 * @author porquero
 */
class m_timeline extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('default', TRUE);
	}

	/**
	 * Get timeline for resume
	 *
	 * @param integer $participant_id
	 * @param string $ts_date
	 *
	 * @return array
	 */
	public function get_for_resume($participant_id = NULL, $ts_date = NULL)
	{
		$result = array();
		$table = 'timeline_' . $this->session->userdata('current_account');
		$date_format = account_date_format_mysql();

		$this->db
			->from("{$table} t")
			->select(
				"distinct date_format(t.ts, '{$date_format}') ts, date(t.ts) sql_date, t.context, t.from_participant, "
				. "t.to_participant, t.title, t.id_topic, a.name, t.id_context"
				, FALSE)
			->join('action a', 't.action_id = a.id')
			->order_by('t.id', 'desc');

		if (is_null($participant_id) === FALSE) {
			$this->db->where('to_participant', $participant_id);
		}

		if (is_null($ts_date) === FALSE) {
			$this->db->where('date(ts)', $ts_date);
		}

		$account_timeline = $this->db->get();

		foreach ($account_timeline->result() as $action) {
			$result[] = $action;
		}

		return $result;
	}

	/**
	 * Get timeline for context
	 *
	 * @param string $context
	 * @return array
	 */
	public function get_for_context($context, $participant_id = NULL, $ts_date = NULL)
	{
		$result = array();
		$table = 'timeline_' . $this->session->userdata('current_account');
		$date_format = account_date_format_mysql();

		$this->db
			->from("{$table} t")
			->select(
				"distinct DATE_FORMAT(t.ts, '{$date_format}') ts, date(t.ts) sql_date, t.context, t.from_participant, t.to_participant, t.title, t.id_topic, a.name, t.id_context"
				, FALSE)
			->join('action a', 't.action_id = a.id')
			->where("t.context REGEXP '{$context}(.*)'")
			->order_by('t.id', 'desc');

		if (is_null($participant_id) === FALSE) {
			$this->db->where('to_participant', $participant_id);
		}

		if (is_null($ts_date) === FALSE) {
			$this->db->where('date(ts)', $ts_date);
		}

		$account_timeline = $this->db->get();

//		Plogger::var_dump($this->db->last_query());

		foreach ($account_timeline->result() as $action) {
			$result[] = $action;
		}

		return $result;
	}

	/**
	 * Get timeline for context
	 *
	 * @param string $context
	 * @return array
	 */
	public function contexts_topics($context, $participant_id = NULL, $ts_date = NULL)
	{
		$result = array();
		$table = 'timeline_' . $this->session->userdata('current_account');

		$this->db
			->from("{$table} t")
			->select('t.title, t.id_topic, t.context')
			->join('action a', 't.action_id = a.id')
			->where("t.context = '{$context}'")
			->where('id_topic is not null')
			->group_by('title')
			->order_by('t.id', 'desc');

		if (is_null($participant_id) === FALSE) {
			$this->db->where('to_participant', $participant_id);
		}

		if (is_null($ts_date) === FALSE) {
			$this->db->where('date(ts)', $ts_date);
		}

		$account_timeline = $this->db->get();

//		Plogger::var_dump($this->db->last_query());

		foreach ($account_timeline->result() as $action) {
			$result[] = $action;
		}

		return $result;
	}

	/**
	 * Get timeline for topic
	 * 
	 * @param string $context
	 * @return array
	 */
	public function get_for_topic($context)
	{
		$result = array();
		$table = 'timeline_' . $this->session->userdata('current_account');
		preg_match('/(\_[a-zA-Z0-9-]*)$/', $context, $id_topic);
		$date_format = account_date_format_mysql();

		$account_timeline = $this->db
			->from("{$table} t")
			->select(
				"distinct DATE_FORMAT(t.ts, '{$date_format}') ts, date(t.ts) sql_date, t.context, t.from_participant, t.to_participant, t.title, t.id_topic, a.name"
				, FALSE)
			->join('action a', 't.action_id = a.id')
			->where("t.id_topic = '{$id_topic[0]}'")
			->order_by('t.id', 'desc')
			->limit(7)
			->get();

		foreach ($account_timeline->result() as $action) {
			$result[] = $action;
		}

		return $result;
	}

	/*
	 * Get user timeline
	 * 
	 * @param string $username
	 * @param string $participant_id
	 * @param string $ts_date yyyy-mm-dd
	 * 
	 * @return array
	 */

	public function get_for_user($username, $participant_id = NULL, $ts_date = NULL)
	{
		$table = 'timeline_' . $this->session->userdata('current_account');
		$date_format = account_date_format_mysql();

		$this->db->select(
				"distinct DATE_FORMAT(t.ts, '{$date_format}') ts, date(t.ts) sql_date, t.context, t.from_participant, t.to_participant, t.title, t.id_topic, a.name, t.id_context"
				, FALSE)
			->from($table . ' t')
			->join('action a', 't.action_id = a.id')
			->where('from_participant', $username)
			->order_by('t.id', 'desc');

		if (is_null($participant_id) === FALSE) {
			$this->db->where('to_participant', $participant_id);
		}

		if (is_null($ts_date) === FALSE) {
			$this->db->where('date(ts)', $ts_date);
		}

		$r = $this->db->get();
		$result = array();

		foreach ($r->result() as $action) {
			$result[] = $action;
		}

		return $result;
	}

	/**
	 * Save action ocurred in timeline
	 * 
	 * @param array $data Array format [title, from_participant, to_participant, context, action_id, id_topic]
	 * @return object
	 */
	public function save_action($data)
	{
		$table = 'timeline_' . $this->session->userdata('current_account');

		$this->db->insert($table, $data);
	}

	/**
	 * Create new timeline table for new account
	 * 
	 * @param type $accoun_id
	 */
	public function create($accoun_id)
	{
		$table = 'timeline_' . $this->session->userdata('current_account');

		return $this->db->query("create table timeline_{$accoun_id} like " . $table);
	}

	/**
	 * Update context and childs timeline to new context recently moved
	 *
	 * @param string $id_context
	 * @param string $from_context
	 * @param string $to_context
	 */
	public function move_context($id_context, $from_context, $to_context)
	{
		$table = 'timeline_' . $this->session->userdata('current_account');

		// Move childs first.
		$from = trim($from_context . '_' . $id_context, '_');
		$to = trim($to_context . '_' . $id_context, '_');

		$q = <<<PQR
update `{$table}`
set context = replace(context, "{$from}", "{$to}") WHERE context like '{$from}%'
PQR;
		$this->db->query($q, FALSE);

		// Finally move context.
		$this->db->where('context', $from_context)
			->where('id_context', $id_context)
			->update($table, array('context' => $to_context));
	}

	/**
	 * Remove timeline for context
	 *
	 * @param string $id_context
	 * @param string $context
	 *
	 * @return boolean
	 */
	public function delete_context($id_context, $context)
	{
		$table = 'timeline_' . $this->session->userdata('current_account');

		$full_context = trim("{$context}_{$id_context}", '_');

		$q = <<<PQR
DELETE FROM `{$table}`
WHERE `context` LIKE '{$full_context}%'
OR (`id_context` LIKE '{$id_context}' AND `context` = '{$context}')
PQR;

		$r = $this->db->query($q);

		return $r;
	}

	/**
	 * Update topic timeline to new context recently moved
	 *
	 * @param string $id_topic
	 * @param string $from_context
	 * @param string $to_context
	 */
	public function move_topic($id_topic, $from_context, $to_context)
	{
		$table = 'timeline_' . $this->session->userdata('current_account');

		$q = <<<PQR
update `{$table}`
set context = replace(context, "{$from_context}", "{$to_context}") WHERE id_topic = '_{$id_topic}'
PQR;
		Plogger::log($q);

		return $this->db->query($q, FALSE);
	}

	/**
	 * Remove timeline for topic
	 *
	 * @param string $id_topic
	 * @param string $context
	 *
	 * @return boolean
	 */
	public function delete_topic($id_topic, $context)
	{
		$table = 'timeline_' . $this->session->userdata('current_account');

		$q = <<<PQR
DELETE FROM `{$table}`
WHERE `id_topic` = '_{$id_topic}' AND `context` = '{$context}'
PQR;

		$r = $this->db->query($q);

		return $r;
	}

	/**
	 * Change topic title in timeline.
	 *
	 * @param string $id_topic
	 * @param string $new_title
	 *
	 * @return boolean
	 */
	public function modify_topic($id_topic, $new_title)
	{
		$table = 'timeline_' . $this->session->userdata('current_account');

		return $this->db->where('id_topic', '_' . $id_topic)->update($table, array('title' => $new_title));
	}

}