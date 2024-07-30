<?php
/*
Plugin Name: MRO Events Divi Extention
Plugin URI:  https://shorifullislamratan.me/projects/mro-events
Description: A simple Divi Extention to show custom events posts
Version:     1.0.0
Author:      Ratan Mia
Author URI:  https://shorifullislamratan.me
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: mro-mro-events-divi-extention
Domain Path: /languages

Mro Events Divi Extention is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Mro Events Divi Extention is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Mro Events Divi Extention. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/


if (!function_exists('mro_initialize_extension')) :
    /**
     * Creates the extension's main class instance.
     *
     * @since 1.0.0
     */

    // add javascript and css
    function mro_events_divi_extention_scripts()
    {
        wp_enqueue_style('mro-events-divi-extention-style', plugin_dir_url(__FILE__) . '/styles/swiper.css', array(), '1.0.0', 'all');
        wp_enqueue_style('mro-events-custom-style', plugin_dir_url(__FILE__) . '/styles/custom-style.css', array(), '1.0.0', 'all');
        wp_enqueue_script('mro-events-swiper', plugin_dir_url(__FILE__) . '/scripts/swiper-bundle.min.js', array('jquery'), '1.0.0', true);
        wp_enqueue_style('mro-event-carousel', plugin_dir_url(__FILE__) . '/includes/modules/BlogCarousel/style.css', array(), '1.0.0', 'all');
        wp_enqueue_script('mro-event-carousel', plugin_dir_url(__FILE__) . '/includes/modules/BlogCarousel/frontend.min.js', array('mro-events-swiper'), '1.0.0', true);
    }
    add_action('wp_enqueue_scripts', 'mro_events_divi_extention_scripts');


    function mro_initialize_extension()
    {
        require_once plugin_dir_path(__FILE__) . 'includes/MroEventsDiviExtention.php';
    }
    add_action('divi_extensions_init', 'mro_initialize_extension');
endif;


// Enqueue scripts

function enqueue_custom_scripts()
{
    wp_enqueue_style('bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), null, true);
    wp_enqueue_script('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js', array('jquery'), null, true);
    wp_enqueue_script('ajax-filter-posts', get_template_directory_uri() . '/js/ajax-filter-posts.js', array('jquery'), null, true);

    wp_localize_script('ajax-filter-posts', 'afp_vars', array(
        'afp_nonce' => wp_create_nonce('afp_nonce'), // Create nonce which we later will use to verify AJAX request
        'afp_ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');



function filter_posts()
{
    check_ajax_referer('afp_nonce', 'afp_nonce');

    $author = isset($_POST['author']) ? sanitize_text_field($_POST['author']) : '';
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $tags = isset($_POST['tags']) ? sanitize_text_field($_POST['tags']) : '';
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $paged = isset($_POST['paged']) ? sanitize_text_field($_POST['paged']) : 1;

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 6,
        'paged' => $paged,
        'author' => $author,
        'category' => $category,
        'tag__in' => $tags ? array($tags) : array(),
        's' => $search
    );

    $query = new WP_Query($args);
    if ($query->have_posts()) :
        echo '<div class="row">';
        while ($query->have_posts()) : $query->the_post();
?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php the_title(); ?></h5>
                        <p class="card-text"><?php the_excerpt(); ?></p>
                        <a href="<?php the_permalink(); ?>" class="btn btn-primary">Read More</a>
                    </div>
                </div>
            </div>
        <?php
        endwhile;
        echo '</div>';
        ?>
        <div class="pagination">
            <?php
            echo paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => $paged,
                'prev_text' => __('« Prev'),
                'next_text' => __('Next »'),
                'format' => '?paged=%#%',
            ));
            ?>
        </div>
    <?php
    else :
        echo '<p>No posts found</p>';
    endif;

    wp_reset_postdata();
    die();
}
add_action('wp_ajax_filter_posts', 'filter_posts');
add_action('wp_ajax_nopriv_filter_posts', 'filter_posts');







function custom_post_filter_shortcode()
{
    ob_start();
    ?>
    <form method="GET" id="filter-form" class="d-flex align-items-center mb-4">


        <div class="row">
            <div class="col-md-3">
                <div class="form-group mr-2 mb-2">
                    <label for="author" class="mr-2">Author:</label>
                    <select name="author" id="author" class="form-control">
                        <option value="">Select Author</option>
                        <?php
                        $authors = get_users(array('who' => 'authors'));
                        foreach ($authors as $author) {
                            echo '<option value="' . $author->ID . '">' . $author->display_name . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group mr-2 mb-2">
                    <label for="category" class="mr-2">Category:</label>
                    <select name="category" id="category" class="form-control">
                        <option value="">Select Category</option>
                        <?php
                        $categories = get_categories();
                        foreach ($categories as $category) {
                            echo '<option value="' . $category->term_id . '">' . $category->name . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group mr-2 mb-2">
                    <label for="tags" class="mr-2">Tags:</label>
                    <select name="tags" id="tags" class="form-control">
                        <option value="">Select Tags</option>
                        <?php
                        $tags = get_tags(array(
                            'hide_empty' => false
                        ));

                        foreach ($tags as $tag) {
                            echo '<option value="' . $tag->term_id . '">' . $tag->name . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group position-relative">
                    <label for="search" class="mr-2">Search:</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search...">
                    <button type="submit" class="btn position-absolute" style="right: 0; top: 0; height: 100%; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>

        </div>
    </form>

    <div id="response">
        <?php
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => 6,
            'paged' => $paged,
        );

        $query = new WP_Query($args);
        if ($query->have_posts()) :
            echo '<div class="row">';
            while ($query->have_posts()) : $query->the_post();
        ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php the_title(); ?></h5>
                            <p class="card-text"><?php echo wp_kses_post(get_the_excerpt()); ?></p>
                            <a href="<?php the_permalink(); ?>" class="btn btn-primary">Read More</a>
                        </div>
                    </div>
                </div>
            <?php
            endwhile;
            echo '</div>';
            ?>
            <div class="pagination">
                <?php
                echo paginate_links(array(
                    'total' => $query->max_num_pages,
                    'current' => $paged,
                    'prev_text' => __('« Prev'),
                    'next_text' => __('Next »'),
                    'format' => '?paged=%#%',
                ));
                ?>
            </div>
        <?php
        else :
            echo '<p>No posts found</p>';
        endif;

        wp_reset_postdata();
        ?>
    </div>

<?php
    return ob_get_clean();
}

add_shortcode('custom_post_filter', 'custom_post_filter_shortcode');



// Add AJAX actions to filter the post

function inline_ajax_filter_script()
{
?>
    <script type="text/javascript">
        jQuery(function($) {
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();

                var filter = $('#filter-form');
                $.ajax({
                    url: afp_vars.afp_ajax_url,
                    type: 'post',
                    data: filter.serialize() + '&action=filter_posts&afp_nonce=' + afp_vars.afp_nonce,
                    beforeSend: function() {
                        $('#response').html('Loading...');
                    },
                    success: function(response) {
                        $('#response').html(response);
                    }
                });
            });
        });
    </script>
<?php
}
add_action('wp_footer', 'inline_ajax_filter_script');





// Register Ajax Post Filter Module

function custom_divi_module()
{
    if (class_exists('ET_Builder_Module')) {
        include_once plugin_dir_path(__FILE__) . 'class-AjaxPostFilter.php';
        // include_once plugin_dir_path(__FILE__) . 'class-MRO-AjaxPostFilter.php';
    }
}
add_action('et_builder_ready', 'custom_divi_module');
