<?php
/**
 * Plugin Name: KSAS Job Market Candidate
 * Plugin URI:  http://krieger.jhu.edu/
 * Description: Adds metaboxes and a random-rotation widget for Job Market Candidates.
 * Version: 4.0
 * Author: KSAS Communications
 * Author URI: mailto:ksasweb@jhu.edu
 * License: GPL2
 * Text Domain: ksas-job-market
 *
 * @package KSAS_Job_Market
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the Widget Class File.
 * PHPCS requires the "class-" prefix and hyphenated lowercase naming.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-ksas-job-candidate-widget.php';

/**
 * Returns an array of field IDs and labels.
 *
 * @return array
 */
function ksas_get_job_candidate_fields() {
	return array(
		'ecpt_thesis'  => __( 'Thesis Title', 'ksas-job-market' ),
		'ecpt_fields'  => __( 'Fields', 'ksas-job-market' ),
		'ecpt_advisor' => __( 'Main Advisor', 'ksas-job-market' ),
	);
}

/**
 * Registers the metabox for the 'people' post type.
 */
function ksas_add_job_candidate_meta_box() {
	add_meta_box(
		'jobcandidatedetails',
		__( 'Job Candidate Details', 'ksas-job-market' ),
		'ksas_show_job_candidate_box',
		array( 'people' ),
		'normal',
		'low'
	);
}
add_action( 'add_meta_boxes', 'ksas_add_job_candidate_meta_box' );

/**
 * Renders the HTML for the metabox fields.
 *
 * @param WP_Post $post The current post object.
 */
function ksas_show_job_candidate_box( $post ) {
	$fields = ksas_get_job_candidate_fields();

	// Security Nonce.
	wp_nonce_field( basename( __FILE__ ), 'ksas_job_candidate_nonce' );

	echo '<table class="form-table"><tbody>';
	foreach ( $fields as $id => $label ) {
		$meta_value = get_post_meta( $post->ID, $id, true );
		?>
		<tr>
			<th style="width:20%">
				<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			</th>
			<td>
				<input type="text" 
					name="<?php echo esc_attr( $id ); ?>" 
					id="<?php echo esc_attr( $id ); ?>" 
					value="<?php echo esc_attr( $meta_value ); ?>" 
					class="regular-text" style="width:97%" />
			</td>
		</tr>
		<?php
	}
	echo '</tbody></table>';
}

/**
 * Saves the metabox data when the post is saved.
 *
 * @param int $post_id The ID of the post being saved.
 */
function ksas_save_job_candidate_meta( $post_id ) {
	// Verify nonce.
	if ( ! isset( $_POST['ksas_job_candidate_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['ksas_job_candidate_nonce'] ) ), basename( __FILE__ ) ) ) {
		return;
	}

	// Stop autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check permissions.
	if ( isset( $_POST['post_type'] ) && 'page' === $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = ksas_get_job_candidate_fields();
	foreach ( $fields as $id => $label ) {
		if ( isset( $_POST[ $id ] ) ) {
			update_post_meta( $post_id, $id, sanitize_text_field( wp_unslash( $_POST[ $id ] ) ) );
		}
	}
}
add_action( 'save_post', 'ksas_save_job_candidate_meta' );

/**
 * Registers the Job Candidate Widget.
 */
function ksas_register_job_candidate_widget() {
	register_widget( 'KSAS_Job_Candidate_Widget' );
}
add_action( 'widgets_init', 'ksas_register_job_candidate_widget' );