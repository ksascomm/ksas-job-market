<?php
/**
 * Job Candidate Widget Class.
 *
 * @package KSAS_Job_Market
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'KSAS_Job_Candidate_Widget' ) ) {
	/**
	 * Class KSAS_Job_Candidate_Widget
	 */
	class KSAS_Job_Candidate_Widget extends WP_Widget {

		/**
		 * Constructor.
		 */
		public function __construct() {
			parent::__construct(
				'job-candidate-widget',
				__( 'Job Candidate Profile', 'ksas-job-market' ),
				array( 'description' => __( 'Displays a random Job Market Candidate.', 'ksas-job-market' ) )
			);
		}

		/**
		 * Widget backend form.
		 *
		 * @param array $instance Current settings.
		 */
		public function form( $instance ) {
			$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'ksas-job-market' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
			</p>
			<?php
		}

		/**
		 * Update widget settings.
		 *
		 * @param array $new_instance New settings.
		 * @param array $old_instance Old settings.
		 * @return array
		 */
		public function update( $new_instance, $old_instance ) {
			$instance          = array();
			$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
			return $instance;
		}

		/**
		 * Render widget output.
		 *
		 * @param array $args     Display arguments.
		 * @param array $instance Saved settings.
		 */
		public function widget( $args, $instance ) {
			echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			if ( ! empty( $instance['title'] ) ) {
				$title = apply_filters( 'widget_title', $instance['title'] );
				echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			$query_args = array(
				'post_type'      => 'people',
				'posts_per_page' => 1,
				'orderby'        => 'rand',
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'role',
						'field'    => 'slug',
						'terms'    => 'job-market-candidate',
					),
				),
			);

			$job_query = new WP_Query( $query_args );

			if ( $job_query->have_posts() ) :
				while ( $job_query->have_posts() ) :
					$job_query->the_post();
					$thesis  = get_post_meta( get_the_ID(), 'ecpt_thesis', true );
					$website = get_post_meta( get_the_ID(), 'ecpt_website', true );
					?>
					<article>
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>">
								<?php
								the_post_thumbnail(
									'directory',
									array(
										'class' => 'floatleft',
										'alt'   => get_the_title(),
									)
								);
								?>
							</a>
						<?php endif; ?>
						
						<h3>
							<?php if ( $website ) : ?>
								<a href="<?php echo esc_url( $website ); ?>" title="<?php the_title(); ?>'s website" target="_blank">
									<?php the_title(); ?>
									<span class="fa-light fa-square-up-right" aria-hidden="true"></span>
								</a>
							<?php else : ?>
							<a href="<?php the_permalink(); ?>">
								<?php the_title(); ?>
							</a>
							<?php endif; ?>
						</h3>

						<?php if ( $thesis ) : ?>
							<p>
								<strong><?php esc_html_e( 'Thesis:', 'ksas-job-market' ); ?></strong> 
								<?php echo esc_html( $thesis ); ?>
							</p>
						<?php endif; ?>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
				?>

				<p class="view-more-link">
					<a href="<?php echo esc_url( home_url( '/people/job-market/' ) ); ?>">
						<?php esc_html_e( 'View all Candidates', 'ksas-job-market' ); ?> 
						<span class="fa fa-chevron-circle-right" aria-hidden="true"></span>
					</a>
				</p>
				<?php
			endif;

			echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}