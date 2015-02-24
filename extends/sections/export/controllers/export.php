<?php

/**
 * Export all topics for current context.
 */
class example extends section {

    /**
     * Export button
     * 
     */
    public function index() {
        $this->load->module('file/read');

        $topics = glob_recursive(_INC_ROOT . context_real_path($context) . "/info_topic.json");
        $temp = tmpfile();
        fputcsv($temp, array_keys($this->read->json_content($topics[0])), ";", '"');

        foreach ($topics as $topic) {
            $topic_content = array_values($this->read->json_content($topic));
            fputcsv($temp, $topic_content, ";", '"');
        }

        fseek($temp, 0);
        $metaDatas = stream_get_meta_data($temp);
        $tmpFilename = $metaDatas['uri'];
        $return = fread($temp, filesize($tmpFilename));

        fclose($temp);
        return $return;
    }

}
