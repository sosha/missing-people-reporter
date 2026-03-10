<?php
/**
 * Public Discussion (Comments) Integration for Missing People Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enable comments for missing_person post type.
 */
function mpr_enable_comments_for_missing_person()
{
    add_post_type_support('missing_person', 'comments');
}
add_action('init', 'mpr_enable_comments_for_missing_person');

/**
 * Custom Comment Styling and Template.
 */
function mpr_custom_comment_list($comment, $args, $depth)
{
    $GLOBALS['comment'] = $comment;
?>
    <li <?php comment_class('mpr-comment-item'); ?> id="li-comment-<?php comment_ID(); ?>">
        <div id="comment-<?php comment_ID(); ?>" class="mpr-comment-body">
            <div class="mpr-comment-avatar">
                <?php echo get_avatar($comment, 48); ?>
            </div>
            <div class="mpr-comment-content">
                <div class="mpr-comment-meta">
                    <span class="mpr-comment-author"><?php comment_author(); ?></span>
                    <span class="mpr-comment-date"><?php printf(__('%1$s at %2$s'), get_comment_date(), get_comment_time()); ?></span>
                </div>
                <div class="mpr-comment-text">
                    <?php if ($comment->comment_approved == '0'): ?>
                        <p class="mpr-comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.', 'mpr'); ?></p>
                    <?php
    endif; ?>
                    <?php comment_text(); ?>
                </div>
                <div class="mpr-comment-reply">
                    <?php comment_reply_link(array_merge($args, array('depth' => $depth, 'max_depth' => $args['max_depth']))); ?>
                </div>
            </div>
        </div>
    <?php
}

/**
 * Filter Comment Form to match design.
 */
function mpr_custom_comment_form_args($args)
{
    $args['class_form'] = 'mpr-discussion-form';
    $args['title_reply'] = __('Join the Discussion', 'mpr');
    $args['label_submit'] = __('Post Comment', 'mpr');
    $args['comment_field'] = '<p class="comment-form-comment"><textarea id="comment" name="comment" cols="45" rows="5" aria-required="true" placeholder="' . esc_attr__('Share a message of support or public information...', 'mpr') . '"></textarea></p>';
    return $args;
}
add_filter('comment_form_defaults', 'mpr_custom_comment_form_args');
