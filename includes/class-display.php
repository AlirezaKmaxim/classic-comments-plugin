<?php
/**
 * Custom Comment System Display Handler
 *
 * @package CustomCommentSystem
 * @author AlirezaKMaxim
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Custom_Comment_Display {

    /**
     * Render the comments section for a post
     *
     * @return string
     */
    public function render_comments_list() {
        $post_id = get_the_ID();
        
        // Fetch approved parent comments for the current post
        $comments = get_comments( array(
            'post_id' => $post_id,
            'status'  => 'approve',
            'parent'  => 0,
            'order'   => 'DESC', // Newest first
        ) );

        ob_start();
        ?>
        <section class="comments-section">
            <?php if ( ! empty( $comments ) ) : ?>
                <ul class="comments-list">
                    <?php foreach ( $comments as $comment ) : ?>
                        <li class="comment-item" id="comment-<?php echo esc_attr( $comment->comment_ID ); ?>">
                            <?php self::render_single_comment( $comment ); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <!-- Optional: placeholder when no comments exist -->
            <?php endif; ?>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Render a single parent comment along with its admin replies
     *
     * @param WP_Comment $comment
     */
    public static function render_single_comment( $comment ) {
        if ( ! $comment ) {
            return;
        }

        // Render parent comment metadata (outside box)
        ?>
        <header class="comment__meta">
            <span class="comment__author"><?php echo esc_html( $comment->comment_author ); ?></span>
            <time class="comment__date" datetime="<?php echo esc_attr( mysql2date( 'c', $comment->comment_date ) ); ?>">
                <?php echo esc_html( self::get_formatted_date( $comment ) ); ?>
            </time>
        </header>

        <!-- Main customer comment box (Orange) -->
        <article class="comment__box comment__box--main">
            <div class="comment__line comment__line--white"></div>
            <p class="comment__text"><?php echo nl2br( esc_html( $comment->comment_content ) ); ?></p>
        </article>

        <?php
        // Fetch approved children/replies
        $replies = get_comments( array(
            'post_id' => $comment->comment_post_ID,
            'status'  => 'approve',
            'parent'  => $comment->comment_ID,
            'order'   => 'ASC', // Chronological order for replies
        ) );

        // Filter to keep only admin replies
        $admin_replies = array();
        foreach ( $replies as $reply ) {
            if ( $reply->user_id ) {
                $user = get_userdata( $reply->user_id );
                if ( $user && in_array( 'administrator', $user->roles ) ) {
                    $admin_replies[] = $reply;
                }
            }
        }

        // If there are admin replies, render the connector and each reply
        if ( ! empty( $admin_replies ) ) {
            $reply_count = count( $admin_replies );
            ?>
            <!-- SVG connector and reply count -->
            <div class="comments-section__connector">
                <svg width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M23 4V14C23 16.2091 21.2091 18 19 18H7" stroke="#66614d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="3 3"/>
                    <path d="M11 14L7 18L11 22" stroke="#66614d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="comments-section__count">
                    <?php echo esc_html( self::get_replies_count_text( $reply_count ) ); ?>
                </span>
            </div>

            <?php
            foreach ( $admin_replies as $reply ) {
                ?>
                <!-- Admin reply metadata (outside box, aligned left) -->
                <header class="comment__meta comment__meta--reply">
                    <span class="comment__author"><?php echo esc_html( $reply->comment_author ); ?></span>
                    <time class="comment__date" datetime="<?php echo esc_attr( mysql2date( 'c', $reply->comment_date ) ); ?>">
                        <?php echo esc_html( self::get_formatted_date( $reply ) ); ?>
                    </time>
                </header>

                <!-- Admin reply box (Cream) -->
                <article class="comment__box comment__box--reply">
                    <div class="comment__line comment__line--dark"></div>
                    <p class="comment__text"><?php echo nl2br( esc_html( $reply->comment_content ) ); ?></p>
                </article>
                <?php
            }
        }
    }

    /**
     * Helper to format dates to Jalali and convert digits to Persian
     *
     * @param WP_Comment $comment
     * @return string
     */
    public static function get_formatted_date( $comment ) {
        $timestamp = mysql2date( 'U', $comment->comment_date );
        
        // Get active date format (default j F Y, e.g. 15 خرداد 1402)
        $date_format = get_option( 'date_format', 'j F Y' );
        
        // wp_date automatically translates and applies jalali filters if WP-Jalali/Parsi Date is active
        $date_str = wp_date( $date_format, $timestamp );
        
        return self::to_persian_digits( $date_str );
    }

    /**
     * Convert English digits to Persian digits
     *
     * @param string $number
     * @return string
     */
    public static function to_persian_digits( $number ) {
        $english = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
        $persian = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
        return str_replace( $english, $persian, $number );
    }

    /**
     * Generate localized replies count text
     *
     * @param int $count
     * @return string
     */
    public static function get_replies_count_text( $count ) {
        return self::to_persian_digits( $count ) . ' پاسخ';
    }
}
