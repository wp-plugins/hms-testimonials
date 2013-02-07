<?php

function hms_testimonials_widgets() {
	register_widget('HMS_Testimonials_View');
	register_widget('HMS_Testimonials_Rotator');
}

class HMS_Testimonials_View extends WP_Widget {

	public function __construct() {
		parent::__construct('hms_testimonial_view', 'HMS Testimonals', array('description' => __('Show 1 or several testimonials')));
	}

	public function form($instance) {

		$title = (isset($instance[ 'title' ])) ? $instance[ 'title' ] : __( 'Testimonials');
		$numshow = (isset($instance['numshow'])) ? $instance['numshow'] : 0;
		$show = (isset($instance['show'])) ? $instance['show'] : 'all';
		$show_value = (isset($instance['show_value'])) ? $instance['show_value'] : '';
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show' ); ?>"><?php _e('Display:'); ?></label>
			<select name="<?php echo $this->get_field_name( 'show' ); ?>" id="<?php echo $this->get_field_id( 'show' ); ?>">
				<option value="all" <?php if (esc_attr( $show )=='all') echo ' selected="selected"'; ?>>All</option>
				<option value="group" <?php if (esc_attr( $show )=='group') echo ' selected="selected"'; ?>>Group</option>
				<option value="single" <?php if (esc_attr( $show )=='single') echo ' selected="selected"'; ?>>Single</option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_value' ); ?>"><?php _e('Show Value:'); ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'show_value' ); ?>" name="<?php echo $this->get_field_name( 'show_value' ); ?>" value="<?php echo esc_attr( $show_value ); ?>" />
		</p>
		<p>Enter the Group ID or Testimonial ID if applicable.</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'numshow' ); ?>"><?php _e( 'Number to Show:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'numshow' ); ?>" name="<?php echo $this->get_field_name( 'numshow' ); ?>" type="text" value="<?php echo esc_attr( $numshow ); ?>" style="width:25px;" />
		</p>
		<p>Set to 0 to show all</p>
		
		<?php 
	}

	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['numshow'] = (int)$new_instance['numshow'];
		$instance['show'] = (isset($new_instance['show'])) ? $new_instance['show'] : 'all';
		$instance['show_value'] = @$new_instance['show_value'];
		return $instance;

	}

	public function widget($args, $instance) {
		global $wpdb, $blog_id;
		if (!isset($instance['show']))
			$instance['show'] = 'all';
		if (!isset($instance['show_value']))
			$instance['show_value'] = 0;

		if (isset($instance['numshow']) && ((int)$instance['numshow'] > 0))
			$limit = "LIMIT ".(int)$instance['numshow'];
		else
			$limit = '';

		switch($instance['show']) {
			case 'single':
				$get = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."hms_testimonials` WHERE `id` = ".(int)$instance['show_value']." AND `display` = 1 AND `blog_id` = ".(int)$blog_id, ARRAY_A);
				$single = 1;
			break;
			case 'group':
				$get = $wpdb->get_results("SELECT t.* FROM `".$wpdb->prefix."hms_testimonials` AS t INNER JOIN `".$wpdb->prefix."hms_testimonials_group_meta` AS m ON m.testimonial_id = t.id WHERE m.group_id = ".(int)$instance['show_value']." AND t.blog_id = ".$blog_id." AND t.display = 1 ORDER BY m.display_order ASC ".$limit, ARRAY_A);
				$single = 0;
			break;
			case 'all':
			default:
				$get = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."hms_testimonials` WHERE `display` = 1 AND `blog_id` = ".(int)$blog_id." ORDER BY `display_order` ASC ".$limit, ARRAY_A);
				$single = 0;
			break;
		}

		if (count($get)<1)
			return true;

		echo $args['before_widget'];
		if (!empty($instance['title']))
				echo $args['before_title'].$instance['title'].$args['after_title'];

		if ($single==1) {
			echo nl2br($get['testimonial']).'<br />'.nl2br($get['name']);
			if ($get['url']!='') echo '<br />'.$get['url'];

			echo '<br /><br />';
		} else {
			foreach($get as $g) {
				echo nl2br($g['testimonial']).'<br />'.nl2br($g['name']);
				if ($g['url']!='') echo '<br />'.$g['url'];

				echo '<br /><br />';
			}
		}
		echo $args['after_widget'];

	}

}

class HMS_Testimonials_Rotator extends WP_Widget {

	public function __construct() {
		parent::__construct('hms_testimonial_rotator', 'HMS Testimonial Rotator', array('description' => __('Rotates your testimonials')));
	}

	public function form($instance) {
		global $wpdb;

		$title = (isset($instance[ 'title' ])) ? $instance[ 'title' ] : __( 'Testimonials');
		$group = (isset($instance[ 'group' ])) ? $instance[ 'group' ] : 0;
		$seconds = (isset($instance[ 'seconds' ])) ? $instance[ 'seconds' ] : 10;

		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'group' ); ?>"><?php _e('Group:'); ?></label>
			<select name="<?php echo $this->get_field_name( 'group' ); ?>" id="<?php echo $this->get_field_id( 'group' ); ?>">
				<option value="all" <?php if (esc_attr( $group )=='0') echo ' selected="selected"'; ?>>All</option>
				<?php
				$get_groups = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."hms_testimonials_groups` ORDER BY `name` ASC", ARRAY_A);
				foreach($get_groups as $g):
					echo '<option value="'.$g['id'].'"'; if ($group == $g['id']) echo ' selected="selected"';  echo '>'.$g['name'].'</option>';
				endforeach;
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'seconds' ); ?>"><?php _e( 'Seconds Between:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'seconds' ); ?>" name="<?php echo $this->get_field_name( 'seconds' ); ?>" type="text" value="<?php echo esc_attr( $seconds ); ?>" style="width:25px;" />
		</p>
		
		
		<?php 
	}

	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['group'] = (int)$new_instance['group'];
		$instance['seconds'] = (int)$new_instance['seconds'];
		
		return $instance;
	}

	public function widget($args, $instance) {
		global $wpdb, $blog_id;

		if (!isset($instance['group']))
			$instance['group'] = 0;

		if ($instance['group'] == 0) {
			$get = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."hms_testimonials` WHERE `display` = 1 AND `blog_id` = ".(int)$blog_id." ORDER BY `display_order` ASC ", ARRAY_A);
		} else {
			$get = $wpdb->get_results("SELECT t.* FROM `".$wpdb->prefix."hms_testimonials` AS t INNER JOIN `".$wpdb->prefix."hms_testimonials_group_meta` AS m ON m.testimonial_id = t.id WHERE m.group_id = ".(int)$instance['group']." AND t.blog_id = ".$blog_id." AND t.display = 1 ORDER BY m.display_order ASC", ARRAY_A);
		}

		if (count($get)<1)
			return true;

		$identifier = $this->_randomstring();

		echo $args['before_widget'];
		if (!empty($instance['title']))
			echo $args['before_title'].$instance['title'].$args['after_title'];

		echo '<div id="hms-testimonial-'.$identifier.'">';

			echo nl2br($get[0]['testimonial']).'<br />'.nl2br($get[0]['name']);
			if ($get[0]['url']!='') echo '<br />'.$get[0]['url'];

		echo '</div>';
		?>

		<div style="display:none;" id="hms-testimonial-list-<?php echo $identifier; ?>">
			<?php
				foreach($get as $g) {
					echo '<span>'.nl2br($g['testimonial']).'<br />'.nl2br($g['name']);
					if ($g['url']!='') echo '<br />'.$g['url'];
					echo '</span>';
				} ?>
		</div>

		<script type="text/javascript">
			var index_<?php echo $identifier; ?> = 1;
			jQuery(document).ready(function() {
				setInterval(function() {
					var nextitem = jQuery("#hms-testimonial-list-<?php echo $identifier; ?> span").get(index_<?php echo $identifier; ?>);
					if (nextitem == undefined) {
						index_<?php echo $identifier; ?> = 0;
						var nextitem = jQuery("#hms-testimonial-list-<?php echo $identifier; ?> span").get(0);
					}
					jQuery("#hms-testimonial-<?php echo $identifier; ?>").fadeOut('slow', function(){ jQuery(this).html(nextitem.innerHTML)}).fadeIn();
					index_<?php echo $identifier; ?> = index_<?php echo $identifier; ?> + 1;
				}, <?php echo $instance['seconds']; ?>000);
			});
			
		</script>
		<?php
		
		echo $args['after_widget'];
	}

	public function _randomstring() {
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    	$randstring = '';
    	for ($i = 0; $i < 5; $i++)
            $randstring .= $characters[rand(0, strlen($characters))];
        
    	return $randstring;
	}

}