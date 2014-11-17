<?php

/**
 * Manage due table
 *
 * @package CarbonCopy
 * @subpackage cc
 * @author Cristian Riffo <criffoh at gmail.com>
 */
class m_due extends CI_Model {

	/**
	 * Due account table
	 *
	 * @var string
	 */
	private $_table;

	public function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('default', TRUE);
		$this->_table = $this->session->userdata('current_account') . '_due';
	}

	/**
	 * Create new timeline table for new account.
	 * 
	 * @param type $accoun_id
	 */
	public function create($accoun_id)
	{
		return $this->db->query("create table {$accoun_id}_due like " . $this->_table);
	}

	/**
	 * Get topic with due date between sent parameters.
	 * 
	 * @param string $middle_date YYYY-MM-DD
	 * @param integer $days_range
	 * 
	 * @return array
	 */
	public function range($middle_date, $days_range = 7)
	{
		$q = <<<PQR
SELECT * 
FROM  `{$this->_table}` 
WHERE  `date` 
BETWEEN  '{$middle_date}' - INTERVAL {$days_range} DAY
AND  '{$middle_date}' + INTERVAL {$days_range} DAY
PQR;

		$r = $this->db->query($q);
		$result = array();

		foreach ($r->result() as $topic) {
			$result[$topic->date][] = $topic->topic_context;
		}

		return $result;
	}

	/**
	 * Add new topic due date.
	 *
	 * @param string $topic_context slugged topic path
	 * @param string $date YYY-MM-DD
	 *
	 * @return boolean
	 */
	public function add($topic_context, $date)
	{
		return $this->db->insert($this->_table, array(
					'topic_context' => $topic_context,
					'date' => $date,
		));
	}

	/**
	 * Delete topic due date.
	 * 
	 * @param string $topic_context slugged topic path
	 * 
	 * @return boolean
	 */
	public function delete_topic($topic_context)
	{
		return $this->db->where('topic_context', $topic_context)->delete($this->_table);
	}

	/**
	 * Delete all topics due date for context.
	 *
	 * @param string $context slugged topic path
	 *
	 * @return boolean
	 */
	public function delete_context($context)
	{
		return $this->db->where('topic_context like ', trim($context, '_') . '_%')->delete($this->_table);
	}

	/**
	 * Validate if topic has a due date.
	 * 
	 * @param string $topic_context
	 * 
	 * @return boolean
	 */
	public function exists($topic_context)
	{
		return $this->db->from($this->_table)->where('topic_context', $topic_context)->count_all_results() > 0;
	}

	/**
	 * Change topic due date.
	 * 
	 * @param string $topic_context slugged topic path
	 * @param string $new_date YYYY-MM-DD
	 * 
	 * @return boolean
	 */
	public function change($topic_context, $new_date)
	{
		if ($this->exists($topic_context)) {
			return $this->db->where('topic_context', $topic_context)
					->update($this->_table, array('date' => $new_date));
		}
		else {
			return $this->add($topic_context, $new_date);
		}
	}

	/**
	 * Change topic context when is moved to another one.
	 *
	 * @param string $topic_context_from
	 * @param string $topic_context_to
	 *
	 * @return boolean
	 */
	public function move($topic_context_from, $topic_context_to)
	{
		return $this->db->where('topic_context', $topic_context_from)
				->update($this->_table, array('topic_context' => $topic_context_to));
	}

	/**
	 * Return topics for specific date
	 * 
	 * @param string $date YYYY-MM-DD
	 * 
	 * @return array
	 */
	public function for_day($date = NULL)
	{
		$date = is_null($date) ? date('Y-m-d') : $date;

		return $this->range($date, 0);
	}

}
