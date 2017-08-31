<?php
/*
Plugin Name: Videojs Player
Version: 1.9.0
Plugin URI: https://github.com/videojs/video.js
Author: Hushino
Author URI: https://github.com/hushino
Description: Easily embed videos using videojs player
Text Domain: videojs-player
Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('VIDEOJS_PLAYER')) {

    class VIDEOJS_PLAYER {

        var $plugin_version = '1.9.0';

        function __construct() {
            define('VIDEOJS_PLAYER_VERSION', $this->plugin_version);
            $this->plugin_includes();
        }

        function plugin_includes() {
            if (is_admin()) {
                add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
            }
            add_action('plugins_loaded', array($this, 'plugins_loaded_handler'));
            add_action('wp_enqueue_scripts', 'videojs_player_enqueue_scripts');
            add_action('admin_menu', array($this, 'add_options_menu'));
            add_action('wp_head', 'videojs_player_header');
            add_shortcode('videojs_video', 'videojs_video_embed_handler');
            //allows shortcode execution in the widget, excerpt and content
            add_filter('widget_text', 'do_shortcode');
            add_filter('the_excerpt', 'do_shortcode', 11);
            add_filter('the_content', 'do_shortcode', 11);
        }

        function plugin_url() {
            if ($this->plugin_url)
                return $this->plugin_url;
            return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
        }

        function plugin_action_links($links, $file) {
            if ($file == plugin_basename(dirname(__FILE__) . '/videojs-player.php')) {
                $links[] = '<a href="options-general.php?page=videojs-player-settings">'.__('Settings', 'videojs-player').'</a>';
            }
            return $links;
        }
        
        function plugins_loaded_handler()
        {
            load_plugin_textdomain('videojs-player', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/'); 
        }

        function add_options_menu() {
            if (is_admin()) {
                add_options_page(__('Videojs Settings', 'videojs-player'), __('Videojs Player', 'videojs-player'), 'manage_options', 'videojs-player-settings', array($this, 'options_page'));
            }
        }

        function options_page() {
            $url = "https://github.com/hushino";
            $link_text = sprintf(wp_kses(__('For documentation and problems please visit the plugin creator update homepage <a target="_blank" href="%s">here</a>.', 'videojs-player'), array('a' => array('href' => array(), 'target' => array()))), esc_url($url));
            ?>
            <div class="wrap"><h2>Videojs Player - v<?php echo $this->plugin_version; ?></h2>
            <div class="update-nag"><?php echo $link_text;?></div>
            </div>
            <?php
        }

    }

    $GLOBALS['easy_video_player'] = new VIDEOJS_PLAYER();
}

function videojs_player_enqueue_scripts() {
    if (!is_admin()) {
        $plugin_url = plugins_url('', __FILE__);
        wp_enqueue_script('jquery');
        wp_register_script('videojs', $plugin_url . '/videojs/video.js', array('jquery'), VIDEOJS_PLAYER_VERSION, true);
        wp_enqueue_script('videojs');
        wp_register_style('videojs', $plugin_url . '/videojs/video-js.css');
        wp_enqueue_style('videojs');
        wp_register_style('videojs-style', $plugin_url . '/videojs-player.css');
        wp_enqueue_style('videojs-style');
    }
}

function videojs_player_header() {
    if (!is_admin()) {
        $config = '<!-- This site is embedding videos using the Videojs Player plugin v' . VIDEOJS_PLAYER_VERSION . ' - http://wphowto.net/videojs-player-for-wordpress-757 -->';
        echo $config;
    }
}

function videojs_video_embed_handler($atts) {
    extract(shortcode_atts(array(
        'url' => '',
        'webm' => '',
        'ogv' => '',
        'width' => '',
        'controls' => '',
        'preload' => 'auto',
        'autoplay' => 'false',
        'loop' => '',
        'muted' => '',
        'poster' => '',
        'class' => '',
    ), $atts));
    if(empty($url)){
        return __('you need to specify the src of the video file', 'videojs-player');
    }
    //src
    $src = '<source src="'.$url.'" type="video/mp4" />';
    if (!empty($webm)) {
        $webm = '<source src="'.$webm.'" type="video/webm" />';
        $src = $src.$webm; 
    }
    if (!empty($ogv)) {
        $ogv = '<source src="'.$ogv.'" type="video/ogg" />';
        $src = $src.$ogv; 
    }
    //controls
    if($controls == "false") {
        $controls = "";
    } 
    else{
        $controls = " controls";
    }
    //preload
    if($preload == "metadata") {
        $preload = ' preload="metadata"';
    }
    else if($preload == "none") {
        $preload = ' preload="none"';
    }
    else{
        $preload = ' preload="auto"';
    }
    //autoplay
    if($autoplay == "true"){
        $autoplay = " autoplay";
    }
    else{
        $autoplay = "";
    }
    //loop
    if($loop == "true"){
        $loop = " loop";
    }
    else{
        $loop = "";
    }
    //muted
    if($muted == "true"){
        $muted = " muted";
    }
    else{
        $muted = "";
    }
    //poster
    if(!empty($poster)) {
        $poster = ' poster="'.$poster.'"';
    }
    $player = "videojs" . uniqid();
    //custom style
    $style = '';   
    if(!empty($width)){
        $style = <<<EOT
        <style>
        #$player {
            max-width:{$width}px;   
        }
        </style>
EOT;
        
    }
    $output = <<<EOT
    <video id="$player" class="video-js vjs-default-skin"{$controls}{$preload}{$autoplay}{$loop}{$muted}{$poster} data-setup='{"fluid": true}'>
        $src
    </video>
    $style
EOT;
    return $output;
}
