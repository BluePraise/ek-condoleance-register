<?php
namespace Tahlil\Inc\Frontend;

if (!defined('ABSPATH')) { exit; }

if (!class_exists('PmgCommentAttachment')){
    class PmgCommentAttachment
    {
        private $key            = 'pmg_comment_atttachment_';
        private $adminPrefix    = 'pmg_comment_atttachment_';
        private $settings;

        /**
         * Constructor
         */
        public function __construct()
        {
            // error_reporting(0);
            add_action('init', array($this, 'init'));
            add_action('plugins_loaded', array($this, 'loaded'));
            add_action('tahlil_attachment_post', array($this, 'attachmentAction'));
        }

        /**
         * Classic init
         */
        public function init()
        {
            add_action('delete_comment',                array($this, 'deleteAttachment'));
            add_filter('comment_notification_text',     array($this, 'notificationText'), 10, 2);
            // add_action('admin_init',                    array($this, 'adminInit'));
            $this->attachmentAction();
        }

        public function adminInit()
        {
            add_filter('comment_row_actions', array($this, 'addCommentActionLinks'), 10, 2);
        }

        public function attachmentAction()
        {
            add_filter('preprocess_comment',    array($this, 'checkAttachment'), 10, 1);
            add_action('comment_post',          array($this, 'saveAttachment'));
        }


        /**
         * Returns maximum upload file size
         */

        public static function getMaximumUploadFileSize()
        {
            $maxUpload      = (int)(ini_get('upload_max_filesize'));
            $maxPost        = (int)(ini_get('post_max_size'));
            $memoryLimit    = (int)(ini_get('memory_limit'));
            return min($maxUpload, $maxPost, $memoryLimit);
        }

        /**
         * Gets allowed file types extensions
         */

        public function getAllowedFileExtensions()
        {
            return array(
                'jpg',
                'jpeg',
                'gif',
                'png'
            );
        }


        /**
         * For attachment display, get's image mime types
         */

        public function getImageMimeTypes()
        {
            return array(
                'image/jpeg',
                'image/jpg',
                'image/jp_',
                'application/jpg',
                'application/x-jpg',
                'image/pjpeg',
                'image/pipeg',
                'image/vnd.swiftview-jpeg',
                'image/x-xbitmap',
                'image/gif',
                'image/x-xbitmap',
                'image/gi_',
                'image/png',
                'application/png',
                'application/x-png'
            );
        }

        /**
         * Gets allowed file types for attachment check.
         */

        public function getAllowedMimeTypes()
        {
            return $this->getImageMimeTypes();
        }


        /**
         * This one actually will need explaining, it's hard
         */

        public function getAllowedUploadMimes($existing = array())
        {
            return $this->getAllowedMimeTypes();
        }


        /*
         * For error info, and form upload info.
         */
        public function displayAllowedFileTypes()
        {
            $fileTypesString = '';
            foreach($this->getAllowedFileExtensions() as $value){
                $fileTypesString .= $value . ', ';
            }
            return substr($fileTypesString, 0, -2);
        }


        /**
         * Checks attachment, size, and type and throws error if something goes wrong.
         */

        public function checkAttachment($data)
        {
            // wp_die(print_r($_FILES['attachment']));
            if (isset($_POST['pmg_comment_type']) && $_POST['pmg_comment_type'] == 'photos') {
                if (isset($_FILES) && isset($_FILES['attachment'])) {
                    if($_FILES['attachment']['size'] > 0 && $_FILES['attachment']['error'] == 0) {
                        $fileInfo = pathinfo($_FILES['attachment']['name']);
                        $fileExtension = strtolower($fileInfo['extension']);

                        if(function_exists('finfo_file')){
                            $fileType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $_FILES['attachment']['tmp_name']);
                        } elseif(function_exists('mime_content_type')) {
                            $fileType = mime_content_type($_FILES['attachment']['tmp_name']);
                        } else {
                            $fileType = $_FILES['attachment']['type'];
                        }
                        substr($fileType, 0, strrpos($fileType, ' '));
                        if (!in_array($fileType, $this->getAllowedMimeTypes()) ||  $_FILES['attachment']['size'] > (2 * 1048576)) {
                            wp_die(sprintf(__('<strong>ERROR:</strong> File you upload must be valid file type <strong>(%1$s)</strong>, and under %2$sMB!','comment-attachment'),$this->displayAllowedFileTypes(),2));
                        }
                    } elseif ($_FILES['attachment']['error'] == 4) {
                        wp_die(__('<strong>ERROR:</strong> Attachment is a required field!','comment-attachment'));
                    } elseif($_FILES['attachment']['error'] == 1) {
                        wp_die(__('<strong>ERROR:</strong> The uploaded file exceeds the upload_max_filesize directive in php.ini.','comment-attachment'));
                    } elseif($_FILES['attachment']['error'] == 2) {
                        wp_die(__('<strong>ERROR:</strong> The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.','comment-attachment'));
                    } elseif($_FILES['attachment']['error'] == 3) {
                        wp_die(__('<strong>ERROR:</strong> The uploaded file was only partially uploaded. Please try again later.','comment-attachment'));
                    } elseif($_FILES['attachment']['error'] == 6) {
                        wp_die(__('<strong>ERROR:</strong> Missing a temporary folder.','comment-attachment'));
                    } elseif($_FILES['attachment']['error'] == 7) {
                        wp_die(__('<strong>ERROR:</strong> Failed to write file to disk.','comment-attachment'));
                    } elseif($_FILES['attachment']['error'] == 7) {
                        wp_die(__('<strong>ERROR:</strong> A PHP extension stopped the file upload.','comment-attachment'));
                    }
                }
            }
            return $data;
        }


        /**
         * Notification email message
         *
         */

        public function notificationText($notify_message,  $comment_id)
        {
            if(PmgCommentAttachment::hasAttachment($comment_id)){
                $attachmentId = get_comment_meta($comment_id, 'attachmentId', TRUE);
                $attachmentName = basename(get_attached_file($attachmentId));
                $notify_message .= __('Attachment:','comment-attachment') . "\r\n" .  $attachmentName . "\r\n\r\n";
            }
            return $notify_message;
        }


        /**
         * Inserts file attachment from your comment to wordpress
         * media library, assigned to post.
         */

        public function insertAttachment($fileHandler, $postId)
        {
            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
            require_once(ABSPATH . "wp-admin" . '/includes/file.php');
            require_once(ABSPATH . "wp-admin" . '/includes/media.php');
            return media_handle_upload($fileHandler, $postId);
        }


        /**
         * Save attachment to db, with all sizes etc. Assigned
         * to post, or not.
         */

        public function saveAttachment($commentId)
        {   
            if (isset($_FILES) && isset($_FILES['attachment'])) {
                if($_FILES['attachment']['size'] > 0){
                    $bindId = $_POST['comment_post_ID'];
                    $attachId = $this->insertAttachment('attachment', $bindId);
                    add_comment_meta($commentId, 'attachmentId', $attachId);
                    unset($_FILES);
                }
            }
        }

        /**
         * Loaded, check request
         */
        public function loaded()
        {
            // check to delete att
            if(isset($_GET['deleteAtt']) && ($_GET['deleteAtt'] == '1')){
                if((isset($_GET['c'])) && is_numeric($_GET['c'])){
                    PmgCommentAttachment::deleteAttachment($_GET['c']);
                    delete_comment_meta($_GET['c'], 'attachmentId');
                    add_action('admin_notices', function(){
                        echo "<div class='updated'><p>".__('Comment Attachment deleted.','comment-attachment')."</p></div>";
                    });
                }
            }
        }


        /**
         * add action to admin comments table's row
         */
        public function addCommentActionLinks($actions, $comment)
        {
            if(PmgCommentAttachment::hasAttachment($comment->comment_ID)){
                $url = $_SERVER["SCRIPT_NAME"] . "?c=$comment->comment_ID&deleteAtt=1";
                $actions['deleteAtt'] = "<a href='$url' title='".esc_attr__('Delete Attachment','comment-attachment')."'>".__('Delete Attachment','comment-attachment').'</a>';
            }
            return $actions;
        }

        /**
         * This deletes attachment after comment deletition.
         */
        public function deleteAttachment($commentId)
        {
            $attachmentId = get_comment_meta($commentId, 'attachmentId', TRUE);
            if(is_numeric($attachmentId) && !empty($attachmentId)){
                wp_delete_attachment($attachmentId, TRUE);
            }
        }

        /**
         * Has attachment
         *
         */
        public static function hasAttachment($commentId)
        {
            $attachmentId = get_comment_meta($commentId, 'attachmentId', TRUE);
            if(is_numeric($attachmentId) && !empty($attachmentId)){
                return true;
            }
            return false;
        }
    }
}