<?php

/**
 * Manage cron queries
 *
 * @package CarbonCopy
 * @subpackage cc
 * @author Cristian Riffo <criffoh at gmail.com>
 */
class m_cron extends CI_Model {

    /**
     * Participation types to make query.
     *
     * @var array
     */
    private $_action_type = array(
        'new' => '= 1',
        'participation' => '!= 1',
    );

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
        $this->_table = $this->session->userdata('current_account') . '_timeline';
    }

    /**
     * Return list of activities
     * 
     * @param string $participant username
     * @param string $interval DAY|WEEK
     * @param string $type from|to|all
     * 
     * @return object
     */
    public function get_activity($participant, $interval = 'DAY', $type = 'from')
    {
        $date_format = account_date_format_mysql();
        $participation = "from_participant = '{$participant}'";
        if ($type === 'to') {
            $participation = "to_participant = '{$participant}' AND from_participant != '{$participant}'";
        } elseif($type === 'all'){
            $participation = 1;
        }

        $q = <<<PQR
SELECT DISTINCT date_format(t.ts, '{$date_format}') ts, date(t.ts) sql_date, t.context, 
t.from_participant, t.to_participant, t.title, t.id_topic, a.name, t.id_context
FROM `cc_timeline` t
JOIN action a ON t.action_id = a.id
WHERE t.ts BETWEEN date_sub(NOW(), INTERVAL 1 {$interval}) AND NOW()
AND {$participation}
ORDER BY t.id DESC
PQR;

        $r = $this->db->query($q);

        return $r->result();
    }

}
