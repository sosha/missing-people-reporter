<?php
get_header(); ?>

<div id="primary" class="content-area mpr-container">
    <main id="main" class="site-main">

        <header class="page-header">
            <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
        </header>

        <?php if (have_posts()): ?>
            <div class="mpr-summary-container mpr-grid">
                <?php while (have_posts()):
        the_post(); ?>
                    <?php
        $age = get_post_meta(get_the_ID(), 'mpr_age', true);
        $location = get_post_meta(get_the_ID(), 'mpr_last_seen_location', true);
        $first_image_src = mpr_get_case_image_url(get_the_ID(), 'medium');
        $image_alt = get_the_title();
?>
                    <div class="mpr-summary-item">
                        <a href="<?php the_permalink(); ?>">
                             <img src="<?php echo esc_url($first_image_src); ?>" alt="<?php echo esc_attr($image_alt); ?>">
                        </a>
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <?php if ($age): ?>
                            <p><strong><?php _e('Age:', 'mpr'); ?></strong> <?php echo esc_html($age); ?></p>
                        <?php
        endif; ?>
                        <?php if ($location): ?>
                            <p><strong><?php _e('Last Seen:', 'mpr'); ?></strong> <?php echo esc_html($location); ?></p>
                        <?php
        endif; ?>
                    </div>
                <?php
    endwhile; ?>
            </div>

            <?php the_posts_pagination(); ?>

        <?php
else: ?>
            <p><?php _e('No missing people reports found.', 'mpr'); ?></p>
        <?php
endif; ?>

    </main>
</div>

<?php get_footer(); ?>