<?php
if (!class_exists('ET_Builder_Module')) {
    return;
}

class ET_Builder_Module_MRO_Ajax_Post_Filter extends ET_Builder_Module
{
    public $slug = 'et_pb_mro_ajax_post_filter';
    public $vb_support = 'on';

    function init()
    {
        $this->name = esc_html__('MRO AJAX POST Filter', 'et_builder');
    }

    public function get_fields()
    {
        return array(
            'content' => array(
                'label' => esc_html__('Content', 'et_builder'),
                'type' => 'tiny_mce',
                'option_category' => 'basic_option',
                'description' => esc_html__('Add the content here.', 'et_builder'),
                'toggle_slug' => 'main_content',
            ),
        );
    }

    public function render($attrs, $content = null, $render_slug)
    {
        ob_start();
?>
        <form method="GET" id="filter-form" class="d-flex align-items-center mb-4">
            <div class="form-group mr-2 mb-2">
                <label for="author" class="mr-2">Author:</label>
                <select name="author" id="author" class="form-control">
                    <option value="">Select Author</option>
                    <?php
                    $authors = get_users(array('who' => 'authors'));
                    foreach ($authors as $author) {
                        echo '<option value="' . esc_attr($author->ID) . '">' . esc_html($author->display_name) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group mr-2 mb-2">
                <label for="category" class="mr-2">Category:</label>
                <select name="category" id="category" class="form-control">
                    <option value="">Select Category</option>
                    <?php
                    $categories = get_categories();
                    foreach ($categories as $category) {
                        echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group mr-2 mb-2">
                <label for="tags" class="mr-2">Tags:</label>
                <select name="tags" id="tags" class="form-control">
                    <option value="">Select Tags</option>
                    <?php
                    $tags = get_tags(array(
                        'hide_empty' => false
                    ));

                    foreach ($tags as $tag) {
                        echo '<option value="' . esc_attr($tag->term_id) . '">' . esc_html($tag->name) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group mr-2 mb-2">
                <label for="search" class="mr-2">Search:</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search...">
            </div>
            <button type="submit" class="btn btn-primary mb-2">Search</button>
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
}

new ET_Builder_Module_MRO_Ajax_Post_Filter;

function enqueue_custom_scripts()
{
    wp_enqueue_style('bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), null, true);
    wp_enqueue_script('ajax-filter-posts', plugin_dir_url(__FILE__) . 'js/ajax-filter-posts.js', array('jquery'), null, true);

    wp_localize_script('ajax-filter-posts', 'afp_vars', array(
        'afp_nonce' => wp_create_nonce('afp_nonce'),
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
