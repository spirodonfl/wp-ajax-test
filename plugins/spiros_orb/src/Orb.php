<?php
/**
 * The main class that power the Orb plugin test
 */

if ( ! class_exists( 'Orb' ) ) {
    /**
     * The Orb Class!
     */
    class Orb {
        /**
         * Constant that should or do not change
         */
        const ADMIN_PAGE_TITLE = 'Orb Ajax Form Title';
        const ADMIN_PAGE_DESCRIPTION = 'Orb Ajax Form Description';
        const UNIQUE_IDENTIFIER = '123456789-test-ajax-form';

        /**
         * Static Singleton Holder
         * @var self
         */
        protected static $instance;

        /**
         * Holds values
         */
        private $options;

        /**
         * Get (and instantiate, if necessary) the instance of the class
         *
         * @return self
         */
        public static function instance() {
            if ( ! self::$instance ) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        /**
         * Initializes plugin variables and sets up WordPress hooks/actions.
         */
        protected function __construct() {
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 0 );
            add_action( 'admin_menu', array( $this, 'admin_menu' ), 0 );
            add_action( 'admin_init', array( $this, 'register_orb_form' ), 0 );
        }

        /**
         * Registers the submitted form value from the orb form
         */
        public function register_orb_form() {
            register_setting( 'orb_form_group', 'orb_form', array( $this, 'sanitize' ) );

            add_settings_section(
                'orb_form_id',
                'Spiros Orb Form',
                array( $this, 'print_orb_form_info' ),
                $this::UNIQUE_IDENTIFIER
            );

            add_settings_field(
                'full_name',
                'Full Name',
                array( $this, 'full_name_callback' ),
                $this::UNIQUE_IDENTIFIER,
                'orb_form_id'
            );
        }

        /**
         * Adds scripts to the appropriate pages
         * 
         * @return void
         */
        public function enqueue_scripts() {
            wp_enqueue_script( 'orb_ajax', plugin_dir_url( ORB_MAIN_FILE ) . '/js/orb.js' );
        }

        /**
         * Sets up the admin menu and page
         *
         * @return void
         */
        public function admin_menu() {
            add_options_page(
                $this::ADMIN_PAGE_TITLE,
                $this::ADMIN_PAGE_DESCRIPTION,
                'manage_options',
                $this::UNIQUE_IDENTIFIER,
                array( $this, 'orb_form' )
            );
        }

        /**
         * This actually executes a backend CURL request using the WP functionality.
         * Note: Could've gone raw curl here
         * 
         * @param string full_name Contains the name entered in the form
         * 
         * @return curl response | false
         */
        public static function executeRequest($full_name = false) {
            if ($full_name) {
                $data = array( 'full_name' => $full_name );
                $response = wp_remote_post( 'https://orbisius.com/apps/qs_cmd/?json', array( 'data' => $data ) );
                return $response;
            }
            return false;
        }

        /**
         * Actually outputs the orb form admin page
         *
         * @return void
         */
        public function orb_form() {
            $this->options = get_option( 'orb_form' );

            $response = false;
            if (isset($_REQUEST) && isset($_REQUEST['settings-updated']) && $_REQUEST['settings-updated'] == true) {
                if (isset($this->options['full_name']) && $this->options['full_name'] != '') {
                    $response = $this::executeRequest($this->options['full_name']);
                }
            }
        ?>
        <div class="wrap">
            <h1>Orb Form</h1>
            <form method="post" action="options.php">
                <?php
                    settings_fields( 'orb_form_group' );
                    do_settings_sections( $this::UNIQUE_IDENTIFIER );
                ?>
                <button id="submit_ajax">Submit (Ajax Style)!</button>
                <?php print submit_button(); ?>
            </form>
            <div id="return">
            <?php if ($response) : ?>
                <?php $body = json_decode($response['body']); ?>
                <table class="form-table">
                    <tr><td>JSON: </td><td><?php ($body->body == '') ? print 'Empty' : print $body->body; ?></td></tr>
                    <tr><td>Status: </td><td><?php ($body->status == '') ? print 'Empty' : print $body->status; ?></td></tr>
                    <tr><td>Message: </td><td><?php ($body->message == '') ? print 'Empty' : print $body->message; ?></td></tr>
                    <tr><td>Full Raw Response:</td><td><textarea><?php print_r($response); ?></textarea></td>
                </table>
            <?php endif; ?>
            </div>
        </div>
        <?php
        }

        /**
         * Sanitize each setting field as needed
         *
         * @param array $input Contains all settings fields as array keys
         * 
         * @return array $new_input Contains sanitized field values
         */
        public function sanitize( $input ) {
            $new_input = array();

            if ( isset( $input['full_name'] ) )
                $new_input['full_name'] = sanitize_text_field( $input['full_name'] );

            return $new_input;
        }

        /** 
         * Get the settings option array and print one of its values
         * 
         * @return void
         */
        public function full_name_callback() {
            printf(
                '<input type="text" id="full_name" name="orb_form[full_name]" value="%s" />',
                isset( $this->options['full_name'] ) ? esc_attr( $this->options['full_name']) : ''
            );
        }

        /** 
         * Print the Section text
         * 
         * @return void
         */
        public function print_orb_form_info() {
            print 'Enter your settings below and then choose the ajax method or the POST submission method:';
        }
    }
}