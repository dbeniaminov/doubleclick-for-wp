<?php

/**
 * Adds DoubleClick_Widget widget.
 */
class DoubleClick_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'doubleclick_widget', // Base ID
			__( 'DoubleClick Ad', 'dfw' ), // Name
			array( 'description' => __( 'Serve ads from DFP.', 'dfw' ) ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		global $doubleclick;

		// prepare identifier parameter.
		$identifier = ! empty( $instance['identifier'] ) ? $instance['identifier'] : 'ident';

		// prepare size parameter.
		$sizes = $instance['sizes'];
		if ( ! empty( $sizes ) ) {
			foreach ( $sizes as $breakpoint => $size ) {
				if ( empty( $sizes[ $breakpoint ] ) ) {
					unset( $sizes[ $breakpoint ] );
				}
			}
		} else {
			printf(
				'<!-- %1$s -->',
				esc_html__( 'This DoubleClick for WordPress widget is not appearing because the widget has no sizes set for its breakpoints.', 'dfw' )
			);
			return;
		}

		// bugfix: replace $args with $dfw_args to prevent widget interference
		// prepare dfw_args parameter.
		$dfw_args = null;
		if ( $instance['lazyLoad'] ) {
			$dfw_args = array( 'lazyLoad' => true );
		}

		// begin actual widget output
		echo wp_kses_post( $args['before_widget'] );

		// print (optional) title.
		if ( ! empty( $instance['title'] ) ) {
			echo wp_kses_post( $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'] );
		}

		// and finally, place the ad.
		$doubleclick->place_ad( $identifier, $sizes, $dfw_args );

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		global $doubleclick;

		$identifier = ! empty( $instance['identifier'] ) ? $instance['identifier'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'identifier' ) ); ?>"><?php esc_html_e( 'Identifier:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'identifier' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'identifier' ) ); ?>" type="text" value="<?php echo esc_attr( $identifier ); ?>">
		</p>

		<?php if ( count( $doubleclick->breakpoints ) > 0 ) : $i = 0; ?>

			<p><strong>Size for breakpoints:</strong></p>

			<?php foreach ( $doubleclick->breakpoints as $breakpoint ) : ?>
				<p>
					<label><?php echo esc_html( $breakpoint->identifier ); ?> <em>(<?php echo esc_html( $breakpoint->min_width ); ?>px+)</em></label><br/>
					<input
						class="widefat"
						type="text"
						name="<?php echo esc_attr( $this->get_field_name( 'sizes' ) ); ?>[<?php echo esc_attr( $breakpoint->identifier ); ?>]"
						value="<?php echo esc_attr( $instance['sizes'][ $breakpoint->identifier ] ); ?>"
						>
				</p>

			<?php endforeach; ?>

			<p><hr/></p>

		<?php else : ?>

			<p>
			<label><strong>Size: </strong></label><br/>
				<input
					class="widefat"
					type="text"
					name="<?php echo esc_attr( $this->get_field_name( 'size' ) ); ?>"
					value="<?php echo esc_attr( $instance['size'] ); ?>"
					>
			</p>

		<?php endif; ?>

		<p><strong>Lazy Load?</strong></p>
		<p>
			<input
				class="checkbox"
				type="checkbox"
				name="<?php echo esc_attr( $this->get_field_name( 'lazyLoad' ) ); ?>"
				value="1"
				<?php if ( $instance['lazyLoad'] ) { echo 'checked';} ?>
				><label>Only load ad once it comes into view on screen.</label><br/>
		</p>
		<hr/><br>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['identifier'] = ( ! empty( $new_instance['identifier'] ) ) ? strip_tags( $new_instance['identifier'] ) : '';
		$instance['lazyLoad'] = ( ! empty( $new_instance['lazyLoad'] ) ) ? $new_instance['lazyLoad'] : 0 ;
		$instance['breakpoints'] = $new_instance['breakpoints'];
		$instance['sizes'] = $new_instance['sizes'];
		$instance['size'] = $new_instance['size'];
		return $instance;
	}

}

function dfw_register_widget() {
	register_widget( 'DoubleClick_Widget' );
}

add_action( 'widgets_init', 'dfw_register_widget' );
