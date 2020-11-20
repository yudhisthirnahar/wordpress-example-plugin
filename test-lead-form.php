<?php
/*
Plugin name: Test Lead Form Generation
Plugin URI: https://example.com
Description: Test Lead Form Generation for testing.
Version: 1.0.0
Author: Yudhisthir
Author URI: https://example.com
Text Domain: test-lead-form
Domain Path: /languages
*/

if ( ! defined('WPINC') ) {
    die;
}

if( !defined( 'TLF_PLUGIN_VER' ) )
	define( 'TLF_PLUGIN_VER', '1.0.0' );

if( !defined( 'TLF_PLUGIN_NAME' ) )
	define( 'TLF_PLUGIN_NAME', 'Test Lead Form Generation' );

// Main class
if ( ! class_exists( 'Test_Lead_Form' ) ) {
	class Test_Lead_Form {

		/**
		 * Static property to hold our singleton instance
		 * @since	1.0.0
		 * @access	static
		 */
		static $instance = false;

		/**
		 * Constructor
		 *
		 * @since	1.0.0
		 * @access	private
		 * @return	void
		 */
		private function __construct() {
			// back end

			add_action( 'plugins_loaded', array( $this, 'setlocale' ) );
			add_action('init', array($this, 'init'));

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
		 * @since	1.0.0
		 * @access	public
		 * @return	void
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
		 * Registering Customer post type
		 *
		 * @since	1.0.0
		 * @access	private
		 * @return	void
		 */		
		private function register_custom_post_type(){
			$args = array(
				'labels' 		=> array(
					'name' 				 => __( 'Customers', 'tlf' ),
					'singular_name' 	 => __( 'Customer', 'tlf' ),
					'add_new' 			 => 'Add New',
					'add_new_item' 		 => 'Add New Customer',
					'edit' 				 => 'Edit',
					'edit_item' 		 => 'Edit Customer',
					'new_item' 			 => 'New Customer',
					'view' 				 => 'View',
					'view_item' 		 => 'View Customer',
					'search_items' 		 => 'Search Customers',
					'not_found' 		 => 'No Customers found',
					'not_found_in_trash' => 'No Customers found in Trash',
					'parent' 			 => 'Parent Customer'
				),
				'public' 		=> false,
				'menu_position' => 3,				
				'supports' 		=> array( 'title', 'editor', 'custom-fields' ),			
				'show_in_rest' 	=> true,				
				'show_ui' 		=> true,					
				'can_export' 	=> true,
				'hierarchical' 	=> false,
			);

			//recommendation: add prefix to custom post type like tlf_customer
			register_post_type( 'tlf_customer', $args );
						
			$args = array(
				'label'            	=> __( 'Categories', 'tlf' ),
				'public'       		=> false,
				'rewrite'      		=> false,
				'hierarchical' 		=> true,
				'show_ui'           => true,
        		'show_admin_column' => true,
			);
			
			register_taxonomy( 'customer_category', 'tlf_customer', $args );

			$args = array(
				'label'        		=> __( 'Tags', 'tlf' ),
				'public'       		=> false,
				'rewrite'      		=> false,
				'hierarchical' 		=> false,
				'show_ui'           => true,
        		'show_admin_column' => true,
			);
			
			register_taxonomy( 'customer_tag', 'tlf_customer', $args );
			
		}

		/**
		 * Registering short code
		 *
		 * @since	1.0.0
		 * @access	private
		 * @return	void
		 */
		private function register_short_code(){
			add_shortcode('tlf', array( $this, 'short_code_implementation' ) );			
		}

		/**
		 * short code implementation
		 *
		 * @since	1.0.0
		 * @access	public
		 * @return	string
		 */
		public function short_code_implementation( $atts, $content = null ){
			//labels	
			$label_name   = isset( $atts['label_name'] ) && !empty( $atts['label_name'] ) ? $atts['label_name']: 'Name';
			$label_email  = isset( $atts['label_email'] ) && !empty( $atts['label_email'] ) ? $atts['label_email']: 'Email';
			$label_phone  = isset( $atts['label_phone'] ) && !empty( $atts['label_phone'] ) ? $atts['label_phone']: 'Phone';
			$label_budget = isset( $atts['label_budget'] ) && !empty( $atts['label_budget'] ) ? $atts['label_budget']: 'Desired Budget';
			$label_message = isset( $atts['label_message'] ) && !empty( $atts['label_message'] ) ? $atts['label_message']: 'Message';

			//maxlength	
			$maxlength_name   = isset( $atts['maxlength_name'] ) && !empty( $atts['maxlength_name'] ) ? " maxlength = '".$atts['maxlength_name']."' ": '';
			$maxlength_email  = isset( $atts['maxlength_email'] ) && !empty( $atts['maxlength_email'] ) ? " maxlength = '".$atts['maxlength_email']."' ": '';
			$maxlength_phone  = isset( $atts['maxlength_phone'] ) && !empty( $atts['maxlength_phone'] ) ? " maxlength = '".$atts['maxlength_phone']."' ": '';
			$maxlength_budget = isset( $atts['maxlength_budget'] ) && !empty( $atts['maxlength_budget'] ) ? " maxlength = '".$atts['maxlength_budget']."' ": '';
			$maxlength_message = isset( $atts['maxlength_message'] ) && !empty( $atts['maxlength_message'] ) ? " maxlength = '".$atts['maxlength_message']."' ": '';

			//rows	
			$rows_message = isset( $atts['rows_message'] ) && !empty( $atts['rows_message'] ) ? " rows = '".$atts['rows_message']."' ": '';
			//cols
			$cols_message = isset( $atts['cols_message'] ) && !empty( $atts['cols_message'] ) ? " cols = '".$atts['cols_message']."' ": '';		
			
			wp_enqueue_script('jquery');		
			wp_enqueue_script('tlf-script');	
			ob_start();
			?>			
			<div class="tlf-container">
				<?php
					echo isset( $content ) && !empty( $content ) ? "<p>".$content."</p>": '';
				?>
				<form class="tlf-form">
					<input type="hidden" name="action" value="tlf_submit_customer">
					<input type="hidden" name="timestamp" value="<?php echo time(); ?>">
					<?php wp_nonce_field( 'tlf_submit_customer', 'tlf_customer_nonce' ); ?>	
					<div class="row">
						<div class="col-25">
							<label><?php echo $label_name; ?></label>
						</div>
						<div class="col-75">
							<input type="text" name="name" required <?php echo $maxlength_name; ?> >
						</div>
					</div>
					<div class="row">
						<div class="col-25">
							<label><?php echo $label_phone; ?></label>
						</div>
						<div class="col-75">
							<input type="text" name="phone" required <?php echo $maxlength_phone; ?> oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
						</div>
					</div>
					<div class="row">
						<div class="col-25">
							<label><?php echo $label_email; ?></label>
						</div>
						<div class="col-75">
							<input type="email" name="email" required <?php echo $maxlength_email; ?> >
						</div>
					</div>
					<div class="row">
						<div class="col-25">
							<label><?php echo $label_budget; ?></label>
						</div>
						<div class="col-75">
							<input type="text" name="budget" <?php echo $maxlength_budget; ?> oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
						</div>
					</div>
					
					<div class="row">
						<div class="col-25">
							<label><?php echo $label_message; ?></label>
						</div>
						<div class="col-75 ">
							<textarea name="message" <?php echo $maxlength_message; ?> <?php echo $rows_message; ?> <?php echo $cols_message; ?> ></textarea>
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
		 * @since	1.0.0
		 * @access	public
		 * @return	object
		 */

		public static function getInstance() {
			if ( !self::$instance )
				self::$instance = new self;
			return self::$instance;
		}
		
		/**
		 * Register all of the hooks related to the dashboard functionality
		 * of the plugin.
		 *
		 * @since	1.0.0
		 * @access	private
		 * @return	void
		 */
		private function admin_hooks() {
			
				
		}

		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since	1.0.0
		 * @access	private
		 * @return	void
		 */
		private function public_hooks() {
			// front end
			add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ), 10 );				
		}

		/**
		 * load textdomain
		 * @since	1.0.0
		 * @access	public
		 * @return	void
		 */

		public function setlocale() {

			load_plugin_textdomain( 'test-lead-form', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		}

		/**
		 * Admin styles
		 *
		 * @since	1.0.0
		 * @access	private
		 * @return	void
		 */

		public function admin_scripts() {			

		}

		/**
		 * submit ajax action
		 * @since	1.0.0
		 * @access	public
		 * @return	object
		 */

		public function submit_customer_callback() {	
							
			if ( ! isset( $_POST['tlf_customer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['tlf_customer_nonce'] ), 'tlf_submit_customer' ) ) {
				wp_send_json_error(
					array(
						'message' => 'Invalid Token !!!',											
					)
				);
				
			} else {

				//process to save as customer post type
				$lead_data = array(
					'name'      => '',
					'phone'     => '',
					'email'     => '',
					'budget'    => '',
					'message'   => '',
					'timestamp' => ''
				);

				if( isset( $_POST['name'] ) && !empty( $_POST['name'] ) ){
					$lead_data['name'] = sanitize_text_field( $_POST['name'] );
				}
				if( isset( $_POST['email'] ) && !empty( $_POST['email'] ) ){
					$lead_data['email'] = sanitize_email( $_POST['email'] );
				}
				if( isset( $_POST['phone'] ) && !empty( $_POST['phone'] ) ){
					$lead_data['phone'] = sanitize_text_field( $_POST['phone'] );
				}
				if( isset( $_POST['budget'] ) && !empty( $_POST['budget'] ) ){
					$lead_data['budget'] = sanitize_text_field( $_POST['budget'] );
				}
				if( isset( $_POST['message'] ) && !empty( $_POST['message'] ) ){
					$lead_data['message'] = sanitize_textarea_field( $_POST['message'] );
				}
				if( isset( $_POST['timestamp'] ) && !empty( $_POST['timestamp'] ) ){
					$lead_data['timestamp'] = sanitize_text_field( $_POST['timestamp'] );
				}

				$title = $lead_data['name'] . ", " . $lead_data['email'] . ", " . $lead_data['phone'];

				//alternative add to the meta
				$content = "<p> <strong>Name :</strong> " . $lead_data['name'] . "</p>";
				$content .= "<p> <strong>Email Address :</strong> " . $lead_data['email'] . "</p>";
				$content .= "<p> <strong>Phone Number :</strong> " . $lead_data['phone'] . "</p>";
				$content .= "<p> <strong>Desired Budget :</strong> " . $lead_data['budget'] . "</p>";
				$content .= "<p> <strong>Time :</strong> " . date( "Y-m-d H:i:s", $lead_data['timestamp'] ) . "</p>";
				$content .= "<p> <strong>Message :</strong> " . $lead_data['message'] . "</p>";				

				$customer_post = array(
					'post_title'    => wp_strip_all_tags( $title ),
					'post_content'  => $content,
					'post_type'     => 'tlf_customer',
					'post_status'   => 'publish',					
					// 'meta_input'    => array(
					// 	array(
					// 		'key'   => 'email',
					// 		'value' => $lead_data['email']
					// 	),
					// 	array(
					// 		'key'   => 'phone',
					// 		'value' => $lead_data['phone']
					// 	),
					// 	array(
					// 		'key'   => 'email',
					// 		'value' => $lead_data['budget']
					// 	),
					// 	array(
					// 		'key'   => 'message',
					// 		'value' => $lead_data['message']
					// 	),
					// 	array(
					// 		'key'   => 'timestamp',
					// 		'value' => $lead_data['timestamp']
					// 	)						
					// )			
				);
				
				// Insert the post into the database
				$post_id = wp_insert_post( $customer_post );
				if( $post_id ){
					wp_send_json_success(
						array(
							'message'   => 'Lead Inserted !!!',						
							'lead_data' => $lead_data //to test						
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
		 * call front-end CSS
		 *
		 * @since	1.0.0
		 * @access	public
		 * @return	void
		 */

		public function front_scripts() {

			wp_enqueue_style( 'tlf-style', plugins_url('public/css/tlf-public-style.css', __FILE__), array(), TLF_PLUGIN_VER, 'all' );
			// register the script	
			wp_register_script( 'tlf-script', plugins_url( 'public/js/tlf-public-script.js', __FILE__ ), array(), TLF_PLUGIN_VER );	

			// localize the script
			wp_localize_script( 'tlf-script', 'tlfAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), '_ajax_nonce' => wp_create_nonce( "tlf_submit_customer" ) ));           			
		}

	/// end class
	}
	// Instantiate class
	$Test_Lead_Form = Test_Lead_Form::getInstance();
}