<?php

class Last_Posts_Widget extends WP_Widget {

    public function __construct() {
        // Instantiate the parent object
        parent::__construct(false, 'Advanced Last Posts');
        add_action('widgets_init', array($this, 'register_widget_area'));
        add_action('widgets_init', array($this, 'enqueue_widget_style_css'));
        add_action('widgets_content', array($this, 'control'));
    }

    function register_widget_area() {
        register_widget('Last_Posts_Widget');
    }

    public function widget($args, $instance) {
        extract($args);

        $title = apply_filters('widget_title', empty($instance['widget_title']) ? 'Recent Posts' : $instance['widget_title'], $instance, $this->id_base);
        echo $before_widget;
        echo $before_title;

        echo $title;
        echo $after_title;

        echo "<ul>";
        $lp_option = get_option('lp_option');

        if ($lp_option['use_information'] == 'from_plugin') {
            $this->display_information_from_plugin($instance['post_count'], $instance['show_thumb'], $instance['thumb_size'], $instance['show_date'], $instance['post_excerpt'], $instance['show_excerpt'], $instance['read_more']);
        } else {
            $this->display_information_from_posts($instance['post_count'], $instance['show_thumb'], $instance['thumb_size'], $instance['show_date'], $instance['post_excerpt'], $instance['show_excerpt'], $instance['read_more']);
        }
        echo "</ul>";
        echo $after_widget;
    }

    function update($new_instance, $old_instance) {
        $instance = array();
        $instance['widget_title'] = $_POST['widget_title'];
        $instance['post_count'] = $_POST['post_count'];
        if ($_POST['show_thumb'] == 'on') {
            $instance['show_thumb'] = 'checked';
        }
        if ($_POST['show_date'] == 'on') {
            $instance['show_date'] = 'checked';
        }
        switch ($_POST['thumb_size']) {
            case 'thumbnail': $instance['thumb_selected'] = 'checked';
                break;
            case 'medium': $instance['medium_selected'] = 'checked';
                break;
            case 'large': $instance['large_selected'] = 'checked';
                break;
            case 'full': $instance['full_selected'] = 'checked';
                break;
        }


        $instance['thumb_size'] = $_POST['thumb_size'];
        $instance['post_excerpt'] = $_POST['post_excerpt'];

        if ($_POST['show_excerpt'] == 'on') {
            $instance['show_excerpt'] = 'checked';
        }
        if (!empty($_POST['read_more'])) {
            $instance['read_more'] = $_POST['read_more'];
        }

        return $instance;
    }

    function form($instance) {
        echo "<label for='sisa'>Title:</label> ";
        echo "<input type='text' name='widget_title' id='sisa' value='" . $instance['widget_title'] . "' /><br>";
        echo "<label>Number of Posts:</label> ";
        echo "<input type='text' class='tf-small-text' name='post_count' value='" . $instance['post_count'] . "' /><br>";
        echo "<label for='post-date'>Display Post Date:</label> ";
        echo "<input type='checkbox' id='post-date' name='show_date' " . $instance['show_date'] . "/><br>";
        echo "<label>Show Posts Thumbnail</label>";
        echo "<input type='checkbox' name='show_thumb' id='show_thumb' " . $instance['show_thumb'] . " /><br>";

        echo "<label>Thumbnail size:</label><br>";
        echo "<div id='widget-thumb-size'>";
        echo "<label>thumbnail:</label>";
        echo "<input type='radio' name='thumb_size' value='thumbnail' " . $instance['thumb_selected'] . "><br>";
        echo "<label>medium:</label>";
        echo "<input type='radio' name='thumb_size' value='medium' " . $instance['medium_selected'] . " ><br>";
        echo "<label>large:</label>";
        echo "<input type='radio' name='thumb_size' value='large' " . $instance['large_selected'] . " ><br>";
        echo "<label>full:</label>";
        echo "<input type='radio' name='thumb_size' value='full' " . $instance['full_selected'] . " ><br>";
        echo '</div>';
        echo "<label>Posts excerpt:</label> ";
        echo "<input type='checkbox' name='show_excerpt' " . $instance['show_excerpt'] . "><br>";
        echo "<label>Posts excerpt in words:</label> ";
        echo "<input type='text' name='post_excerpt' class='tf-small-text' value='" . $instance['post_excerpt'] . "'><br>";
        echo "<label>Read more text:</label> ";
        echo "<input type='text' name='read_more' value='" . $instance['read_more'] . "'><br>";

        return $instance;
    }

    public function display_information_from_plugin($number_of_posts, $show_thumb, $thumb_size, $display_date, $post_excerpt, $show_excerpt, $read_more) {
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
            'showposts' => $number_of_posts,
            'tax_query' => $tax_query_array,
            'orderby' => 'title',
            'order' => "$order"
        );


        $get_all_posts = get_posts($args);
        foreach ($get_all_posts as $get_post) {

            echo '<li class="single-post">';

            echo "<div class='post-title'><a href='" . get_permalink($get_post->ID) . "'>" . $get_post->post_title . "</a></div>";
            if ($show_thumb) {
                echo "<div class='post-thumb alignleft'>";
                $this->display_posts_thumbnails($get_post->ID, $thumb_size);
                echo '</div>';
            }
            if ($display_date) {
                echo $get_post->post_date;
            }

            if ($show_excerpt) {
                echo $this->excerpt_length($get_post->post_content, $post_excerpt) . " <a class='read-more' href='" . get_permalink($get_post->ID) . "'>" . $read_more . "</a>";
            } else {
                echo "<div class='post-content'>" . $get_post->post_content . "</div>";
            }

            echo '</li>';
        }
    }

    public function display_information_from_posts($number_of_posts, $show_thumb, $thumb_size, $display_date, $post_excerpt, $show_excerpt) {
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
            'showposts' => $number_of_posts,
            'tax_query' => $tax_query_array,
            'orderby' => 'title',
            'order' => "$order"
        );


        $get_all_posts = get_posts($args);
        foreach ($get_all_posts as $get_post) {
            echo '<li class="single-post">';
            echo "<div class='post-title'><a href='" . get_permalink($get_post->ID) . "'>" . $get_post->post_title . "</a></div>";
            
            if ($show_thumb) {
                echo "<div class='post-thumb alignleft'>";
                $this->display_posts_thumbnails($get_post->ID, $thumb_size);
                echo '</div>';
            }
            if ($display_date) {
                echo $get_post->post_date;
            }
            if ($show_excerpt) {
                echo $this->excerpt_length($get_post->post_content, $post_excerpt) . " <a class='read-more' href='" . get_permalink($get_post->ID) . "'>" . $read_more . "</a>";
            } else {
               echo "<div class='post-content'>" . $get_post->post_content . "</div>";
            }
             echo '</li>';
        }
    }

    public function display_posts_thumbnails($post_id, $thumb_size) {

        switch ($thumb_size) {
            case 'medium': $thumb_size = array(80, 80);
                break;
            case 'large': $thumb_size = array(120, 120);
                break;
            case 'full': $thumb_size = 'full';
                break;
            default: $thumb_size = array(60, 60);
                break;
        }
        echo get_the_post_thumbnail($post_id, $thumb_size);
    }

    public function display_posts_date() {
        echo the_date();
    }

    function excerpt_length($post_content, $post_excerpt_length) {
        $excerpted_sentence = implode(' ', array_slice(explode(' ', $post_content), 0, $post_excerpt_length));
        return $excerpted_sentence;
    }

    public function enqueue_widget_style_css() {
        wp_enqueue_style('widget-styles.css', plugins_url('/styles/widget-styles.css', __FILE__));
        wp_enqueue_style('widget-styles.css');
    }

}

new Last_Posts_Widget();
?>
