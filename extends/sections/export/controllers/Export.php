<?php

/**
 * Export all topics for current context.
 */
class export extends section {

    /**
     * Export butotn
     *
     */
    public function index() {
        // Only html!
    }

    /**
     * Export topics context
     *
     */
    public function download($context) {
        $this->load->module('file/read');
        $this->load->helper('download');

        $topics = glob_recursive(_INC_ROOT . context_real_path($context) . "/info_topic.json");
        $temp = tmpfile();
        fputcsv($temp, array_merge(array_keys($this->read->json_content($topics[0])), array('context')), ";", '"');

        foreach ($topics as $topic) {
            $topic_content = array_merge(array_values($this->read->json_content($topic)), (array) parent_context(slug_full_topic_path($topic)));
            fputcsv($temp, $topic_content, ";", '"');
        }

        fseek($temp, 0);
        $metaDatas = stream_get_meta_data($temp);
        $tmpFilename = $metaDatas['uri'];

        force_download($context . '_topics.csv', fread($temp, filesize($tmpFilename)));

        fclose($temp);
    }

}
