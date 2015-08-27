<?php

if ( ! defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * File read methods
 *
 * @package CarbonCopy
 * @subpackage file
 * @author porquero
 */
class read extends MX_Controller {

    /**
     * Get list of directories from path
     *
     * @param string $context
     * @return mixed
     */
    public function directories($context)
    {
        return $this->listing($context);
    }

    /**
     * Get list of files from path
     *
     * @param string $context
     * @return mixed
     */
    public function files($context)
    {
        return $this->listing($context, 'file');
    }

    /**
     * Get list of files/directories from path
     *
     * @param string $context
     * @return mixed
     */
    public function listing($context, $type = 'dir')
    {
        $this->load->helper('array');
        $context = Modules::run('file/misc/final_slash', $context);
        if (file_exists($context) === TRUE && is_dir($context) === TRUE) {
            // TODO: Al parecer no funciona el orden por modificación!
            foreach (glob($context . '*') as $filename) {
                if ($type == 'dir') {
                    if (is_dir($filename) === TRUE) {
                        $file_array[][filemtime($filename)] = preg_replace('/\/$/', '', $filename);
                    }
                }
                else {
                    if (is_file($filename) === TRUE) {
                        $file_array[][filemtime($filename)] = $filename;
                    }
                }
            }

            if (isset($file_array)) {
                uasort($file_array, 'cmp');

                foreach ($file_array as $k => $context) {
                    $context = array_values($context);
                    $result[] = $context[0];
                }

                return $result;
            }

            return array();
        }
        else {
            return array();
        }
    }

    /**
     * Get file content
     *
     * @param string $file_path
     * @return mixed
     */
    public function content($file_path)
    {
        if (file_exists($file_path) === TRUE && is_file($file_path) === TRUE) {
            $this->load->helper('file');

            return read_file($file_path);
        }
        else {
            $msg = 'File ' . $file_path . ' doesn\'t exists!';
            Plogger::log($msg);
            echo $msg . '<br />';

            return FALSE;
        }
    }

    /**
     * Get Json content and return an array with result.
     * 
     * @param string $file_path
     * @return array
     */
    public function json_content($file_path)
    {
        $content = $this->content($file_path);
        return json_decode($content, TRUE);
    }

    /**
     * Get file csv content  and return string in html format.
     *
     * @todo Esto debe encapsularse, es decir, crear una clase para obtener datos csv línea por línea.
     * @param string $file_handle
     * @param string $context
     */
    public function csv_content_to_html($file_handle, $context)
    {
        $file_handle = fopen(_INC_ROOT . $file_handle, "r");

        if ($file_handle === FALSE) {
            return "Topic doesn't exists";
        }

        $result = '';

        while ( ! feof($file_handle)) {
            $file_attach = '';

            $data = fgetcsv($file_handle);
            if (strlen($data[1]) === 0) {
                continue;
            }

            $date = account_date_format($data[0], TRUE);
            if (strlen($data[3]) > 0) {
                $file_attach = '<div class="fatach"><b>' . lang('file_attached') . '</b>: <a href="' . site_url() . '/cc/file/download/' . $context . '_' . urlencode($data[3]) . '" target="_blank">' . $data[3] . '</a></div>';
            }

            $link_profile = site_url('account/participant/profile/' . $data[1]);
            $reply = <<<PQR
<li>
    <div>
        <a href="{$link_profile}" class="usr">{$data[1]}</a>
        <span>{$date}</span>
        <div class="p">{$data[2]}</div>
        {$file_attach}
        <span class="clear"></span>
    </div>
</li>
PQR;

            $result = $reply . $result;
        }

        fclose($file_handle);

        return $result;
    }

    /**
     * Get basename for files listed
     *
     * @param array $context
     * @return array Basename files
     */
    public function files_basename($context)
    {
        $list_files = $this->files($context);
        array_walk($list_files, 'file_basename');

        return $list_files;
    }

}
