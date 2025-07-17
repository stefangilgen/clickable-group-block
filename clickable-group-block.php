<?php
/**
 * Plugin Name: Clickable Group Block
 * Plugin URI: https://github.com/stefangilgen/clickable-group-block
 * Description: Macht WordPress Gruppen-Blöcke klickbar und fügt elegante Hover-Effekte hinzu
 * Version: 1.0.0
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: Blitz & Donner Medienzauber und Kommunikationsberatung AG
 * Author URI: https://blitzdonner.ch
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: clickable-group-block
 * Domain Path: /languages
 */

// Verhindere direkten Zugriff
if (!defined('ABSPATH')) {
    exit;
}

// Definiere Konstanten
define('CGB_VERSION', '1.0.0');
define('CGB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CGB_PLUGIN_URL', plugin_dir_url(__FILE__));

// Registriere Block Style
function cgb_register_block_styles() {
    register_block_style(
        'core/group',
        array(
            'name'  => 'hover-group',
            'label' => __('Hover Gruppe (Link)', 'clickable-group-block'),
        )
    );
}
add_action('init', 'cgb_register_block_styles');

// Wrapper für klickbare Gruppe
function cgb_wrap_hover_group($block_content, $block) {
    if ($block['blockName'] === 'core/group' && 
        isset($block['attrs']['className']) && 
        strpos($block['attrs']['className'], 'is-style-hover-group') !== false) {
        
        preg_match('/<a[^>]+href=([\'"])(.*?)\1/', $block_content, $matches);
        if (!empty($matches[2])) {
            $url = esc_url($matches[2]);
            $escaped_url = preg_quote($url, '/');
            
            $content = preg_replace('/<a[^>]+href=([\'"])'.$escaped_url.'\1[^>]*>(.*?)<\/a>/', '$2', $block_content, 1);
            
            // Füge ARIA-Attribute für bessere Barrierefreiheit hinzu
            return sprintf(
                '<a href="%s" class="cgb-hover-group" style="display:block; text-decoration:none; color:inherit;" role="link" aria-label="%s">%s</a>',
                $url,
                esc_attr(wp_strip_all_tags($content)), // Beschreibender Text für Screenreader
                $content
            );
        }
    }
    return $block_content;
}
add_filter('render_block', 'cgb_wrap_hover_group', 10, 2);

// Lade Styles
function cgb_enqueue_styles() {
    wp_enqueue_style(
        'clickable-group-block',
        CGB_PLUGIN_URL . 'assets/css/style.css',
        array(),
        CGB_VERSION
    );
}
add_action('wp_enqueue_scripts', 'cgb_enqueue_styles');

// Lade Übersetzungen
function cgb_load_textdomain() {
    load_plugin_textdomain(
        'clickable-group-block',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}
add_action('init', 'cgb_load_textdomain');

// Aktivierungshook
register_activation_hook(__FILE__, 'cgb_activate');
function cgb_activate() {
    // Prüfe WordPress-Version
    if (version_compare(get_bloginfo('version'), '5.9', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('Dieses Plugin benötigt WordPress 5.9 oder höher!', 'clickable-group-block'));
    }
} 
