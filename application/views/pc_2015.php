<?php
$current_account = current_account_info();
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo @$title ?> - CarbonCopy</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php
        echo
        link_tag('pub/css/reset.css')
        . link_tag('pub/css/font-awesome.css')
        . link_tag('pub/css/ui-lightness/jquery-ui-1.10.3.custom.min.css')
//        . link_tag('pub/js/jquery-custom-scrollbar/jquery.custom-scrollbar.css')
        . link_tag('pub/formalize/css/formalize.css')
        . link_tag('pub/pc_2015/css/main.css')
        . @$head . @$_head;
        ?>
        <script type="text/javascript">
            var site_url = '<?php echo preg_replace('/\/$/', '', site_url()); ?>/';
            var base_url = '<?php echo final_slash(base_url()) ?>';
        </script>
    </head>
    <body>
        <div id="delegate" class="container">
            <header>
                <div class="flexc lt">
                    <div id="cc">
                        <div>
                            <a href="<?php echo site_url() ?>" title="CarbonCopy - Collaboration Manager"><span>CC</span></a>
                            <h2>CarbonCopy</h2>
                        </div>
                    </div>
                    <?php
                    if (connected_user() !== NULL) {
                        echo Modules::run('cc/due/date_line', preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $this->uri->segment(5)) === 1 ? $this->uri->segment(5) : date('Y-m-d'), 15, TRUE);
                    }
                    ?>
                </div>
                <div class="flexc sn">
                    <?php
                    if (connected_user() !== NULL):
                        ?>
                        <div id="search-box">
                            <input type="text" name="seach" id="search" placeholder="Search" tabindex="1" />
                            <div id="search_results"></div>
                        </div>
                    <?php endif; ?>
                    <nav>
                        <ul>
                            <li><a href="<?php echo site_url() ?>"><?php echo lang('home') ?></a></li>
                            <?php if (NULL !== connected_user()): ?>
                                <li><a href="<?php echo site_url('/account/participant/config_form') ?>"><?php echo connected_user() /* . lang('connected_in') . $current_account['name'] */ ?></a></li>
                                <li><a href="<?php echo site_url('/account/participant/all_people') ?>"><?php echo lang('all_people') ?></a></li>
                                <?php
                                if (is_administrator()) {
                                    echo '<li><a href="' . site_url('account/manage/config_form') . '">config</a></li>';
                                }
                                ?>
                                <li><a href="<?php echo site_url('/cc/user/logout') ?>"><?php echo lang('logout') ?></a></li>
                            <?php else: ?>
                                <!--<li><a href="<?php echo site_url('/cc/user/register_form') ?>"><?php echo lang('register') ?></a></li>-->
                                <li><a href="<?php echo site_url('/cc/user/login_form') ?>"><?php echo lang('login') ?></a></li>
<?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </header>
            <div class="content">
                <section>
                    <div class="pad">
                        <?php
                        echo @$_sidebar;
                        echo list_enabled_components();
                        echo @$_section_sidebar;
                        ?>
                    </div>
                </section>
                <article>
                    <div id="context_breadcrumb">
                        <div class="pad">
                            <a href="<?php echo site_url() ?>">[/]</a><?php echo ! is_null($breadcrumb) ? ' &raquo; ' . $breadcrumb : null; ?>
                        </div>
                    </div>
                    <p id="<?php echo @$msg_type; ?>" class="msg"><?php echo @$msg; ?></p>
                    <div class="pad">
                        <?php
                        echo '<h1>' . @ucfirst($title) . '</h1>';
                        echo '<div id="subtitle">' . @ucfirst($subtitle) . '</div>';
                        echo @$_section_before_content;
                        echo '<div class="description">' . @ucfirst($description) . '</div>';
                        echo @$_section_after_content;
                        echo @$_view;
                        ?>
                    </div>
                </article>
                    <?php if (isset($_aside)): ?>
                    <aside>
                    <?php echo $_aside; ?>
                    </aside>
<?php endif; ?>
            </div>
        </div>
        <div id="aggressive_message"><div><span><?php echo @isset($aggressive_message) ? $aggressive_message : lang('wait_please'); ?></span> <img src="<?php echo base_url('pub/pc_2015/img/ajax-loader.gif') ?>" alt="" /></div></div>
        <ul id="shortcuts"></ul>
        <?php
        echo
        js_tag('pub/js/jquery-1.9.1.js')
        . js_tag('pub/js/main.js')
        . js_tag('pub/pc_2015/js/main.js')
        . js_tag('pub/js/jquery-ui-1.10.3.custom.min.js')
        . js_tag('pub/js/jquery.form.js')
        . js_tag('pub/js/jquery.hotkeys/jquery.hotkeys.latam.js')
//        . js_tag('pub/js/jquery-custom-scrollbar/jquery.custom-scrollbar.js')
        . @$footer;
        ?>
    </body>
</html>
