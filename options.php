<?php

class Options {

    public function __construct() {
        $this->use_information_from_plugin();
    }

    function use_information_from_plugin() {
        if (isset($_POST['submit_options'])) {
            $lp_option['use_information'] = $_POST['is_from_plugin'];
            update_option('lp_option', $lp_option);
        }

        $lp_option = get_option('lp_option');
        if ($lp_option['use_information'] == 'from_plugin') {
            $from_plugin = 'checked';
        } else {
            $not_from_plugin = 'checked';
        }

        echo "<form method='post' action=''>";
        echo '<label>Use information from Plugin</label>';
        echo "<input type='radio' name='is_from_plugin' value='from_plugin' " . $from_plugin . " ><br>";
        echo '<label>Use information from Wordpress Posts</label>';
        echo "<input type='radio' name='is_from_plugin' value='not_from_plugin' " . $not_from_plugin . " >";
        echo "<p class='submit'>";
        echo "<input type='submit' class='button-primary' name='submit_options' value='Update' />";
        echo '</p>';
        echo '</form>';
    }

}

new Options();
