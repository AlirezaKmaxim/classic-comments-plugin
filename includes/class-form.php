<?php
/**
 * Custom Comment System Form Handler
 *
 * @package CustomCommentSystem
 * @author AlirezaKMaxim
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Custom_Comment_Form {

    public function __construct() {
        // Register AJAX hooks
        add_action( 'wp_ajax_custom_submit_comment', array( $this, 'handle_submit' ) );
        add_action( 'wp_ajax_nopriv_custom_submit_comment', array( $this, 'handle_submit' ) );
    }

    /**
     * Render the custom comment form HTML
     *
     * @return string
     */
    public function render_form() {
        ob_start();
        ?>
        <form class="custom-form" action="#" method="post">
            <?php wp_nonce_field( 'custom_comment_action', 'custom_comment_nonce' ); ?>
            <input type="hidden" name="comment_post_ID" value="<?php echo esc_attr( get_the_ID() ); ?>" />
            
            <!-- First row: Name & Email side-by-side -->
            <div class="form-row">
                <div class="form-group">
                    <label for="fullName">نام و نام خانوادگی</label>
                    <input type="text" id="fullName" name="fullName" placeholder="نام خود را وارد کنید" required>
                </div>
                <div class="form-group">
                    <label for="email">ایمیل</label>
                    <input type="email" id="email" name="email" placeholder="example@email.com" required>
                </div>
            </div>

            <!-- Second row: Message textarea -->
            <div class="form-group">
                <label for="message">پیام شما</label>
                <textarea id="message" name="message" placeholder="متن خود را اینجا بنویسید..." required></textarea>
            </div>

            <!-- Submit Button -->
            <div class="btn-container">
                <button type="submit" class="submit-btn">ثبت</button>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX handler for comment submission
     */
    public function handle_submit() {
        // Verify security nonce
        if ( ! isset( $_POST['custom_comment_nonce'] ) || ! wp_verify_nonce( $_POST['custom_comment_nonce'], 'custom_comment_action' ) ) {
            wp_send_json_error( array( 'message' => 'خطای امنیتی رخ داده است. لطفاً صفحه را مجدداً بارگذاری کنید.' ) );
        }

        // Validate and sanitize post ID
        $post_id = isset( $_POST['comment_post_ID'] ) ? intval( $_POST['comment_post_ID'] ) : 0;
        if ( ! $post_id || get_post_status( $post_id ) !== 'publish' ) {
            wp_send_json_error( array( 'message' => 'شناسه نوشته معتبر نمی‌باشد.' ) );
        }

        // Sanitize form inputs
        $fullName = isset( $_POST['fullName'] ) ? sanitize_text_field( $_POST['fullName'] ) : '';
        $email    = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
        $message  = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';

        // Validate empty fields
        if ( empty( $fullName ) || empty( $email ) || empty( $message ) ) {
            wp_send_json_error( array( 'message' => 'لطفاً تمامی فیلدها را به طور کامل پر کنید.' ) );
        }

        // Validate email format
        if ( ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => 'آدرس ایمیل وارد شده معتبر نمی‌باشد.' ) );
        }

        // Determine if comment should be approved immediately
        // Follows WordPress settings for comment moderation
        $comment_approved = get_option( 'comment_moderation' ) ? 0 : 1;

        // Prepare comment data
        $comment_data = array(
            'comment_post_ID'      => $post_id,
            'comment_author'       => $fullName,
            'comment_author_email' => $email,
            'comment_content'      => $message,
            'comment_type'         => 'comment',
            'comment_parent'       => 0, // Always parent since it's submitted from front form
            'comment_approved'     => $comment_approved,
            'comment_date'         => current_time( 'mysql' ),
            'user_id'              => get_current_user_id(),
        );

        // Insert comment
        $comment_id = wp_insert_comment( $comment_data );

        if ( ! $comment_id ) {
            wp_send_json_error( array( 'message' => 'خطایی در ثبت نظر رخ داد. لطفاً مجدداً تلاش کنید.' ) );
        }

        $comment_html = '';
        if ( 1 === $comment_approved ) {
            $comment = get_comment( $comment_id );
            // Generate comment markup if display class is loaded
            if ( $comment && class_exists( 'Custom_Comment_Display' ) ) {
                ob_start();
                Custom_Comment_Display::render_single_comment( $comment );
                $comment_html = ob_get_clean();
            }
        }

        // Response message based on approval status
        $msg = ( 1 === $comment_approved ) 
            ? 'نظر شما با موفقیت ثبت و منتشر شد.' 
            : 'نظر شما با موفقیت ثبت شد و پس از تایید ادمین نمایش داده می‌شود.';

        wp_send_json_success( array(
            'message'      => $msg,
            'comment_html' => $comment_html,
        ) );
    }
}
