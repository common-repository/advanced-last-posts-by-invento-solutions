<?php

/*
  Plugin Name: Advanced Last Posts by Invento Solutions
  Description: Advanced last posts with widget and shortcodes
  Version: 1.3  
  Author: Invento Solutions
  Author URI: http://invento.bg/
  License: GPLv2
 */


class Last_Posts {

    function __construct() {
        add_action('init', array($this, 'reg_to_admin'));
        add_action('init', array($this, 'register_taxonomy_lp'));
        add_action('init', array($this, 'register_shortcode'));
        add_action('admin_menu', array($this, 'register_option_page'));
        add_action('admin_menu', array($this, 'register_help_page'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_widget_jquery'));
        $this->lp_register_widget_area();
    }

    public function reg_to_admin() {
        register_post_type('last-posts', array(
            'labels' => array(
                'name' => __('Last Posts', 'lp'),
                'singular_name' => __('Last Posts', 'lp'),
                'add_new' => _x('Add New', 'pluginbase', 'lp'),
                'add_new_item' => __('Add New Post', 'lp'),
                'edit_item' => __('Edit Post', 'lp'),
                'new_item' => __('New Post', 'lp'),
                'view_item' => __('View Post', 'lp'),
                'search_items' => __('Search Posts', 'lp'),
                'not_found' => __('There is no posts', 'lp'),
                'not_found_in_trash' => __('There is no posts in Trash', 'lp'),
                'supports' => array( 'title', 'author', 'revisions' )
                
            ),           
            'menu_icon' =>  plugins_url( 'invento-logo.png' , __FILE__ ),
            'description' => __('Last Posts', 'lp'),
            'public' => true,
            'publicly_queryable' => true,
            'query_var' => true,
            'rewrite' => true,
            'exclude_from_search' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 40,
            'supports' => array(
                'title',
                'thumbnail',
                'editor',
                'author',
                'tags',
                'custom-fields',
                'page-attributes',
            ),
        ));
    }

    public function register_taxonomy_lp() {

        register_taxonomy('last-posts-tax', array('last-posts'), array(
            'hierarchical' => true,
            'labels' => array(
                'name' => _x('Categories', 'taxonomy general name', 'lp'),
                'singular_name' => _x('Last Post', 'taxonomy singular name', 'lp'),
                'search_items' => __('Search Last Posts', 'lp'),
                'popular_items' => __('Popular Last Posts', 'lp'),
                'all_items' => __('All Last Posts', 'lp'),
                'parent_item' => null,
                'parent_item_colon' => null,
                'edit_item' => __('Edit Last Post', 'lp'),
                'update_item' => __('Update Last Post', 'lp'),
                'add_new_item' => __('Add New Last Post', 'lp'),
                'new_item_name' => __('New Last Post Name', 'lp'),
                'separate_items_with_commas' => __('Separate Last Posts with commas', 'lp'),
                'add_or_remove_items' => __('Add or remove Last Post', 'lp'),
                'choose_from_most_used' => __('Choose from the most used Last Post', 'lp')
            ),
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => true,
        ));
        register_taxonomy_for_object_type('last-posts-tax', 'last-posts-tax');
    }

    public function register_shortcode() {

        add_shortcode('inventolp', array($this, 'display_shortcode'));
    }

    public function display_shortcode($atts, $content, $order, $count = '') {
        extract(shortcode_atts(array(
            'cat' => "$content",
            'order' => "$order",
            'count' => $count
                        ), $atts));
        $this->display_posts("{$cat}", "{$order}", "{$count}");
    }

    public function display_posts($content, $order, $count) {
        $lp_option = get_option('lp_option');
        if ($lp_option['use_information'] == 'from_plugin') {
            $this->display_information_from_plugin($content, $order, $count);
        } else {
            $this->display_information_from_posts($content, $order, $count);
        }
    }

    public function display_information_from_plugin($content, $order, $count) {
        $tax_query_array = '';
        if (!empty($content)) {
            $tax_query_array = array(
                array(
                    'taxonomy' => 'last-posts-tax',
                    'field' => 'slug',
                    'terms' => $content
                )
            );
        }


        $args = array(
            'post_type' => 'last-posts',
            'showposts' => $count,
            'tax_query' => $tax_query_array,
            'orderby' => 'title',
            'order' => "$order"
        );
        $get_all_posts = get_posts($args);
        foreach ($get_all_posts as $get_post) {
            echo '<h2>';
            echo "<a href='" . get_permalink($get_post->ID) . "'>" . $get_post->post_title . "</a>";
            echo '</h2>';
            echo '<p>';
            echo $get_post->post_content;
            echo '</p>';
        }
    }

    public function display_information_from_posts($content, $order, $count) {
        $tax_query_array = '';
        if (!empty($content)) {
            $tax_query_array = array(
                array(
                    'taxonomy' => 'last-posts-tax',
                    'field' => 'slug',
                    'terms' => $content
                )
            );
        }


        $args = array(
            'post_type' => '',
            'showposts' => $count,
            'tax_query' => $tax_query_array,
            'orderby' => 'title',
            'order' => "$order"
        );
        $get_all_posts = get_posts($args);
        foreach ($get_all_posts as $get_post) {
            echo '<h2>';
            echo "<a href='" . get_permalink($get_post->ID) . "'>" . $get_post->post_title . "</a>";
            echo '</h2>';
            echo '<p>';
            echo $get_post->post_content;
            echo '</p>';
        }
    }

    public function lp_register_widget_area() {
        return include plugin_dir_path(__FILE__) . '/widget-area.php';
    }

    public function register_option_page() {
        add_submenu_page('edit.php?post_type=last-posts', 'Options', 'Options', 'edit_themes', 'options', array($this, 'option_submenu_callback'));
    }

    public function register_help_page() {
        add_submenu_page('edit.php?post_type=last-posts', 'Help', 'Help', 'edit_themes', 'Help', array($this, 'help_submenu_callback'));
    }

    public function option_submenu_callback() {
        return include plugin_dir_path(__FILE__) . '/options.php';
    }

    public function help_submenu_callback() {
        return include plugin_dir_path(__FILE__) . '/help.php';
    }

    public function enqueue_widget_jquery() {
        wp_register_script('jquery1', plugins_url('/js/jquery-1.9.1.min.js', __FILE__), array('jquery'));

        wp_enqueue_script('jquery1');
    }

}

new Last_Posts();
?>
