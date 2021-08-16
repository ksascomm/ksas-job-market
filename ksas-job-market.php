<?php
/**
 * Plugin Name: KSAS Job Market Candidate
 * Plugin URI:  http://krieger.jhu.edu/
 * Description: The people plugin must also be activated. This adds the metabox and widget for job market candidates.  Built specifically for the Department of Economics. Will need to modify the widget text each academic year.
 * Version: 3.0
 * Author: KSAS Communications
 * Author URI: mailto:ksasweb@jhu.edu
 * License: GPL2
 */

/** Set up Job Candidate Metabox */
$jobcandidatedetails_6_metabox = array(
	'id'       => 'jobcandidatedetails',
	'title'    => 'Job Candidate Details',
	'page'     => array( 'people' ),
	'context'  => 'normal',
	'priority' => 'low',
	'fields'   => array(

		array(
			'name'        => 'Thesis Title',
			'desc'        => '',
			'id'          => 'ecpt_thesis',
			'class'       => 'ecpt_thesis',
			'type'        => 'text',
			'rich_editor' => 0,
			'max'         => 0,
			'std'         => '',
		),

		array(
			'name'        => 'Fields',
			'desc'        => '',
			'id'          => 'ecpt_fields',
			'class'       => 'ecpt_fields',
			'type'        => 'text',
			'rich_editor' => 0,
			'max'         => 0,
			'std'         => '',
		),

		array(
			'name'        => 'Main Advisor',
			'desc'        => '',
			'id'          => 'ecpt_advisor',
			'class'       => 'ecpt_advisor',
			'type'        => 'text',
			'rich_editor' => 0,
			'max'         => 0,
			'std'         => '',
		),
	),
);
/** Function to add meta boxes */
function ecpt_add_jobcandidatedetails_6_meta_box() {

	global $jobcandidatedetails_6_metabox;

	foreach ( $jobcandidatedetails_6_metabox['page'] as $page ) {
		add_meta_box( $jobcandidatedetails_6_metabox['id'], $jobcandidatedetails_6_metabox['title'], 'ecpt_show_jobcandidatedetails_6_box', $page, 'normal', 'low', $jobcandidatedetails_6_metabox );
	}
}
add_action( 'admin_menu', 'ecpt_add_jobcandidatedetails_6_meta_box' );


/** Function to show meta boxes */
function ecpt_show_jobcandidatedetails_6_box() {
	global $post;
	global $jobcandidatedetails_6_metabox;
	global $ecpt_prefix;
	global $wp_version;

	// Use nonce for verification.
	echo '<input type="hidden" name="ecpt_jobcandidatedetails_6_meta_box_nonce" value="', wp_create_nonce( basename( __FILE__ ) ), '" />';

	echo '<table class="form-table">';

	foreach ( $jobcandidatedetails_6_metabox['fields'] as $field ) {
		// get current post meta data.

		$meta = get_post_meta( $post->ID, $field['id'], true );

		echo '<tr>',
				'<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
				'<td class="ecpt_field_type_' . str_replace( ' ', '_', $field['type'] ) . '">';
		switch ( $field['type'] ) {
			case 'text':
				echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" /><br/>', '', $field['desc'];
				break;

		}
		echo '<td>',
			'</tr>';
	}

	echo '</table>';
}

add_action( 'save_post', 'ecpt_jobcandidatedetails_6_save' );

/** Save data from meta box */
function ecpt_jobcandidatedetails_6_save( $post_id ) {
	global $post;
	global $jobcandidatedetails_6_metabox;

	// verify nonce.
	if ( ! isset( $_POST['ecpt_jobcandidatedetails_6_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['ecpt_jobcandidatedetails_6_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// check autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	// check permissions.
	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	foreach ( $jobcandidatedetails_6_metabox['fields'] as $field ) {

		$old = get_post_meta( $post_id, $field['id'], true );
		$new = $_POST[ $field['id'] ];

		if ( $new && $new != $old ) {
			if ( $field['type'] == 'date' ) {
				$new = ecpt_format_date( $new );
				update_post_meta( $post_id, $field['id'], $new );
			} else {
				update_post_meta( $post_id, $field['id'], $new );

			}
		} elseif ( '' == $new && $old ) {
			delete_post_meta( $post_id, $field['id'], $old );
		}
	}
}

/*************Job Market Candidate Widget*****************/
add_action( 'widgets_init', 'ksas_register_jobcandidate_widgets' );

/** Add Job Candidate Widget */
function ksas_register_jobcandidate_widgets() {
	register_widget( 'job_candidate_Widget' );
}


// Define job candidate widget.
class Job_Candidate_Widget extends WP_Widget {

	public function __construct() {
		$widget_options = array(
			'classname'   => 'widget_job_candidate',
			'description' => __( 'Job Market Candidate Profile' ),
		);
		parent::__construct( 'job-candidate-widget', 'Job Candidate Profile', $widget_options );
	}

	public function form( $instance ) {

		/* Set up some default widget settings. */
		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : ''; ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input type="text" name="<?php echo $this->get_field_name('title');?>" id="<?php echo $this->get_field_id('title'); ?>" value="<?php echo $title; ?>">
		</p>

		<?php
	}

	/** Update/Save the widget settings. */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	public function widget( $args, $instance ) {
		$title = $instance['title'];

		echo $args['before_widget'];

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		$jobmarket_widget_query = new WP_Query(
			array(
				'post_type'      => 'people',
				'role'           => 'job-market-candidate',
				'orderby'        => 'rand',
				'posts_per_page' => 1,
			)
		);

		if ( $jobmarket_widget_query->have_posts() ) :
			while ( $jobmarket_widget_query->have_posts() ) :
				$jobmarket_widget_query->the_post();
				global $post;
				?>
				<article class="row" aria-labelledby="post-<?php the_ID(); ?>" >
					<div class="small-12 columns">
						<?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail(
								'directory',
								array(
									'class' => 'floatleft',
									'alt'   => get_the_title(),
								)
							); }
						?>
						<h5><a href="<?php the_permalink(); ?>" id="post-<?php the_ID(); ?>" ><?php the_title(); ?><span class="link"></span></a></h5>
						<p><strong>Thesis:&nbsp;</strong>
						<?php
						if ( get_post_meta( $post->ID, 'ecpt_thesis', true ) ) {
							echo get_post_meta( $post->ID, 'ecpt_thesis', true ); }
						?>
						</p>
					</div>
				</article>
				<?php endwhile; ?>
		<article aria-label="job-market-candidate archives">
			<p class="jmc-archive-link"><a href="<?php bloginfo( 'url' ); ?>/directoryindex/job-market/">More job market candidates <span class="fa fa-chevron-circle-right" aria-hidden="true"></span></a></p>
		</article>
			<?php
	endif;
		echo $args['after_widget'];
	}

}