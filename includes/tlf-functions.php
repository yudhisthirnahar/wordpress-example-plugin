<?php
/**
 * Common functions for Test Lead Form plugin.
 *
 * @package TLF
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Sanitize lead form data from POST request.
 *
 * @since   1.0.0
 * @param   array $post_data POST data array.
 * @return  array Sanitized lead data.
 */
function tlf_sanitize_lead_data( $post_data ) {
	$lead_data = array(
		'name'    => '',
		'phone'   => '',
		'email'   => '',
		'budget'  => '',
		'message' => '',
	);

	if ( isset( $post_data['name'] ) && ! empty( $post_data['name'] ) ) {
		$lead_data['name'] = sanitize_text_field( wp_unslash( $post_data['name'] ) );
	}
	if ( isset( $post_data['email'] ) && ! empty( $post_data['email'] ) ) {
		$lead_data['email'] = sanitize_email( wp_unslash( $post_data['email'] ) );
	}
	if ( isset( $post_data['phone'] ) && ! empty( $post_data['phone'] ) ) {
		$lead_data['phone'] = sanitize_text_field( wp_unslash( $post_data['phone'] ) );
	}
	if ( isset( $post_data['budget'] ) && ! empty( $post_data['budget'] ) ) {
		$lead_data['budget'] = sanitize_text_field( wp_unslash( $post_data['budget'] ) );
	}
	if ( isset( $post_data['message'] ) && ! empty( $post_data['message'] ) ) {
		$lead_data['message'] = sanitize_textarea_field( wp_unslash( $post_data['message'] ) );
	}

	return $lead_data;
}

/**
 * Format lead data as HTML content.
 *
 * @since   1.0.0
 * @param   array $lead_data Lead data array.
 * @return  string Formatted HTML content.
 */
function tlf_format_lead_content( $lead_data ) {
	$content  = '<p> <strong>Name :</strong> ' . esc_html( $lead_data['name'] ) . '</p>';
	$content .= '<p> <strong>Email Address :</strong> ' . esc_html( $lead_data['email'] ) . '</p>';
	$content .= '<p> <strong>Phone Number :</strong> ' . esc_html( $lead_data['phone'] ) . '</p>';
	$content .= '<p> <strong>Desired Budget :</strong> ' . esc_html( $lead_data['budget'] ) . '</p>';
	$content .= '<p> <strong>Message :</strong> ' . esc_html( $lead_data['message'] ) . '</p>';

	return $content;
}

/**
 * Generate customer post title from lead data.
 *
 * @since   1.0.0
 * @param   array $lead_data Lead data array.
 * @return  string Post title.
 */
function tlf_generate_customer_title( $lead_data ) {
	return $lead_data['name'] . ', ' . $lead_data['email'] . ', ' . $lead_data['phone'];
}

/**
 * Get default shortcode attributes.
 *
 * @since   1.0.0
 * @return  array Default attributes.
 */
function tlf_get_default_shortcode_attrs() {
	return array(
		'label_name'    => 'Name',
		'label_email'   => 'Email',
		'label_phone'   => 'Phone',
		'label_budget'  => 'Desired Budget',
		'label_message' => 'Message',
	);
}

/**
 * Get shortcode attribute value with default fallback.
 *
 * @since   1.0.0
 * @param   array  $atts    Shortcode attributes.
 * @param   string $key     Attribute key.
 * @param   string $default Default value.
 * @return  string Attribute value.
 */
function tlf_get_shortcode_attr( $atts, $key, $default = '' ) {
	return isset( $atts[ $key ] ) && ! empty( $atts[ $key ] ) ? $atts[ $key ] : $default;
}

/**
 * Generate HTML attribute string for maxlength.
 *
 * @since   1.0.0
 * @param   string $value Maxlength value.
 * @return  string HTML attribute string.
 */
function tlf_get_maxlength_attr( $value ) {
	return ! empty( $value ) ? " maxlength = '" . esc_attr( $value ) . "' " : '';
}

/**
 * Generate HTML attribute string for rows.
 *
 * @since   1.0.0
 * @param   string $value Rows value.
 * @return  string HTML attribute string.
 */
function tlf_get_rows_attr( $value ) {
	return ! empty( $value ) ? " rows = '" . esc_attr( $value ) . "' " : '';
}

/**
 * Generate HTML attribute string for cols.
 *
 * @since   1.0.0
 * @param   string $value Cols value.
 * @return  string HTML attribute string.
 */
function tlf_get_cols_attr( $value ) {
	return ! empty( $value ) ? " cols = '" . esc_attr( $value ) . "' " : '';
}

/**
 * Validate email address.
 *
 * @since   1.0.0
 * @param   string $email Email address.
 * @return  bool True if valid, false otherwise.
 */
function tlf_validate_email( $email ) {
	return is_email( $email );
}

/**
 * Validate phone number (basic validation).
 *
 * @since   1.0.0
 * @param   string $phone Phone number.
 * @return  bool True if valid, false otherwise.
 */
function tlf_validate_phone( $phone ) {
	// Remove common phone number characters for validation.
	$cleaned = preg_replace( '/[^0-9]/', '', $phone );
	return ! empty( $cleaned ) && strlen( $cleaned ) >= 10;
}

/**
 * Get plugin directory path.
 *
 * @since   1.0.0
 * @return  string Plugin directory path.
 */
function tlf_get_plugin_dir() {
	return plugin_dir_path( dirname( __FILE__ ) );
}

/**
 * Get plugin directory URL.
 *
 * @since   1.0.0
 * @return  string Plugin directory URL.
 */
function tlf_get_plugin_url() {
	return plugin_dir_url( dirname( __FILE__ ) );
}

/**
 * Get plugin basename.
 *
 * @since   1.0.0
 * @return  string Plugin basename.
 */
function tlf_get_plugin_basename() {
	return plugin_basename( dirname( dirname( __FILE__ ) ) . '/test-lead-form.php' );
}

/**
 * Save customer data as post meta.
 *
 * @since   1.0.0
 * @param   int   $post_id   Post ID.
 * @param   array $lead_data Lead data array.
 * @return  void
 */
function tlf_save_customer_meta( $post_id, $lead_data ) {
	if ( empty( $post_id ) || empty( $lead_data ) ) {
		return;
	}

	// Save each field as post meta with dynamic meta key.
	foreach ( $lead_data as $field_key => $field_value ) {
		if ( ! empty( $field_value ) ) {
			$meta_key = 'tlf_customer_' . $field_key;
			update_post_meta( $post_id, $meta_key, $field_value );
		}
	}
}

/**
 * Extract customer name from post meta.
 *
 * @since   1.0.0
 * @param   int $post_id Post ID.
 * @return  string Customer name.
 */
function tlf_extract_customer_name( $post_id ) {
	$name = get_post_meta( $post_id, 'tlf_customer_name', true );
	if ( ! empty( $name ) ) {
		return $name;
	}

	// Fallback: try to extract from post title.
	$post = get_post( $post_id );
	if ( $post && ! empty( $post->post_title ) ) {
		$parts = explode( ',', $post->post_title );
		if ( ! empty( $parts[0] ) ) {
			return trim( $parts[0] );
		}
	}

	return '';
}

/**
 * Extract customer email from post meta.
 *
 * @since   1.0.0
 * @param   int $post_id Post ID.
 * @return  string Customer email.
 */
function tlf_extract_customer_email( $post_id ) {
	$email = get_post_meta( $post_id, 'tlf_customer_email', true );
	if ( ! empty( $email ) ) {
		return $email;
	}

	// Fallback: try to extract from post title.
	$post = get_post( $post_id );
	if ( $post && ! empty( $post->post_title ) ) {
		$parts = explode( ',', $post->post_title );
		if ( ! empty( $parts[1] ) ) {
			return trim( $parts[1] );
		}
	}

	return '';
}

/**
 * Extract customer phone from post meta.
 *
 * @since   1.0.0
 * @param   int $post_id Post ID.
 * @return  string Customer phone.
 */
function tlf_extract_customer_phone( $post_id ) {
	$phone = get_post_meta( $post_id, 'tlf_customer_phone', true );
	if ( ! empty( $phone ) ) {
		return $phone;
	}

	// Fallback: try to extract from post title.
	$post = get_post( $post_id );
	if ( $post && ! empty( $post->post_title ) ) {
		$parts = explode( ',', $post->post_title );
		if ( ! empty( $parts[2] ) ) {
			return trim( $parts[2] );
		}
	}

	return '';
}

/**
 * Get customer budget from post meta.
 *
 * @since   1.0.0
 * @param   int $post_id Post ID.
 * @return  string Customer budget.
 */
function tlf_get_customer_budget( $post_id ) {
	return get_post_meta( $post_id, 'tlf_customer_budget', true );
}

/**
 * Get customer message from post meta.
 *
 * @since   1.0.0
 * @param   int $post_id Post ID.
 * @return  string Customer message.
 */
function tlf_get_customer_message( $post_id ) {
	return get_post_meta( $post_id, 'tlf_customer_message', true );
}

/**
 * Get customer timestamp from post meta.
 *
 * @since   1.0.0
 * @param   int $post_id Post ID.
 * @return  string Customer timestamp.
 */
function tlf_get_customer_timestamp( $post_id ) {
	return get_post_meta( $post_id, 'tlf_customer_timestamp', true );
}
