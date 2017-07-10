<?php
/*
Name: BYOB Simple Footer Widgets for the Thesis Classic Responsive skin
Author: Rick Anderson - BYOBWebsite.com
Version: 2.1.3
Description: Places a box of widgets in the footer in the Thesis Classic Responsive Skin.  It <strong>REQUIRES</strong> Thesis 2.1.  This will not necessarily work in any other skin.
Class: byob_simple_classic_footer_widgets
*/

class byob_simple_classic_footer_widgets extends thesis_box {
    
        public $type = false;
    
	protected function translate() {
		$this->title = __('BYOB Footer Widgets for Thesis Classic Responsive', 'byobscfw');
                $this->filters['text'] = __('Footer Widgets', 'byobscfw');
	}
        
        
        
        protected function construct() {            
                global $thesis;
                if (is_admin()){
                        define('BYOBSCFW_PATH', THESIS_USER_BOXES . '/byob-classic-footer-widgets');
                        global $byob_ah;
                        if(!class_exists('byob_asset_handler'))
                            include_once( BYOBSCFW_PATH . '/byob_asset_handler.php');
                        if(!isset($byob_ah)) {
                                    $byob_ah = new byob_asset_handler;
                        }
                }
                
                $sidebars = $GLOBALS['wp_registered_sidebars'];
                $widget_areas = isset($this->class_options['columns']) ? $this->class_options['columns'] : '4';
                $count = 0;
                
                while ($count < $widget_areas){
			$count ++;
             
			if(!in_array('Footer Widget ' . $count, $sidebars)){
				register_sidebar(array(
					'name' => 'Footer Widget ' . $count,
					'id' => $this->_id . '_' . $count,
					'before_widget' => '<div class="widget %2$s" id="%1$s">',
					'after_widget' => '</div>',
					'before_title' => '<h4 class="widget_title">',
					'after_title' => '</h4>'));
			}
		} 
                
//                add_filter('thesis_css', array($this, 'columns_css'));
                
                if(!is_admin()){
                        add_action('thesis_hook_container_footer_top', array($this, 'footer_layout'));
                }              
                
	} 
        
//        public function save_admin(){
//                    global $thesis;
//                    
//                    $thesis->skin->_write_css();
//        }

        
        protected function class_options() {
                return array(                        
                        'columns' => array(
                                'type' => 'select',
                                'options' => array(
                                        '1' => '1',
                                        '2' => '2',
                                        '3' => '3',
                                        '4' => '4')));
        }

        
        protected function template_options() {                
                return array(
			'title' => __('Footer Widgets', 'byobscfw'),
			'fields' => array(
				'remove_footer_widgets' => array(
                                        'type' => 'checkbox',
                                        'options' => array(
                                                'yes' => __('Remove footer widgets from this template', 'byobscfw')))));
        }
        
        
	
	public function footer_layout($args = false) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", $depth = !empty($depth) ? $depth : 0);
		$count = 0;                
		$widget_areas = isset($this->class_options['columns']) ? $this->class_options['columns'] : '4';
		$widget_name = 'Footer Widget Area ';

		switch($widget_areas) {
			case 1:
				$class = 'full';
				$columns = 'columns_1';
				break;
			case 2:
				$class = 'half';                    
				$columns = 'columns_2';
				break;
			case 3:
				$class = 'one-third';
				$columns = 'columns_3';
				break;
			default:
				$class = 'one-quarter';
				$columns = 'columns_4';
		}
                
                if(!isset($this->template_options['remove_footer_widgets'])){
                        echo "$tab<div id=\"footer_widgets\" class=\"" . $columns . "\">\n";

                        while ($count < $widget_areas){
                                $count ++;
                                $position = false;
                                if ($count == 1)
                                        $position = ' first';
                                if ($count == $widget_areas)
                                        $position = ' last';

                                echo "$tab\t<div class=\"" . $class . " sidebar$position\">\n";
                                if (!dynamic_sidebar($this->_id . '_' . $count) && is_user_logged_in()){
                                        echo "$tab\t\t<p>" . sprintf(__('This is a widget box called %1$s, but there are no widgets in it yet. <a href="%2$s">Add a widget here</a>.', 'byobscfw'), $widget_name . $count, admin_url('widgets.php')) . "</p>\n";
                                }

                                echo "$tab\t</div>\n";
                        }		
                        echo "$tab\t<div style=\"clear:both;\"></div>\n$tab</div>\n"; 
                }
//                print_r(unserialize($thesis->api->options['thesis_classic_r_vars']));
	}
        
        public function filter_css($css){   
            global $thesis;
            $widget_areas = isset($this->class_options['columns']) ? $this->class_options['columns'] : '4';
            $classic_variables = unserialize($thesis->api->options['thesis_classic_r_vars']);
            $x_single = $x_half = $w_total = $w_content = false;
            foreach ($classic_variables as $cr_var){
                    if($cr_var['ref'] == 'x_single'){
                            $x_single = $cr_var['css'];
                    }
                    if($cr_var['ref'] == 'x_half'){
                            $x_half = $cr_var['css'];
                    }
                    if($cr_var['ref'] == 'w_total'){
                            $w_total = $cr_var['css'];
                    }
                    if($cr_var['ref'] == 'w_content'){
                            $w_content = $cr_var['css'];
                    }
            }
            if ($x_single == false){
                    $x_single = '26px';
            }
            if ($x_half == false){
                    $x_half = '13px';
            }
            if ($w_total == false){
                    $w_total = '1024px';
            }
            if ($w_content == false){
                    $w_content = '699px';
            }
            
            $css .= "\n/* BYOB Simple Classic Footer Widgets Style - Version 1.0 */\n".
                    ".footer{padding-left:0; padding-right:0;}\n".
                    ".footer .attribution{padding-right:$x_single}\n".
                    "#footer_widgets{text-align:left;}\n".
                    "#footer_widgets .sidebar {	padding: $x_single $x_single 0 $x_single;}\n";
            
            switch($widget_areas) {
			case 1:
                                $css .= ".full{width:100%; box-sizing: border-box; -moz-box-sizing: border-box;}\n";
				break;
			case 2:
				$css .= ".half{ width:50%;  -moz-box-sizing: border-box; box-sizing: border-box; }\n".
                                        ".columns_2 .half{float:left;}\n".
                                        "@media only screen and (max-width:$w_content),  screen and (max-device-width:$w_content)\n".
                                        "\t{\n".
                                        "\t\t.full, .half{float:none; width:100%; }\n".
                                        "\t\t#footer_widgets .sidebar{padding-left:0; padding-right:0;}\n".
                                        "\t}\n";
				break;
			case 3:
				$css .= ".one-third{ width:33.33%;  -moz-box-sizing: border-box; box-sizing: border-box; }\n".
                                        ".columns_3 .one-third{float:left;}\n".
                                        "@media only screen and (max-width:$w_total),  screen and (max-device-width:$w_total)\n".
                                        "\t{\n".
                                        "\t\t.one-third{ width:50%; min-width:240px;}\n".
                                        "\t}\n".
                                        "@media only screen and (max-width:450px),  screen and (max-device-width:450px)\n".
                                        "\t{\n".
                                        "\t\t#footer_widgets .sidebar {	padding: $x_single $x_half 0 $x_half;}\n".
                                        "\t\t.columns_3 .one-third{float:none; width:100%; }\n".
                                        "\t}\n";
				break;
			default:
				$css .= ".one-quarter{ width:25%;  -moz-box-sizing: border-box; box-sizing: border-box; }\n".
                                        ".columns_4 .one-quarter{float:left;}\n".
                                        "@media only screen and (max-width:$w_total),  screen and (max-device-width:$w_total)\n".
                                        "\t{\n".
                                        "\t\t.one-quarter{ width:50%; min-width:240px;}\n".
                                        "\t}\n".                                        
                                        "@media only screen and (max-width:450px),  screen and (max-device-width:450px)\n".
                                        "\t{\n".
                                        "\t\t.columns_4 .one-quarter{float:none; width:100%; }\n".
                                        "\t\t#footer_widgets .sidebar {	padding: $x_single $x_half 0 $x_half;}\n".
                                        "\t}\n";
		}
            
            $css .= "/* End BYOB Simple Classic Footer Widgets Style */\n";
            return $css;
        }
}