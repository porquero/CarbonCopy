<?php

/**
 * Make multifile array input complaint with CI_Upload.<br>
 * For use files[ ] input name you must use it method.
 * 
 * @author porquero
 * 
 * @example
 * In Controller<br>
 * $this->load->helper('upload');<br>
 * multifile_array();<br>
 * foreach ($_FILES as $file => $file_data) {<br>
 *    $this->upload->do_upload($file);
 * ...
 */
function multifile_array()
{
    if(count($_FILES) == 0)
        return;
    
    $files = array();
    $all_files = $_FILES['f_file']['name'];
    $i = 0;
    
    foreach ($all_files as $filename) {
        $files[++$i]['name'] = $filename;
        $files[$i]['type'] = current($_FILES['f_file']['type']);
        next($_FILES['f_file']['type']);
        $files[$i]['tmp_name'] = current($_FILES['f_file']['tmp_name']);
        next($_FILES['f_file']['tmp_name']);
        $files[$i]['error'] = current($_FILES['f_file']['error']);
        next($_FILES['f_file']['error']);
        $files[$i]['size'] = current($_FILES['f_file']['size']);
        next($_FILES['f_file']['size']);
    }

    $_FILES = $files;

}

