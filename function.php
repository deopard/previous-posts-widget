<?php
/**
 * Plugin Name: Previous Posts Widget
 * Plugin URI: https://github.com/deopard/previous-posts-widget
 * Description: Show previous posts of the current post in a widget.
 * Verson: 1.0
 * Author: Tom Kim
 * Author URI: https://thetomkim.com
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function enqueue_previous_posts_widget_style() {
	wp_enqueue_style( 'previous_posts_widget_css', plugins_url('css/style.css', __FILE__) );
}
add_action('wp_enqueue_scripts', 'enqueue_previous_posts_widget_style');

class Previous_Posts_Widget extends WP_Widget {
  public function __construct() {
    $widget_ops = array(
      'classname' => 'previous-posts-widget',
			'description' => 'Show previous posts of current post.',
			'customize_selective_refresh' => true,
    );
    parent::__construct('previous-posts-widget', __('Previous Posts Widget'), $widget_ops);
    $this->alt_option_name = 'previous-posts-widget';
  }

  public function widget($args, $instance) {
		if ( ! is_single() ) {
			return;
		}

    if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Previous Posts' );

		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number ) {
			$number = 5;
		}
		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;

		$post_id = get_the_ID();

		$r = new WP_Query( apply_filters( 'widget_posts_args', array(
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'date_query'          => array(
				array(
					'before'          => get_the_date('c', $post_id),
					'inclusive'       => false,
        ),
			)
		), $instance ) );

		if ( ! $r->have_posts() ) {
			return;
		}
		?>
		<?php echo $args['before_widget']; ?>
		<?php
		if ( $title ) {
			// echo $args['before_title'] . $title . $args['after_title'];
			echo '<h2 class="previous-posts-widget__title">' . $title . '</h2>';
		}
		?>
  	<?php foreach ( $r->posts as $previous_post ) : ?>
			<?php
				$post_title = get_the_title( $previous_post->ID );
				$title      = ( ! empty( $post_title ) ) ? $post_title : __( '(no title)' );
			?>
				<a href="<?php the_permalink( $previous_post->ID ); ?>" class="previous-posts-widget__post">
					<div class="previous-posts-widget__post">
						<?php echo get_the_post_thumbnail( $previous_post->ID, 'thumbnail', 'class=previous-posts-widget__post__image'); ?>
						<div class="previous-posts-widget__post__title">
							<?php echo $title ; ?>
						</div>

						<?php if ( $show_date ) : ?>
							<div class="previous-posts-widget__post__date">
								<?php echo get_the_date( 'Y.m.d', $previous_post->ID ); ?>
							</div>
						<?php endif; ?>
					</div>
				</a>
		<?php endforeach; ?>
		<?php
		echo $args['after_widget'];
		?>
		<?php
	}

		/**
	 * Handles updating the settings for the current Previous Posts widget instance.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];
		$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		return $instance;
	}

	/**
	 * Outputs the settings form for the Previous Posts widget.
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 6;
		$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
		<input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox"<?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?' ); ?></label></p>
<?php
	}
}

function register_previous_posts_widget() {
	register_widget('Previous_Posts_Widget');
}

add_action('widgets_init', 'register_previous_posts_widget');
