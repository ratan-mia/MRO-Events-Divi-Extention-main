<?php

if (!class_exists('ET_Builder_Module')) {
    return;
}

class ET_Builder_Module_AjaxPostFilter extends ET_Builder_Module
{
    public $slug = 'et_pb_ajax_post_filter';
    public $vb_support = 'on';

    public function init()
    {
        $this->name = esc_html__('Ajax Post Filter', 'et_builder');
    }

    public function get_fields()
    {
        return array(
            'content' => array(
                'label'           => esc_html__('Content', 'et_builder'),
                'type'            => 'tiny_mce',
                'option_category' => 'basic_option',
                'description'     => esc_html__('Content entered here will appear inside the module.', 'et_builder'),
                'toggle_slug'     => 'main_content',
            ),
        );
    }

    public function render($attrs, $content = null, $render_slug)
    {
        // Load your custom scripts and styles
        wp_enqueue_style('bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
        wp_enqueue_script('bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), null, true);
        wp_enqueue_script('ajax-filter-posts', plugin_dir_path(__FILE__) . '/scripts/ajax-filter-posts.js', array('jquery'), null, true);

        wp_localize_script('ajax-filter-posts', 'afp_vars', array(
            'afp_nonce' => wp_create_nonce('afp_nonce'),
            'afp_ajax_url' => admin_url('admin-ajax.php')
        ));

        // Return the form and the container for the posts
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
                        echo '<option value="' . $author->ID . '">' . $author->display_name . '</option>';
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
                        echo '<option value="' . $category->term_id . '">' . $category->name . '</option>';
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
                        echo '<option value="' . $tag->term_id . '">' . $tag->name . '</option>';
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



new ET_Builder_Module_AjaxPostFilter;
?>