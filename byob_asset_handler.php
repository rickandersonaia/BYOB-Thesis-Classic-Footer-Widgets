<?php
// Version 1.0.3 of the asset handler - 7/17/2013

class byob_asset_handler{
    
        public function __construct() {
		if (is_dir(WP_CONTENT_DIR . '/thesis'))
			add_action('admin_init', array($this, 'get_all_updates'), 100);
	}
	
	public function get_all_updates() {
		global $thesis;
		
		delete_transient('byob_callout');
		if (get_transient('byob_callout'))
			return;
		
		set_transient('byob_callout', time(), 60*60*24);
		
		$objects = array(
			'skins' => thesis_skins::get_items(),
			'boxes' => thesis_user_boxes::get_items(),
			'packages' => thesis_user_packages::get_items()
		);
		
		$transients = array(
			'skins' => 'thesis_skins_update',
			'boxes' => 'thesis_boxes_update',
			'packages' => 'thesis_packages_update'
		);
		
		$all = array();
		
		foreach ($objects as $object => $array)
			if (is_array($array) && !empty($array))
				foreach ($array as $class => $data)
					$all[$object][$class] = $data['version'];
		
		
		foreach ($transients as $key => $transient)
			if (get_transient($transient))
				unset($all[$key]);
		
		if (empty($all))
			return;
                
                $all['thesis'] = $thesis->version;		
		
		$from = 'http://byobwebsite.com/extended-files/files.php';
		$post_args = array(
			'body' => array(
				'data' => serialize($all),
				'wp' => $GLOBALS['wp_version'],
				'php' => phpversion(),
				'user-agent' => "WordPress/{$GLOBALS['wp_version']};" . home_url()
			)
		);
		
		$post = wp_remote_post($from, $post_args);

		if (is_wp_error($post) || empty($post['body']))
			return;
		
		$returned = @unserialize($post['body']);

		if (!is_array($returned))
			return;

		foreach ($returned as $type => $data) // will only return the data that we need to update
			if (in_array("thesis_{$type}_update", $transients))
				set_transient("thesis_{$type}_update", $returned[$type], 60*60*24);
	}
}
?>