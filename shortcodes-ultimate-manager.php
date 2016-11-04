<?php

/*
 Plugin Name: Shortcodes Ultimate Manager

 Description: For shortcode utility manager

 Version: 0.0.1

 Author: Insu Mun

 License: GPLv2
 */

//avoid direct calls to this file
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class ShortcodesUltimateManager
{
    /**
     * ShortcodesUltimateManager constructor.
     */
    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'tsm_init']);
        add_action('admin_menu', [$this, 'display_tsm_menu']);
        add_action('admin_post_tsm_click_hook', [$this, 'process_tsm_click']);
        add_action('admin_head', [$this, 'set_styles']);
    }

    public function tsm_init()
    {
        if (!class_exists('Su_Data')) {
            add_action('admin_notices', function() {
                echo '<div class="error">Please install and active Shortcodes Ultimate plugin</div>';
                exit();
            });
        }
    }

    public function set_styles()
    {
        $currentScreen = get_current_screen();
        if ($currentScreen->id !== 'settings_page_display_tsm') {
            echo '<style type="text/css">';
            echo get_option('tsm_styles');
            echo '</style>';
        }
    }

    public function display_tsm_menu()
    {
        add_options_page('Set Shortcode Manager', 'Shorcode Manager Menu', 'administrator', 'display_tsm', [$this, 'display_tsm_html_page']);
    }

    public function process_tsm_click()
    {
        if (isset($_POST['suItems'])) {
            $txt = '';
            $items = $_POST['suItems'];
            foreach ($items as $item) {
                $txt .= "#su-generator-choices > span[data-shortcode=" . $item . "]{display:none !important;}";
            }

            update_option('tsm_styles', $txt);
            update_option('tsm_lastItems', $items);

            wp_redirect(admin_url('options-general.php?page=display_tsm'));
        } else {
            delete_option('tsm_styles');
            delete_option('tsm_lastItems');
            wp_redirect(admin_url('options-general.php?page=display_tsm'));
        }
    }

    public function display_tsm_html_page()
    {
        $shortcodes = (array)Su_Data::shortcodes();
        if (!empty($shortcodes)) { ?>
            <style>
                .shortcode-manager-form > label {
                    position           : relative;
                    display            : block;
                    width              : 20%;
                    height             : 28px;
                    min-width          : 130px;
                    padding            : 0 5px 0 30px;
                    float              : left;
                    overflow           : hidden;
                    -webkit-box-sizing : border-box;
                    -moz-box-sizing    : border-box;
                    box-sizing         : border-box;
                    border-bottom      : 1px dotted #e5e5e5;
                    color              : #222;
                    vertical-align     : top;
                    text-align         : left;
                    line-height        : 28px;
                    cursor             : pointer;
                }

                .shortcode-manager-form > label:before {
                    clear : both;
                }
            </style>
            <div class="wrap">
                <h2>Shortcode Ultimate Manager</h2>
                <h3 style="color:red;">Please check the shortcode items you want to hide in user interface</h3>
                <form class="shortcode-manager-form" method="post" action="admin-post.php">
                    <input type="hidden" name="action" value="tsm_click_hook"/>
                    <?php
                    $items = get_option('tsm_lastItems');
                    if (!empty($items)) {
                        foreach ($shortcodes as $name => $shortcode) {
                            echo '<label>';
                            if (in_array($name, $items)) {
                                echo '<input type="checkbox" class="checkbox" name="suItems[]" value="' . $name . '" id="' . $name . '" checked/>';
                            } else {
                                echo '<input type="checkbox" class="checkbox" name="suItems[]" value="' . $name . '" id="' . $name . '"/>';
                            }
                            $icon = (isset($shortcode['icon'])) ? $shortcode['icon'] : 'puzzle-piece';
                            $shortcode['name'] = (isset($shortcode['name'])) ? $shortcode['name'] : $name;
                            echo '<span data-name="' . $shortcode['name'] . '" data-shortcode="' . $name . '" title="' . esc_attr($shortcode['desc']) . '" data-desc="' . esc_attr($shortcode['desc']) . '" data-group="' . $shortcode['group'] . '">' . Su_Tools::icon($icon) . $shortcode['name'] . '</span>' . "\n";
                            echo '</label>';
                        }
                    } else {
                        foreach ($shortcodes as $name => $shortcode) {
                            echo '<label>';
                            echo '<input type="checkbox" name="suItems[]" value="' . $name . '" id="' . $name . '" class="tsm_items"/>';
                            $icon = (isset($shortcode['icon'])) ? $shortcode['icon'] : 'puzzle-piece';
                            $shortcode['name'] = (isset($shortcode['name'])) ? $shortcode['name'] : $name;
                            echo '<span data-name="' . $shortcode['name'] . '" data-shortcode="' . $name . '" title="' . esc_attr($shortcode['desc']) . '" data-desc="' . esc_attr($shortcode['desc']) . '" data-group="' . $shortcode['group'] . '">' . Su_Tools::icon($icon) . $shortcode['name'] . '</span>' . "\n";
                            echo '</label>';
                        }
                    }
                    ?>
                    <div class="clear"></div>
                    <input type="submit" class="button button-primary button-large" value="Submit" id="tsm_click"/>
                </form>
            </div>
            <?php
        } else {

        }
    }
}

$tsm = new ShortcodesUltimateManager();
?>