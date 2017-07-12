<?php

// Version 1.0.6 of the asset handler - 2/17/2015

class byob_asset_handler {

	const CALLOUT_TRANSIENT = 'byob_callout';

	public function __construct() {
		if (is_dir(WP_CONTENT_DIR . '/thesis'))
			add_action('thesis_updates', array($this, 'get_all_updates'), 1);
		add_action('upgrader_process_complete', array($this, 'reset_transients'));
	}

	public function get_all_updates() {
		global $thesis;

		//delete_transient(self::CALLOUT_TRANSIENT); //uncommenting this line will force an update check, for testing purposes only
		if (get_transient(self::CALLOUT_TRANSIENT))
			return;

		set_transient(self::CALLOUT_TRANSIENT, time(), 60 * 60 * 24);

		$skin_objects = new thesis_skins();
		$box_objects = new thesis_user_boxes();
		$package_objects = new thesis_user_packages();

		$objects = array(
			'skins' => $skin_objects->get_items(),
			'boxes' => $box_objects->get_items(),
			'packages' => $package_objects->get_items()
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
				set_transient("thesis_{$type}_update", $returned[$type], 60 * 60 * 24);
	}

	public function reset_transients() {
		foreach (array('skins', 'boxes', 'packages') as $tr)
			delete_transient("thesis_{$tr}_update");
		delete_transient(self::CALLOUT_TRANSIENT);
		wp_cache_flush();
	}

}

?>