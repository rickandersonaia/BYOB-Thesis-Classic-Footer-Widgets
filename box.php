<?php

/*
Name: BYOB Simple Footer Widgets for the Thesis Classic Responsive skin
Author: Rick Anderson - BYOBWebsite.com
Requires: 2.1
Version: 2.4
Description: Places a row of widgets in the footer in the Thesis Classic Responsive Skin.  It <strong>REQUIRES</strong> Thesis 2.1.  This will generally work in any of the DIYThemes skins, although it may require some custom CSS and may not work in others.
Class: byob_simple_classic_footer_widgets
Docs: https://www.byobwebsite.com/addons/thesis-2/boxes/simple-footer-widgets/
License: MIT

*/

class byob_simple_classic_footer_widgets extends thesis_box {

	public $type = false;
	public $current_boxes = array();
	public $current_skin = array();
	public $hook_ids = array();

	protected function translate() {
		$this->title           = __( 'BYOB Footer Widgets for Thesis Classic Responsive', 'byobscfw' );
		$this->filters['text'] = __( 'Footer Widgets', 'byobscfw' );
	}


	/**
	 *  Box API method of adding a pseudo constructor
	 */
	protected function construct() {
		global $thesis;
		if(!defined('BYOBSCFW_PATH')){
			define( 'BYOBSCFW_PATH', __DIR__ );
		}

		//  Set up update system
		if ( is_admin() ) {
			global $byob_ah;
			if ( ! class_exists( 'byob_asset_handler' ) ) {
				include_once( BYOBSCFW_PATH . '/byob_asset_handler.php' );
			}
			if ( ! isset( $byob_ah ) ) {
				$byob_ah = new byob_asset_handler;
			}
		}

		$this->current_skin = unserialize($thesis->api->options['thesis_skin']);
		$this->current_boxes = get_option($this->current_skin['class'] . '_boxes', array());
		$this->hook_ids = $this->get_hook_ids();

		$this->register_footer_widget_areas();


		if ( ! is_admin() ) {
			$hook = $this->get_full_hook();
			$priority = $this->get_priority();
			add_action( $hook, array( $this, 'footer_layout' ), $priority );
		}

	}

	/**
	 *  Box API method for defining class options
	 */
	protected function class_options() {
		return array(
			'columns' => array(
				'label' => __( 'Choose the number of widget columns', 'byobscfw' ),
				'type'  => 'select',
				'options' => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4'
				)
			),
			'hook' => array(
				'label' => __( 'Choose the hook to add the widgets to', 'byobscfw' ),
				'type'    => 'select',
				'options' => $this->hook_ids
			),
			'location' => array(
				'label' => __( 'Choose the location of the hook to add the widgets to', 'byobscfw' ),
				'type'    => 'select',
				'options' => array(
					'' => __( 'Select a location', 'byobscfw' ),
					'hook_before_' => 'before',
					'hook_top_' => 'top',
					'hook_bottom_' => 'bottom',
					'hook_after_' => 'after'
				)
			),
			'priority' => array(
				'label' => __( 'Choose the priority <em>optional</em>', 'byobscfw' ),
				'type' => 'text',
				'width' => 'tiny',
				'placeholder' => '10'
			)
		);
	}

	/**
	 *  Box API method for defining template options
	 */
	protected function template_options() {
		return array(
			'title'  => __( 'Footer Widgets', 'byobscfw' ),
			'fields' => array(
				'remove_footer_widgets' => array(
					'type'    => 'checkbox',
					'options' => array(
						'yes' => __( 'Remove footer widgets from this template', 'byobscfw' )
					)
				)
			)
		);
	}

	/**
	 * @return array
	 *
	 * of box _ids (which form the basis of a hook name)
	 */
	protected function get_hook_ids(){
		$hook_ids = array(
			'' => __( 'Select a hook', 'byobscfw' )
		);
		foreach($this->current_boxes as $box_type){
			foreach($box_type as $box){
				if(isset($box['_id']) && !in_array($box['_id'], $hook_ids)){
					$hook_ids[$box['_id']] = $box['_id'];
				}

			}
		}
		return $hook_ids;
	}


	/**
	 * @return string
	 *
	 * of the full hook name - "hook_top_box_id"
	 */
	protected function get_full_hook(){
		$hook_guess = '';
		if(!isset($this->class_options['hook'])){
			$hook_guess = $this->get_hook_guess();
		}

		$fallback_hook = !empty($hook_guess) ? $hook_guess : "footer";

		$hook = isset($this->class_options['hook']) ? $this->class_options['hook'] : $fallback_hook;
		$location = isset($this->class_options['location']) ? $this->class_options['location'] : "hook_top_";

		return $location . $hook;
	}

	/**
	 * @return mixed|string
	 *
	 * looks for the first box _id that has the word "footer" in it and sets that as the default box _id
	 */
	protected function get_hook_guess(){
		$hook_guess = 'whoops';
		$pos = false;

		foreach( $this->hook_ids as $index => $id ){
			 $pos = strpos ( $id, 'footer' );
			 if( $pos !== false ) {
				return $id;
			};
		}
		return $hook_guess;
	}

	/**
	 * @return int
	 */
	protected function get_priority(){
		$priority = isset($this->class_options['priority']) ? (int)$this->class_options['priority'] : 10;
		return $priority;
	}

	/**
	 * registers widget areas
	 */
	protected function register_footer_widget_areas(){

		$sidebars     = $GLOBALS['wp_registered_sidebars'];
		$widget_areas = isset( $this->class_options['columns'] ) ? $this->class_options['columns'] : '4';
		$count        = 0;

		while ( $count < $widget_areas ) {
			$count ++;

			if ( ! in_array( 'Footer Widget ' . $count, $sidebars ) ) {
				register_sidebar( array(
					'name'          => 'Footer Widget ' . $count,
					'id'            => $this->_id . '_' . $count,
					'before_widget' => '<div class="widget %2$s" id="%1$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h4 class="widget_title">',
					'after_title'   => '</h4>'
				) );
			}
		}
	}


	/**
	 * @param bool $args
	 * creates the HTML layout for the widget area	 *
	 */
	public function footer_layout( $args = false ) {
		global $thesis;
		extract( $args = is_array( $args ) ? $args : array() );
		$tab          = str_repeat( "\t", $depth = ! empty( $depth ) ? $depth : 0 );
		$count        = 0;
		$widget_areas = isset( $this->class_options['columns'] ) ? $this->class_options['columns'] : '4';
		$widget_name  = 'Footer Widget Area ';

		switch ( $widget_areas ) {
			case 1:
				$class   = 'full';
				$columns = 'columns_1';
				break;
			case 2:
				$class   = 'half';
				$columns = 'columns_2';
				break;
			case 3:
				$class   = 'one-third';
				$columns = 'columns_3';
				break;
			default:
				$class   = 'one-quarter';
				$columns = 'columns_4';
		}

		if ( ! isset( $this->template_options['remove_footer_widgets'] ) ) {
			echo "$tab<div id=\"footer_widgets\" class=\"" . $columns . "\">\n";

			while ( $count < $widget_areas ) {
				$count ++;
				$position = false;
				if ( $count == 1 ) {
					$position = ' first';
				}
				if ( $count == $widget_areas ) {
					$position = ' last';
				}

				echo "$tab\t<div class=\"" . $class . " sidebar$position\">\n";
				if ( ! dynamic_sidebar( $this->_id . '_' . $count ) && is_user_logged_in() ) {
					echo "$tab\t\t<p>" . sprintf( __( 'This is a widget box called %1$s, but there are no widgets in it yet. <a href="%2$s">Add a widget here</a>.', 'byobscfw' ), $widget_name . $count, admin_url( 'widgets.php' ) ) . "</p>\n";
				}

				echo "$tab\t</div>\n";
			}
			echo "$tab\t<div style=\"clear:both;\"></div>\n$tab</div>\n";
		}
	}

	/**
	 * @param $css
	 *
	 * @return string
	 *
	 * Box method for adding CSS rules to Skin CSS
	 */
	public function filter_css( $css ) {
		global $thesis;
		$widget_areas      = isset( $this->class_options['columns'] ) ? $this->class_options['columns'] : '4';
		$classic_variables = unserialize( $thesis->api->options[$this->current_skin['class'] . '_vars'] );
		$x_single          = $x_half = false;
		foreach ( $classic_variables as $cr_var ) {
			if ( $cr_var['ref'] == 'x_single' ) {
				$x_single = $cr_var['css'];
			}
			if ( $cr_var['ref'] == 'x_half' ) {
				$x_half = $cr_var['css'];
			}
		}
		if ( $x_single == false ) {
			$x_single = '26px';
		}
		if ( $x_half == false ) {
			$x_half = '13px';
		}

		$css .= "\n/* BYOB Simple Classic Footer Widgets Style - Version 1.1 */\n" .
		        ".footer{padding-left:0; padding-right:0;}\n" .
		        ".footer .attribution{padding-right:$x_single}\n" .
		        "#footer_widgets{text-align:left;}\n" .
		        "#footer_widgets .widget{background-color:transparent;}\n" .
		        "#footer_widgets .sidebar {	padding: $x_single $x_single 0 $x_single; display:block;}\n";

		switch ( $widget_areas ) {
			case 1:
				$css .= ".full{width:100%; box-sizing: border-box; -moz-box-sizing: border-box;}\n";
				break;
			case 2:
				$css .= ".half{ width:50%;  -moz-box-sizing: border-box; box-sizing: border-box; }\n" .
				        ".columns_2 .half{float:left;}\n" .
				        "@media only screen and (max-width:800px),  screen and (max-device-width:800px)\n" .
				        "\t{\n" .
				        "\t\t.full, .half{float:none; width:100%; }\n" .
				        "\t\t#footer_widgets .sidebar{padding-left:0; padding-right:0;}\n" .
				        "\t}\n";
				break;
			case 3:
				$css .= ".one-third{ width:33.33%;  -moz-box-sizing: border-box; box-sizing: border-box; }\n" .
				        ".columns_3 .one-third{float:left;}\n" .
				        "@media only screen and (max-width:800px),  screen and (max-device-width:800px)\n" .
				        "\t{\n" .
				        "\t\t.one-third{ width:50%; min-width:240px;}\n" .
				        "\t}\n" .
				        "@media only screen and (max-width:450px),  screen and (max-device-width:450px)\n" .
				        "\t{\n" .
				        "\t\t#footer_widgets .sidebar {	padding: $x_single $x_half 0 $x_half;}\n" .
				        "\t\t.columns_3 .one-third{float:none; width:100%; }\n" .
				        "\t}\n";
				break;
			default:
				$css .= ".one-quarter{ width:25%;  -moz-box-sizing: border-box; box-sizing: border-box; }\n" .
				        ".columns_4 .one-quarter{float:left;}\n" .
				        "@media only screen and (max-width:800px),  screen and (max-device-width:800px)\n" .
				        "\t{\n" .
				        "\t\t.one-quarter{ width:50%; min-width:240px;}\n" .
				        "\t}\n" .
				        "@media only screen and (max-width:450px),  screen and (max-device-width:450px)\n" .
				        "\t{\n" .
				        "\t\t.columns_4 .one-quarter{float:none; width:100%; }\n" .
				        "\t\t#footer_widgets .sidebar {	padding: $x_single $x_half 0 $x_half;}\n" .
				        "\t}\n";
		}

		$css .= "/* End BYOB Simple Classic Footer Widgets Style */\n";

		return $css;
	}
}