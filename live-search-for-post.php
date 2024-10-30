<?php
/*
* Plugin Name: Live search for post
* Description: This plugin use for live search for post without page refresh.
* Version:     1.0.0
* Author:      Shail Mehta
* Author URI:  https://profiles.wordpress.org/mehtashail/
* License:     GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: wordpress.org
*/
if (!class_exists('live_search_for_post')) {
    class Live_search_for_post
    {
        function live_search_for_post_install()
        {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        }
        function activate()
        {
            flush_rewrite_rules();
        }
        function deactivate()
        {
            flush_rewrite_rules();
        }
    }

    // activation and deactivation
    $live_search_for_post = new Live_search_for_post();
    register_activation_hook(__FILE__, array($live_search_for_post, 'activate'));
    register_deactivation_hook(__FILE__, array($live_search_for_post, 'deactivate'));

    add_action('init', 'live_search_for_post_enqueue');
    function live_search_for_post_enqueue()
    {
        // enqueue style
        wp_enqueue_style('Live Search Style', plugin_dir_url(__FILE__) . 'css/style.css', array(), false, 'all');
    }

    // add the ajax fetch js
    add_action('wp_footer', 'live_search_for_post_fetch_data');
    function live_search_for_post_fetch_data()
    {
        ?>
        <script type="text/javascript">
            function fetch() {
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'post',
                    data: {action: 'data_fetch', keyword: jQuery('#keyword').val()},
                    success: function (data) {
                        jQuery('#search_data_fetch').html(data);
                    }
                });
            }
        </script>
        <?php
    }// the ajax function
    add_action('wp_ajax_data_fetch', 'search_data_fetch');
    add_action('wp_ajax_nopriv_data_fetch', 'search_data_fetch');
    function search_data_fetch()
    {
        $the_query = new WP_Query(array('posts_per_page' => -1, 's' => esc_attr($_POST['keyword']), 'post_type' => 'post'));
        if ($the_query->have_posts()) :
            if (strlen($_POST['keyword']) >= 3 && $_POST["keyword"] == 0) {
                while ($the_query->have_posts()):
                    $the_query->the_post(); ?>
                    <div class="live-search-container">
                        <a href="<?php the_permalink(); ?>">
                            <div class="live-search-thumb"><?php the_post_thumbnail() ?></div>
                        </a>
                        <a href="<?php the_permalink() ?>">
                            <div class="live-search-content">
                                <h2> <?php the_title(); ?></h2>
                            </div>
                        </a>
                    </div>
                <?php endwhile;
            } elseif (strlen($_POST['keyword']) < 3) {
                echo "Please enter min 3 characters";
            }
            wp_reset_postdata();
        else:
            echo '<div class="live-search-content">No Results Found</div>';
        endif;
        die();
    }

    /*Short code for Live Search*/
    function live_search_post()
    { ?>
        <div class="live-search">
            <input type="text" class="live-search-design" name="keyword" id="keyword" placeholder="Type min 3 character"
                   onkeyup="fetch()"></input>
            <div id="search_data_fetch"></div>
        </div>
    <?php }

    add_shortcode('live-post-search', 'live_search_post');
}
?>