<?php
/*
Plugin Name: TaxiMap Integration
Plugin URI: http://blog.taximap.co.uk/2015/08/wordpress-plugin/
Description: Displays the TaxiMap taxi fare price calculator on your site via shortcode [taximap] or widget.
Version: 1.1.10
Author: M Williams
Author URI: http://nimbus.agency
*/


function taximap(){
	ob_start();
	return tm_renderiFrame();
}



function tm_scripts(){
	wp_enqueue_style( 'TaxiMapStyle', plugins_url( 'taximap.css', __FILE__ ) );
	wp_enqueue_script( 'TaxiMapScript', plugins_url( 'taximap.js', __FILE__ ),array(), '1.1.10', true );
}


function tm_renderiFrame(){
	$taximapId=esc_attr( get_option('taximap_id') );
	$tmIframe="";
	if($taximapId==''){
		$taximapId='10000';	//default to TaxiMap Demo Account
		$tmIframe.='<div class="tm_alert">Warning: TaxiMap ID not set - Admin must add a TaxiMap ID. </div>';
	}
	$tmIframe.='<div class="taximap"><!-- Version 1.1.9-201108 -->';
	$tmIframe.='<iframe src="//itaxi.co/plugin/taxi_map_frame.asp?wp=shortcodeWP&i1=1&f=1&uid='.$taximapId.'"></iframe>';
	//$tmIframe.='<!-- Powered by TaxiMap.co.uk -->';
	$tmIframe.='<span class="tm_ackno"><a href="https://taximap.co.uk">Taxi Price Calculator by TaxiMap</a></span>';
	$tmIframe.='<span class="tm_fullScreen"><a title="Full Screen" target="_blank" href="//itaxi.co/plugin/taximap/?f=0&uid='.$taximapId.'"><img src="'.plugins_url( 'fullscreen_icon.png', __FILE__ ).'" alt="Full Screen"><!-- Full screen--></a></span></div>';
	return $tmIframe;
}

add_action('wp_enqueue_scripts','tm_scripts');

add_shortcode('taximap', 'taximap');

// create custom plugin settings menu
add_action('admin_menu', 'taximap_integration_create_menu');

function taximap_integration_create_menu() {

	//create new top-level menu
	add_menu_page('TaxiMap Integration', 'TaxiMap Settings', 'administrator', __FILE__, 'taximap_integration_settings_page','dashicons-location' );

	//call register settings function
	add_action( 'admin_init', 'taximap_integration_settings' );
}


function taximap_integration_settings() {
	//register our settings
	register_setting( 'TaxiMap-integration-settings-group', 'taximap_id' );
}

function taximap_integration_settings_page() {
?>
<div class="wrap">
<h2>TaxiMap Integration</h2>
<p style="font-weight:bold;">You will need a TaxiMap account. Registration is free. <a href="#" id="taximapregistration" target="_blank">Click here to register now</a>.</p>
<p style="font-weight:bold;">You will need to get your TaxiMap Membership No. from <a href="https://taximap.co.uk/members/" target="_blank">http://taximap.co.uk</a> (found as the top item in the STATUS table on the right side after loggin in) and enter it below...</p>
<p>All other configuration is done from within your <a href="https://taximap.co.uk/members/" target="_blank">TaxiMap account</a>.</p>
	<form method="post" action="options.php"> 
		
		<?php settings_fields( 'TaxiMap-integration-settings-group' ); ?>
		<?php do_settings_sections( 'TaxiMap-integration-settings-group' ); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">TaxiMap User ID (Membership No):</th>
					<td><input type="text" name="taximap_id" value="<?php echo esc_attr(get_option('taximap_id')); ?>" /></td>
				</tr>
			</table>
		
	<?php submit_button(); ?>
	</form>
	
<h2 style="margin: 10px 0;padding: 3% 4%;text-align: center;font-size: 18px;background-color: #6fb5f5;color: #fff;line-height: 1.5;">To display the plugin on a page/post, just add the following short-code where you want it to appear:<br /><b style="font-size: 300%;color: #ffdf91;display:block;text-align:center;margin-top:2%;">[taximap]</b></h2>
<p>To add TaxiMap to your <b>sidebar</b>, please use the TaxiMap Widget found under <em>Appearance > <a href="widgets.php">Widgets</a></em>. For more info, see the TaxiMap plug-in <em><a href="<?echo plugins_url( 'readme.txt', __FILE__ )?>" target="_blank">read-me</a></em> file or the <em><a href="http://blog.taximap.co.uk/2015/08/wordpress-plugin/" target="_blank">TaxiMap Support Blog</a></em>.
<hr style="margin-top:35px;" />
<p style="color:red;font-weight:bold;font-size:110%;">Have you tried <a href="https://cabgrid.com" target="_blank" title="Find our more in a new window/tab">our other Wordpress taxi plug-in, Cab Grid</a>? It is a more simple, stand-alone system, based on a grid of prices rather than a complex calculation...</p>
<script>
	document.getElementById("taximapregistration").addEventListener("click", function(e){
		//console.log(Date.now()+" Click: "+this+"");
		//e.preventDefault();
		this.href="https://register.taximap.co.uk/go.asp?t="+Date.now()+"&d="+location.hostname;
		
	});
	document.getElementById("taximapregistration").addEventListener("mouseleave", function(e){
		//console.log(Date.now()+" Mouse Left");
		this.href="#";
	});
	
</script>
</div>
<?php }


//**** TAXIMAP WIDGET *****\\

class TaxiMapWidget extends WP_Widget
{
 // function TaxiMapWidget()
 // {
 //   $widget_ops = array('classname' => 'TaxiMapWidget', 'description' => 'Displays TaxiMap fare price calculator in your widebar (or widget area)' );
 //   $this->WP_Widget('TaxiMapWidget', 'TaxiMap', $widget_ops);
 // }
function __construct() {
	parent::__construct(
		'TaxiMapWidget', // Base ID
		__( 'TaxiMap', 'taximap' ), // Name
		array( 'description' => __( 'Displays TaxiMap fare price calculator in your sidebar.', 'taximap' ), ) // Args
	);
}
 
  public function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'beforeWidget'=>'','height' => '320', 'taximapId' => esc_attr(get_option('taximap_id')) ) );
    $title = $instance['title'];
	$beforeWidget = $instance['beforeWidget'];
	$taximapIdForWidget = $instance['taximapId'];
	$height = $instance['height'];
?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
  <p><label for="<?php echo $this->get_field_id('beforeWidget'); ?>">Text (message shown above TaxiMap): <textarea rows="4" class="widefat" id="<?php echo $this->get_field_id('beforeWidget'); ?>" name="<?php echo $this->get_field_name('beforeWidget'); ?>"><?php echo esc_attr($beforeWidget); ?></textarea></label></p>
  <p><label for="<?php echo $this->get_field_id('taximapId'); ?>">TaxiMap Membership No: <input class="widefat" id="<?php echo $this->get_field_id('taximapId'); ?>" name="<?php echo $this->get_field_name('taximapId'); ?>" type="text" value="<?php echo esc_attr($taximapIdForWidget); ?>" /></label></p>
  <p><label for="<?php echo $this->get_field_id('height'); ?>">Height (pixels): <input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo esc_attr($height); ?>" /></label></p>
<?php
  }
 
  public function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
	$instance['beforeWidget'] = $new_instance['beforeWidget'];
	$instance['taximapId'] = $new_instance['taximapId'];
	$instance['height'] = $new_instance['height'];
	
    return $instance;
  }
 
  public function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
 
    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
	$beforeWidget = empty($instance['beforeWidget']) ? ' ' : '<p class="tm_beforeWidget">'.$instance['beforeWidget'].'</p>';
	$height = $instance['height'];
	$taximapId = $instance['taximapId'];
 
    if (!empty($title))
      echo $before_title . $title . $after_title;
 
    // WIDGET CODE GOES HERE
    //echo "<h1>This is my new widget!</h1>";
	echo '<div class="tm_widget">'.$beforeWidget.'<iframe style="height:'.$height.'px;" src="//itaxi.co/plugin/taxi_map_frame.asp?wp=widget&i1=1&f=1&uid='.$taximapId.'"></iframe><a id="tm_ackno" href="https://taximap.co.uk">Taxi Price Calculator by TaxiMap</a></div>';
 
    echo $after_widget;
  }
 
}
//add_action( 'widgets_init', create_function('', 'return register_widget("TaxiMapWidget");') );
function register_taximap_widget() {register_widget( 'TaxiMapWidget' );}
 add_action( 'widgets_init', 'register_taximap_widget' );
?>