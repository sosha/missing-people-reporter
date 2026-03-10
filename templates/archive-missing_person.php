<?php
get_header(); ?>

<div id="primary" class="content-area mpr-container">
    <main id="main" class="site-main">

        <header class="page-header">
            <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
        </header>

        <?php if (have_posts()) : ?>
            <div class="mpr-summary-container mpr-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <?php
                        $age = get_post_meta(get_the_ID(), 'mpr_age', true);
                        $location = get_post_meta(get_the_ID(), 'mpr_last_seen_location', true);
                    ?>
                    <div class="mpr-summary-item">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('medium'); ?>
                            <?php else :
                                // Option 1: Try to get attached images
                                $args = array(
                                    'post_type'      => 'attachment',
                                    'post_mime_type' => 'image',
                                    'post_parent'    => get_the_ID(),
                                    'numberposts'    => 1,
                                    'order'          => 'ASC',
                                    'orderby'        => 'menu_order ID',
                                );
                                $attachments = get_children($args);
                                $first_image_src = '';
                                $image_alt = get_the_title();

                                if ($attachments) {
                                    foreach ($attachments as $attachment) {
                                        $image_array = wp_get_attachment_image_src($attachment->ID, 'medium'); // You can change 'medium' to 'thumbnail', 'large', or a custom size
                                        if ($image_array) {
                                            $first_image_src = $image_array[0];
                                            $image_alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
                                            if (empty($image_alt)) {
                                                $image_alt = $attachment->post_title;
                                            }
                                            if (empty($image_alt)) {
                                                $image_alt = get_the_title();
                                            }
                                            break; // We only need the first one
                                        }
                                    }
                                }

                                // Option 2: If no attached images, try regex from content (fallback)
                                if (empty($first_image_src)) {
                                    $content = get_the_content();
                                    preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);
                                    if (!empty($matches[1])) {
                                        $first_image_src = $matches[1];
                                        preg_match('/<img.+alt=[\'"]([^\'"]+)[\'"].*>/i', $content, $alt_matches);
                                        $image_alt = !empty($alt_matches[1]) ? $alt_matches[1] : get_the_title();
                                    }
                                }
                            ?>
                                <?php if ($first_image_src) : ?>
                                    <img src="<?php echo esc_url($first_image_src); ?>" alt="<?php echo esc_attr($image_alt); ?>">
                                <?php else : ?>
                                    <img src="<?php echo MPR_PLUGIN_URL . 'assets/images/placeholder.png'; ?>" alt="Placeholder Image">
                                <?php endif; ?>
                            <?php endif; ?>
                        </a>
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <?php if ($age) : ?>
                            <p><strong>Age:</strong> <?php echo esc_html($age); ?></p>
                        <?php endif; ?>
                        <?php if ($location) : ?>
                            <p><strong>Last Seen:</strong> <?php echo esc_html($location); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php the_posts_pagination(); ?>

        <?php else : ?>
            <p>No missing people reports found.</p>
        <?php endif; ?>

    </main>
</div>

<?php get_footer(); ?>