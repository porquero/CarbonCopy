<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Overwrite for html:link_tag helper
 *
 * @tutorial Prepares files for href to be used in link_tag, adding modification time for force reload
 * asset when it changes.<br>
 * Example: main.css -> main.css?1323185922
 *
 * @param	mixed	stylesheet hrefs or an array
 * @param	string	rel
 * @param	string	type
 * @param	string	title
 * @param	string	media
 * @param	boolean	should index_page be added to the css path
 *
 * @return string
 */
function link_tag($href = '', $rel = 'stylesheet', $type = 'text/css', $title = '', $media = '', $index_page = FALSE) {

    $CI = & get_instance();

    $link = '<link ';

    if (is_array($href)) {
        foreach ($href as $k => $v) {
            if (!is_file($v)) {
                return '';
            }
            if ($k == 'href' AND strpos($v, '://') === FALSE) {
                if ($index_page === TRUE) {
                    $link .= 'href="' . $CI->config->site_url($v . '?' . filemtime($v)) . '" ';
                } else {
                    $link .= 'href="' . $CI->config->slash_item('base_url') . $v . '?' . filemtime($v) . '" ';
                }
            } else {
                $link .= $k . '="' . $v . '" ';
            }
        }

        $link .= "/>\n";
    } else {
        if (!is_file($href)) {
            return '';
        }
        if (strpos($href, '://') !== FALSE) {
            $link .= 'href="' . $href . '" ';
        } elseif ($index_page === TRUE) {
            $link .= 'href="' . $CI->config->site_url($href . '?' . filemtime($href)) . '" ';
        } else {
            $link .= 'href="' . $CI->config->slash_item('base_url') . $href . '?' . filemtime($href) . '" ';
        }

        $link .= 'rel="' . $rel . '" type="' . $type . '" ';

        if ($media != '') {
            $link .= 'media="' . $media . '" ';
        }

        if ($title != '') {
            $link .= 'title="' . $title . '" ';
        }

        $link .= "/>\n";
    }

    return $link;
}

/**
 * Return script tag for path file sent, adding file modification time to reload adding modification time
 * for force reload asset when it changes
 *
 * @param string $src javascript file path
 *
 * @return string
 */
function js_tag($src) {
    $CI = & get_instance();
    if (!is_file($src)) {
        return '';
    }
    if (strpos($src, '://') === FALSE) {
        $src = $CI->config->slash_item('base_url') . $src . '?' . filemtime($src);
    }
    return '<script src="' . $src . '"></script>' . "\n";
}
