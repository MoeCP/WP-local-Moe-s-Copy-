<?php
	require 'Copypress_API.php';
	require_once 'Client_Info.php';

	/**
	* Plugin Name: CP API
	* Description: Get Articles from CopyPress API 
	* Version: 1.0 beta
	* Author: Moe Kahn
	*/

	register_activation_hook( __FILE__, 'daily_check' );

	function daily_check(){
		if (! wp_next_scheduled ( 'check_for_articles' )) {
		wp_schedule_event(time(), 'daily', 'check_for_articles');
    	}
	}
	
	add_action('check_for_articles', 'get_daily_articles');

	function get_daily_articles(){
		$cp_api = new CP_API();
		$cp_api->apikey = $client_api_key;
		$cp_api->apisig = $client_api_signature;
		$xml_object = $cp_api->call_api($article_id);

		parse_xml($xml_object);
	}


	add_action('admin_menu', 'copypress_menu_start');
	 
	function copypress_menu_start(){
	        add_options_page( 'CopyPress API Settings', 'CopyPress API Settings', 'manage_options', 'test-plugin', 'test_init' );
	}
	 
	function test_init(){
	        ?>
	        <h2> CopyPress API Plugin </h2>
	        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
	         API Key: <input type="text" name="apikey" id="apikey" value= "" size="40" />
	         <br>
	         API Signature: <input type="text" name="apisig" id="apisig" value= "" size="40" />
	         <br>
			 Article ID: <input type="text" name="article_id" id="article_id" value= "" size="40" />
			 <br>
			

			<input type="hidden" name="action" value="post_api">
			<input type="submit" value="Get Articles">
			</form>
	        <?
	        //echo "<h1>Hello World!</h1>";
	}

	add_action( 'admin_post_post_api', 'post_handler' );

	function post_handler(){
		$api_key = sanitize_text_field($_POST['apikey']);
		$api_sig = sanitize_text_field($_POST['apisig']);
		$article_id = sanitize_text_field($_POST['article_id']);
		
		$cp_api = new CP_API();
		$cp_api->apikey = $api_key;
		$cp_api->apisig = $api_sig;
		$xml_object = $cp_api->call_api($article_id);

		parse_xml($xml_object);

	}

	function parse_xml($xml){
		$article_xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		//die(print_r($article_xml));
		foreach ($article_xml as $articlestatus) {
			$author = $articlestatus->Author;
			$title = $articlestatus->title;
			$body = htmlspecialchars_decode($articlestatus->htmlBody);

			wp_insert($author, $title, $body);
		}
	}

	function wp_insert($author, $title, $body){
		if(null== get_page_by_title($title, OBJECT, 'post')){
			$post_array = array
			(
				'post_status'   => 'draft',
        		'post_author'   => $author,
        		'post_content'   => $body,
        		'post_title'    => $title
			);
			wp_insert_post($post_array);	
		}else{
			echo "Duplicate post, didn't post";
			?>

			<h2> There are duplicate posts here. The following are duplicate posts </h2>			

			<?
			//die("Duplicate post, didn't post");
		}
		wp_redirect(admin_url());
	}
