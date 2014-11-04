<?php

/**
 * Manage due dates
 *
 * @author Cristian Riffo <criffoh at gmail.com>
 */
class due extends MX_Controller {

	/**
	 * Return date range to use it in due line
	 * 
	 * @param string $middle_date YYY-MM-DD
	 * @param integer $days_range
	 * 
	 * @return array
	 */
	public function date_range($middle_date, $days_range = 7)
	{
		$result = array();

		for ($i = -$days_range; $i <= $days_range; $i ++) {
			$result[] = date('Y-m-d', strtotime($middle_date . ' +' . $i . ' days'));
		}

		return array_flip($result);
	}

	/**
	 * Get due line data
	 *
	 * @param string $middle_date YYYY-MM-DD
	 * @param integer $days_range
	 *
	 * @return array
	 */
	public function line_range($middle_date = NULL, $days_range = 7)
	{
		$this->load->model('m_due');

		$middle_date = is_null($middle_date) ? date('Y-m-d') : $middle_date;
		$due_dates = $this->m_due->range($middle_date, $days_range);
		$date_range = $this->date_range($middle_date, $days_range);

		foreach ($date_range as $date => $v) {
			if (array_key_exists($date, $due_dates)) {
				$date_range[$date] = $due_dates[$date];
			}
		}

		return $date_range;
	}

	/**
	 * Generates top date line of account.
	 *
	 * @param string $middle_date YYYY-MM-DD
	 * @param integer $days_range
	 * @param boolean $only_opened Shows only opened tasks
	 *
	 * @return string HTML result
	 */
	public function date_line($middle_date = NULL, $days_range = 7, $only_opened = FALSE)
	{
		$middle_date = is_null($middle_date) ? date('Y-m-d') : $middle_date;

		$data = array(
				'line_range' => $this->line_range($middle_date, $days_range),
				'middle_date' => $middle_date,
				'only_opened' => $only_opened,
		);

		return $this->load->view('date_line.phtml', $data, TRUE);
	}

}
