<?php
/**
 * Plugin name: Test Lead Form Generation
 * Plugin URI: https://example.com
 * Description: Test Lead Form Generation for testing.
 * Version: 1.0.0
 * Author: Yudhisthir Nahar
 * Author URI: https://example.com
 * Text Domain: tlf
 * Domain Path: /languages
 *
 * @package TLF
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'TLF_PLUGIN_VER' ) ) {
	define( 'TLF_PLUGIN_VER', '1.0.0' );
}

if ( ! defined( 'TLF_PLUGIN_NAME' ) ) {
	define( 'TLF_PLUGIN_NAME', 'Test Lead Form Generation' );
}

// Include common functions.
require_once plugin_dir_path( __FILE__ ) . 'includes/tlf-functions.php';

/**
 * Main class.
 */
if ( ! class_exists( 'Test_Lead_Form' ) ) {
	/**
	 * Test Lead Form class.
	 *
	 * @since 1.0.0
	 */
	class Test_Lead_Form {

		/**
		 * Static property to hold our singleton instance.
		 *
		 * @since   1.0.0
		 * @access  static
		 * @var     bool|Test_Lead_Form
		 */
		private static $instance = false;

		/**
		 * Constructor
		 *
		 * @since   1.0.0
		 * @access  private
		 * @return  void
		 */
		private function __construct() {
			// Back end.

			add_action( 'plugins_loaded', array( $this, 'setlocale' ) );
			add_action( 'init', array( $this, 'init' ) );

			if ( is_admin() ) {
				$this->admin_hooks();
			}
			if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				$this->public_hooks();
			}

		}

		/**
		 * Constructor
		 *
		 * @since   1.0.0
		 * @access  public
		 * @return  void
		 */
		public function init() {
			add_filter( 'widget_text', 'shortcode_unautop' );
			add_filter( 'widget_text', 'do_shortcode' );

			add_action( 'wp_ajax_tlf_submit_customer', array( $this, 'submit_customer_callback' ) );
			add_action( 'wp_ajax_nopriv_tlf_submit_customer', array( $this, 'submit_customer_callback' ) );

			$this->register_custom_post_type();

			$this->register_short_code();
		}

		/**
		 * Registering Customer post type.
		 *
		 * @since   1.0.0
		 * @access  private
		 * @return  void
		 */
		private function register_custom_post_type() {
			$args = array(
				'labels'        => array(
					'name'               => __( 'Customers', 'tlf' ),
					'singular_name'      => __( 'Customer', 'tlf' ),
					'add_new'            => 'Add New',
					'add_new_item'       => 'Add New Customer',
					'edit'               => 'Edit',
					'edit_item'          => 'Edit Customer',
					'new_item'           => 'New Customer',
					'view'               => 'View',
					'view_item'          => 'View Customer',
					'search_items'       => 'Search Customers',
					'not_found'          => 'No Customers found',
					'not_found_in_trash' => 'No Customers found in Trash',
					'parent'             => 'Parent Customer',
				),
				'public'        => false,
				'menu_position' => 3,
				'supports'      => array( 'title', 'editor', 'custom-fields' ),
				'show_in_rest'  => true,
				'show_ui'       => true,
				'can_export'    => true,
				'hierarchical'  => false,
			);

			// Recommendation: add prefix to custom post type like tlf_customer.
			register_post_type( 'tlf_customer', $args );

			$args = array(
				'label'             => __( 'Categories', 'tlf' ),
				'public'            => false,
				'rewrite'           => false,
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_admin_column' => true,
			);

			register_taxonomy( 'customer_category', 'tlf_customer', $args );

			$args = array(
				'label'             => __( 'Tags', 'tlf' ),
				'public'            => false,
				'rewrite'           => false,
				'hierarchical'      => false,
				'show_ui'           => true,
				'show_admin_column' => true,
			);

			register_taxonomy( 'customer_tag', 'tlf_customer', $args );

		}

		/**
		 * Registering short code.
		 *
		 * @since   1.0.0
		 * @access  private
		 * @return  void
		 */
		private function register_short_code() {
			add_shortcode( 'tlf', array( $this, 'short_code_implementation' ) );
		}

		/**
		 * Short code implementation.
		 *
		 * @since   1.0.0
		 * @access  public
		 * @param   array  $atts    Shortcode attributes.
		 * @param   string $content Shortcode content.
		 * @return  string
		 */
		public function short_code_implementation( $atts, $content = null ) {
			// Labels.
			$defaults      = tlf_get_default_shortcode_attrs();
			$label_name    = tlf_get_shortcode_attr( $atts, 'label_name', $defaults['label_name'] );
			$label_email   = tlf_get_shortcode_attr( $atts, 'label_email', $defaults['label_email'] );
			$label_phone   = tlf_get_shortcode_attr( $atts, 'label_phone', $defaults['label_phone'] );
			$label_budget  = tlf_get_shortcode_attr( $atts, 'label_budget', $defaults['label_budget'] );
			$label_message = tlf_get_shortcode_attr( $atts, 'label_message', $defaults['label_message'] );

			// Maxlength.
			$maxlength_name    = tlf_get_maxlength_attr( tlf_get_shortcode_attr( $atts, 'maxlength_name', '' ) );
			$maxlength_email   = tlf_get_maxlength_attr( tlf_get_shortcode_attr( $atts, 'maxlength_email', '' ) );
			$maxlength_phone   = tlf_get_maxlength_attr( tlf_get_shortcode_attr( $atts, 'maxlength_phone', '' ) );
			$maxlength_budget  = tlf_get_maxlength_attr( tlf_get_shortcode_attr( $atts, 'maxlength_budget', '' ) );
			$maxlength_message = tlf_get_maxlength_attr( tlf_get_shortcode_attr( $atts, 'maxlength_message', '' ) );

			// Rows.
			$rows_message = tlf_get_rows_attr( tlf_get_shortcode_attr( $atts, 'rows_message', '' ) );
			// Cols.
			$cols_message = tlf_get_cols_attr( tlf_get_shortcode_attr( $atts, 'cols_message', '' ) );

			wp_enqueue_script( 'tlf-script' );
			ob_start();
			?>
			<div class="tlf-container">
				<?php
				if ( isset( $content ) && ! empty( $content ) ) {
					echo '<p>' . esc_html( $content ) . '</p>';
				}
				?>
				<form class="tlf-form">
					<input type="hidden" name="action" value="tlf_submit_customer">
					<?php wp_nonce_field( 'tlf_submit_customer', 'tlf_customer_nonce' ); ?>
					<div class="row">
						<div class="col-25">
							<label><?php echo esc_html( $label_name ); ?></label>
						</div>
						<div class="col-75">
							<input type="text" name="name" required <?php echo esc_attr( $maxlength_name ); ?> >
						</div>
					</div>
					<div class="row">
						<div class="col-25">
							<label><?php echo esc_html( $label_phone ); ?></label>
						</div>
						<div class="col-75">
							<input type="text" name="phone" required <?php echo esc_attr( $maxlength_phone ); ?> oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
						</div>
					</div>
					<div class="row">
						<div class="col-25">
							<label><?php echo esc_html( $label_email ); ?></label>
						</div>
						<div class="col-75">
							<input type="email" name="email" required <?php echo esc_attr( $maxlength_email ); ?> >
						</div>
					</div>
					<div class="row">
						<div class="col-25">
							<label><?php echo esc_html( $label_budget ); ?></label>
						</div>
						<div class="col-75">
							<input type="text" name="budget" <?php echo esc_attr( $maxlength_budget ); ?> oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
						</div>
					</div>

					<div class="row">
						<div class="col-25">
							<label><?php echo esc_html( $label_message ); ?></label>
						</div>
						<div class="col-75 ">
							<textarea name="message" <?php echo esc_attr( $maxlength_message ); ?> <?php echo esc_attr( $rows_message ); ?> <?php echo esc_attr( $cols_message ); ?> ></textarea>
						</div>
					</div>
					<div class="row">
						<input class="tlf-form-submit" type="submit" value="Submit">
					</div>
					<div class="tlf-message"></div>

				</form>
			</div>
			<?php

			return ob_get_clean();

		}

		/**
		 * If an instance exists, this returns it.  If not, it creates one and
		 * retuns it.
		 *
		 * @since   1.0.0
		 * @access  public
		 * @return  object
		 */
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Register all of the hooks related to the dashboard functionality
		 * of the plugin.
		 *
		 * @since   1.0.0
		 * @access  private
		 * @return  void
		 */
		private function admin_hooks() {
			// Add custom columns to customer post type.
			add_filter( 'manage_tlf_customer_posts_columns', array( $this, 'add_custom_posts_column' ) );
			add_action( 'manage_tlf_customer_posts_custom_column', array( $this, 'render_custom_posts_column' ), 10, 2 );
		}

		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since   1.0.0
		 * @access  private
		 * @return  void
		 */
		private function public_hooks() {
			// Front end.
			add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ), 10 );
		}

		/**
		 * Load textdomain.
		 *
		 * @since   1.0.0
		 * @access  public
		 * @return  void
		 */
		public function setlocale() {

			load_plugin_textdomain( 'test-lead-form', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		}

		/**
		 * Admin styles.
		 *
		 * @since   1.0.0
		 * @access  private
		 * @return  void
		 */
		public function admin_scripts() {

		}

		/**
		 * Submit ajax action.
		 *
		 * @since   1.0.0
		 * @access  public
		 * @return  void
		 */
		public function submit_customer_callback() {
			if ( ! isset( $_POST['tlf_customer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tlf_customer_nonce'] ) ), 'tlf_submit_customer' ) ) {
				wp_send_json_error(
					array(
						'message' => 'Invalid Token !!!',
					)
				);

			} else {
				// Process to save as customer post type.
				$lead_data = tlf_sanitize_lead_data( $_POST );

				$title   = tlf_generate_customer_title( $lead_data );
				$content = tlf_format_lead_content( $lead_data );

				$customer_post = array(
					'post_title'   => wp_strip_all_tags( $title ),
					'post_content' => $content,
					'post_type'    => 'tlf_customer',
					'post_status'  => 'publish',
				);

				// Insert the post into the database.
				$post_id = wp_insert_post( $customer_post );
				if ( $post_id ) {
					// Save customer data as post meta.
					tlf_save_customer_meta( $post_id, $lead_data );

					wp_send_json_success(
						array(
							'message'   => 'Lead Inserted !!!',
							'lead_data' => $lead_data, // To test.
						)
					);
				} else {
					wp_send_json_error(
						array(
							'message' => 'Error Inserting Lead !!!',
						)
					);
				}
			}
			exit;
		}


		/**
		 * Call front-end CSS.
		 *
		 * @since   1.0.0
		 * @access  public
		 * @return  void
		 */
		public function front_scripts() {
			wp_enqueue_style( 'tlf-style', plugins_url( 'public/css/tlf-public-style.css', __FILE__ ), array(), TLF_PLUGIN_VER, 'all' );
			// Register the script.
			wp_register_script( 'tlf-script', plugins_url( 'public/js/tlf-public-script.js', __FILE__ ), array( 'jquery' ), TLF_PLUGIN_VER, true );

			// Localize the script.
			wp_localize_script(
				'tlf-script',
				'tlfAjax',
				array(
					'ajaxurl'     => admin_url( 'admin-ajax.php' ),
					'_ajax_nonce' => wp_create_nonce( 'tlf_submit_customer' ),
				)
			);
		}

		/**
		 * Add custom column to customer posts list.
		 *
		 * @since   1.0.0
		 * @access  public
		 * @param   array $columns Existing columns.
		 * @return  array Modified columns.
		 */
		public function add_custom_posts_column( $columns ) {
			// Add customer name, email, and phone columns after title.
			$new_columns = array();
			foreach ( $columns as $key => $value ) {
				$new_columns[ $key ] = $value;
				if ( 'title' === $key ) {
					$new_columns['customer_name']  = __( 'Name', 'tlf' );
					$new_columns['customer_email'] = __( 'Email', 'tlf' );
					$new_columns['customer_phone'] = __( 'Phone', 'tlf' );
				}
			}
			return $new_columns;
		}

		/**
		 * Render custom column content.
		 *
		 * @since   1.0.0
		 * @access  public
		 * @param   string $column_name Column name.
		 * @param   int    $post_id     Post ID.
		 * @return  void
		 */
		public function render_custom_posts_column( $column_name, $post_id ) {
			if ( 'customer_name' === $column_name ) {
				$customer_name = tlf_extract_customer_name( $post_id );
				echo esc_html( $customer_name );
			} elseif ( 'customer_email' === $column_name ) {
				$customer_email = tlf_extract_customer_email( $post_id );
				if ( ! empty( $customer_email ) ) {
					echo '<a href="mailto:' . esc_attr( $customer_email ) . '">' . esc_html( $customer_email ) . '</a>';
				}
			} elseif ( 'customer_phone' === $column_name ) {
				$customer_phone = tlf_extract_customer_phone( $post_id );
				if ( ! empty( $customer_phone ) ) {
					echo '<a href="tel:' . esc_attr( $customer_phone ) . '">' . esc_html( $customer_phone ) . '</a>';
				}
			}
		}

		// End class.
	}
	// Instantiate class.
	$test_lead_form = Test_Lead_Form::get_instance();
}
