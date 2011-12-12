<?php
/*
 * Plugin Name: Top Posts & Pages Widget
 * Plugin URI: http://mattrude.com/projects/top-posts-pages-widget/
 * Description: Displays the top Posts & Pages from the WordPress.com Stats plugin, now part of the <a href="http://jetpack.me/" target="_blank">Jetpack</a> suite. The Jetpack plugin is required to use this plugin.
 * Author: Matt Rude
 * Author URI: http://mattrude.com
 * Version: 0.2
 * License: GPLv2
 */

class top_posts_n_pages_widget extends WP_Widget {
  function top_posts_n_pages_widget() {
    $currentLocale = get_locale();
    if(!empty($currentLocale)) {
      $moFile = dirname(__FILE__) . "/languages/top-posts-n-pages-widget." . $currentLocale . ".mo";
      if(@file_exists($moFile) && is_readable($moFile)) load_textdomain('top-posts-n-pages-widget', $moFile);
    }

    $top_posts_n_pages_widget_name = __('Top Posts & Pages', 'top-posts-n-pages-widget');
    $top_posts_n_pages_widget_description = __('Displays the top Posts & Pages from the WordPress.com status plugin.', 'top-posts-n-pages-widget');
    $widget_ops = array('classname' => 'top_posts_n_pages_widget', 'description' => $top_posts_n_pages_widget_description );
    $this->WP_Widget('top_posts_n_pages_widget', $top_posts_n_pages_widget_name, $widget_ops);
  } 

  function widget($args, $instance) {
    extract($args);
    $tpp_widget_title = strip_tags($instance['widget_title']);
    $tpp_number_of_posts = strip_tags($instance['number_of_posts']);
    $tpp_show_pages = $instance['show_pages'];
    $tpp_show_attachments = $instance['show_attachments'];

    if ($tpp_widget_title == "") {
      $tpp_widget_title = __('Top Posts','top-posts-n-pages-widget');
    }

    if ($tpp_show_pages == "") {
      $tpp_show_pages = "on";
    }

    if ($tpp_show_attachments == "") {
      $tpp_show_attachments = "off";
    }

    if ( $tpp_number_of_posts < 1 || 20 < $tpp_number_of_posts )
      $tpp_number_of_posts = 5;

    echo $before_widget . $before_title . $tpp_widget_title . $after_title; ?>
    <ul> <?php

    if ( function_exists('stats_get_csv') ) {
      $top_posts = wp_cache_get('top_posts');
      if ( false == $top_posts ) {
        $top_posts = stats_get_csv('postviews', "days=7&limit=50" );
        wp_cache_set( 'stats_get_csv', $top_posts );
      }
      
      $pn = 1;
      foreach( $top_posts as $posts ) : 
	if ( $pn <= $tpp_number_of_posts ) {
        $postid = get_post($posts['post_id']);
        if ( $tpp_show_pages == "off" ) {
          if( $postid->post_type == "page" ) continue;
        }
  
        if ( $tpp_show_attachments == "off" ) {
          if( $postid->post_type == "attachment" ) continue;
        } ?>

        <li><a href="<?php echo get_permalink( $postid ); ?>"><?php echo $postid->post_title; ?></a></li>
	<?php ++$pn ?>
      <?php }
	 endforeach;
    } else { ?>
      <p> <?php _e('I\'m sorry, but you don\'t seem to have the WordPress.com stat plugin installed on this site.'); ?> </p><?php
    } ?>
    </ul>

    <?php echo $after_widget;
  }

  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['widget_title'] = strip_tags($new_instance['widget_title']);
    $instance['number_of_posts'] = strip_tags($new_instance['number_of_posts']);
    $instance['show_pages'] = strip_tags(empty($new_instance['show_pages']) ? 'on' : apply_filters('show_pages', $new_instance['show_pages']));
    $instance['show_attachments'] = strip_tags(empty($new_instance['show_attachments']) ? 'off' : apply_filters('show_attachments', $new_instance['show_attachments']));
    return $instance;
  }

  function form($instance) {
    $tpp_widget_title = strip_tags($instance['widget_title']);
    $tpp_number_of_posts = strip_tags($instance['number_of_posts']);
    $tpp_show_pages = $instance['show_pages'];
    $tpp_show_attachments = $instance['show_attachments'];

    if ($tpp_widget_title == "") {
      $tpp_widget_title = __('Top Posts','top-posts-n-pages-widget');
    }

    if ($tpp_show_pages == "") {
      $tpp_show_pages = "on";
    }

    if ($tpp_show_attachments == "") {
      $tpp_show_attachments = "off";
    }

    if ( $tpp_number_of_posts < 1 || 20 < $tpp_number_of_posts )
      $tpp_number_of_posts = 5;

    ?><p><label for="<?php echo $this->get_field_id('widget_title'); ?>"><?php _e('Widget Title', 'top-posts-n-pages-widget')?>:<input class="widefat" id="<?php echo $this->get_field_id('widget_title'); ?>" name="<?php echo $this->get_field_name('widget_title'); ?>" type="text" value="<?php echo $tpp_widget_title; ?>" /></label></p>

    <p><label for="<?php echo $this->get_field_id('number_of_posts'); ?>"><?php _e('Number of post to show:', 'top-posts-n-pages-widget'); ?> 
      <select id="<?php echo $this->get_field_id('number_of_posts'); ?>" name="<?php echo $this->get_field_name('number_of_posts'); ?>"><?php
        for ( $i = 1; $i <= 20; ++$i )
          echo "<option value='$i' " . ( $tpp_number_of_posts == $i ? "selected='selected'" : '' ) . ">$i</option>"; ?>
      </select>
    </label></p>

    <p><input class="checkbox" type="checkbox" <?php if ("$tpp_show_pages" == "on" ){echo 'checked="checked"';} ?> id="<?php echo $this->get_field_id('show_pages'); ?>" name="<?php echo $this->get_field_name('show_pages'); ?>" />
    <label for="<?php echo $this->get_field_id('show_pages'); ?>"><?php _e('Show pages?', 'top-posts-n-pages-widget')?></label></p>
    
    <p><input class="checkbox" type="checkbox" <?php if ("$tpp_show_attachments" == "on" ){echo 'checked="checked"';} ?> id="<?php echo $this->get_field_id('show_attachments'); ?>" name="<?php echo $this->get_field_name('show_attachments'); ?>" />
    <label for="<?php echo $this->get_field_id('show_attachments'); ?>"><?php _e('Show attachments?', 'top-posts-n-pages-widget')?></label></p>
    
    <p><?php _e( 'Top Posts &amp; Pages are calculated from WordPress.com stats plugin for that last 7 days. So, they take a while to change.', 'top-posts-n-pages-widget'); ?></p><?php

  }
} // Closing the top_posts_n_pages_widget Class

add_action('widgets_init', 'top_posts_n_pages_init');
function top_posts_n_pages_init() {
    register_widget('top_posts_n_pages_widget');
}

?>
