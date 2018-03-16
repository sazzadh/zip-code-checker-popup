<?php
/**
 * Plugin Name: Zip Code Checker Popup
 * Description: This plugin will show a full screen popup and ask your website visitors to enter his zip code. After entering the zipcode the plugin will check your already listed zip codes with the given zip code. If it dones not match it will re-driect the user to a pre selected page. If the given zip code match with the prelisted zip code user can continue to use the page/site.
 * Author: Sazzad Hu
 * Author URI: http://sazzadh.com/
 * Plugin URI: http://sazzadh.com/
 * Version: 1.0
 * Requires at least: 4.0
 * Tested up to: 4.9.2
 *
 * You should have received a copy of the GNU General Public License
 * License: GPL version 2 or later - <http://www.gnu.org/licenses/>.
 
 zip_code_checker_popup
 zxxp_
 zip-code-checker-popup
 */
 
 class Zip_Code_Checker_popup{
	 
	 function __construct(){
		add_action( 'admin_menu', array($this, 'register_menu') );
		add_action( 'wp_head', array($this, 'wp_header_code') );
		add_action( 'wp_footer', array($this, 'wp_footer_code') );
		//add_action( 'init', array($this, 'check_zip_code') );
		
		add_action( 'wp_ajax_zxxp_zip_code_check', array($this, 'check_zip_code') );
		add_action( 'wp_ajax_nopriv_zxxp_zip_code_check', array($this, 'check_zip_code') );
	 }
	 
	 
	 
	 /**
	 * =====================================
	 * Register WordPress Menu
	 * =====================================
	 */ 
	 function register_menu(){
		 add_options_page( __('Zip Code Checker Popup Settings', 'zip-code-checker-popup'), __('Zip Code Checker Popup', 'zip-code-checker-popup'), 'manage_options', 'zip-code-checker-popup', array($this, 'settings_page_html'));
	 }
	 
	 
	 
	 /**
	 * =====================================
	 * HTML of the settings Pages
	 * =====================================
	 */
	 function settings_page_html(){
		
		 
		 echo '<div class="wrap">';
		 	echo '<h1>'.__('Zip Code Checker Popup Settings', 'zip-code-checker-popup').'</h1>';
			$this->save_settings_data();
			$options = get_option('zip_code_checker_popup');
		 	$zips = (isset($options['zips'])) ? $options['zips'] : NULL;
		 	$link = (isset($options['link'])) ? $options['link'] : NULL;
			$content = (isset($options['content'])) ? $options['content'] : NULL;
			
			echo '<form action="" method="post" name="">';
				wp_nonce_field( 'zip_code_checker_popup_action', 'zip_code_checker_popup_field' );
				echo '<table class="form-table">';
					echo '<tr>';
						echo '<th scope="row">';
							echo '<label for="zip_code_checker_popup[zips]">'.__('Zip Codes', 'zip-code-checker-popup').'</label>';
						echo '</th>';
						echo '<td>';
							echo '<textarea name="zip_code_checker_popup[zips]" type="text" id="zip_code_checker_popup_zips" class="large-text code" rows="3">'.esc_attr($zips).'</textarea>';
							echo '<p class="description">'.__('Enter as many Zip Codes you want. Use the following fromat', 'zip-code-checker-popup').'<code>24454, 56676, 6ew232</code></p>';
						echo '</td>';
					echo '</tr>';
					echo '<tr>';
						echo '<th scope="row">';
							echo '<label for="zip_code_checker_popup[link]">'.__('Redirect URL for unsuccessful validation.', 'zip-code-checker-popup').'</label>';
						echo '</th>';
						echo '<td>';
							echo '<input type="text" name="zip_code_checker_popup[link]" type="text" id="zip_code_checker_popup_link" value="'.esc_url($link).'" class="regular-text">';
							echo '<p class="description">'.__('Enter the URL of the Sorry page. Where your unsuccessful validation user will redirect', 'zip-code-checker-popup').'<code>http://sorry-page...</code></p>';
						echo '</td>';
					echo '</tr>';
					
					echo '<tr>';
						echo '<th scope="row">';
							echo '<label for="zip_code_checker_popup[content]">'.__('Form Description', 'zip-code-checker-popup').'</label>';
						echo '</th>';
						echo '<td>';
							echo '<textarea name="zip_code_checker_popup[content]" type="text" id="zip_code_checker_popup_content" class="large-text code" rows="3">'.wp_kses_post($content).'</textarea>';
							echo '<p class="description">'.__('This content will display above the validaion form.', 'zip-code-checker-popup').'<code>HTML are allowed.</code></p>';
						echo '</td>';
					echo '</tr>';
				echo '</table>';
				echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>';
			echo '</form>';
			
		 echo '</div>';
	 }
	 
	 
	 
	 /**
	 * =====================================
	 * Save the settings page data
	 * =====================================
	 */
	 function save_settings_data(){
		if(isset( $_POST['zip_code_checker_popup_field'] )){
			if( !isset( $_POST['zip_code_checker_popup_field'] ) || ! wp_verify_nonce( $_POST['zip_code_checker_popup_field'], 'zip_code_checker_popup_action' ) ) {
				print 'Sorry, your nonce did not verify.';
				exit;
			}else{
				$pre_data = $_POST['zip_code_checker_popup'];
				$zips = (isset($pre_data['zips'])) ? $pre_data['zips'] : NULL;
		 		$link = (isset($pre_data['link'])) ? $pre_data['link'] : NULL;
				$content = (isset($pre_data['content'])) ? $pre_data['content'] : NULL;
				$data = array('zips' => esc_attr($zips), 'link' => esc_url($link), 'content' => wp_kses_post($content));
				update_option('zip_code_checker_popup', $data);
				echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
	<p><strong>Settings saved.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
			}
		}
	 }
	 
	 
	 
	 /**
	 * =====================================
	 * This function will check the user zip code
	 * @return true or false
	 * =====================================
	 */
	 function check_zip_code(){
		 $message = '';
		 $valid = 'no';
		 $send_to_sorry_page = 'no';
		 
		// if(isset( $_POST['zip_code_checker_popup_user_field'] )){
			if( !isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'zip_code_checker_popup_user_action' ) ) {
				$message = __('Sorry, your nonce did not verify.', 'zip-code-checker-popup');
			}else{
				$user_zip = (isset($_POST['zip_code'])) ? $_POST['zip_code'] : NULL;
				if($user_zip != NULL){
					$options = get_option('zip_code_checker_popup');
					$zips = (isset($options['zips'])) ? $options['zips'] : NULL;
					$link = (isset($options['link'])) ? $options['link'] : NULL;
					 
					$zips = explode(",", preg_replace('/\s+/', '', $zips));
					 
					if(in_array($user_zip, $zips)){
						$this->set_cookie($user_zip);
						$valid = 'yes';
					}else{
						 $send_to_sorry_page = 'yes';
						 $this->set_cookie($user_zip, true);
					}
				}else{
					$message = __('Please enter a Zip code', 'zip-code-checker-popup');
				}
			 }
		// }
		 
		echo json_encode(array('valid' => $valid, 'message' => $message, 'send_to_sorry_page' => $send_to_sorry_page));
	
		wp_die();
	}
	 
	 
	 
	 /**
	 * =====================================
	 * Set cookie
	 * =====================================
	 */
	 function set_cookie($zip_code, $temp = false){
		$cookie_name = "zip_code_checker_popup";
		$cookie_value = $zip_code;
		
		if($temp == true){
			setcookie($cookie_name, $cookie_value, time() + 120, "/");
		}else{
			setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
		}
	 }
	 
	 /**
	 * =====================================
	 * Set  Sorry cookie
	 * =====================================
	 */
	 function set_sorry_cookie($zip_code){
		$cookie_name = "zip_code_checker_popup_sorry";
		$cookie_value = $zip_code;
		setcookie($cookie_name, $cookie_value, 0, "/"); // 86400 = 1 day
	 }
	 
	 
	 /**
	 * =====================================
	 * Check cookie
	 * =====================================
	 */
	 function check_cookie(){
		$cookie_name = "zip_code_checker_popup";
		
		if(!isset($_COOKIE[$cookie_name])) {
			return false;
		} else {
			return $_COOKIE[$cookie_name];
		}
	 }
	 
	 
	 
	 
	  /**
	 * =====================================
	 * HTML outpur of the user zipcode form
	 * =====================================
	 */
	 function user_form(){
		 echo '<form action="" method="post" name="">';
		 	echo '<div class="zip_code_checker_popup_message"></div>';
		 	wp_nonce_field( 'zip_code_checker_popup_user_action', 'zip_code_checker_popup_user_field' );
		 	echo '<input type="text" name="zip-code" id="get_the_zip_code" value="">';
		 	echo '<input type="submit" name="submit" value="Submit" id="zip_code_submit_button">';
		 echo '</form>';
		 
	 }
	 
	 
	 
	 
	 /**
	 * =====================================
	 * Add html and Javascript to WP_FOOTER 
	 * =====================================
	 */
	 function wp_footer_code(){
		 if($this->check_cookie() === false){
			$options = get_option('zip_code_checker_popup');
			$zips = (isset($options['zips'])) ? $options['zips'] : NULL;
			$link = (isset($options['link'])) ? $options['link'] : NULL;
			$content = (isset($options['content'])) ? $options['content'] : NULL;
					
			 echo '<div class="zip_code_checker_popup">';
				echo '<div class="zip_code_checker_popup_in">';
					echo '<div class="zip_code_checker_popup_description">'.wp_kses_post($content).'</div>';
					$this->user_form();
				echo '</div>';
			 echo '</div>';
			 
			 ?>
             <script type="text/javascript" >
				jQuery(document).ready(function($) {
					$( "#zip_code_submit_button" ).click(function(event) {
						event.preventDefault()
						var the_data = {
							'action': 'zxxp_zip_code_check',
							'zip_code': $('#get_the_zip_code').attr('value'),
							'nonce': $('#zip_code_checker_popup_user_field').attr('value'),
						};
						
						//alert($('#get_the_zip_code').attr('value'));
	
						$.ajax({
							url:"<?php echo admin_url( 'admin-ajax.php' ); ?>",
							type : "POST",
							data : the_data,
							dataType: "json",
							success:function(data){ 
								$('.zip_code_checker_popup_message').html( data.message);
								if(data.send_to_sorry_page === 'yes' ){
									window.location.replace('<?php echo esc_url($link); ?>');
								}else if(data.valid === 'yes' ){
									$('.zip_code_checker_popup').remove();
								}
							},
							error:function(request, textstatus, errorThrown){
								alert('Ajax request fail');
								console.log('zip data pass failed');
								console.log(request);
								console.log(textstatus);
								console.log(errorThrown);
							}
						});
						
					});
				});
			</script>
             <?php
		 }
	 }
	 
	 
	 
	 /**
	 * =====================================
	 * Add html and Javascript to WP_Header
	 * =====================================
	 */
	 function wp_header_code(){
		 ?>
         <style>
		 	.zip_code_checker_popup{
				position:fixed;
				height:100%;
				width:100%;
				z-index:99999;
				background-color:#D6C022;
				left: 0;
    			top: 0;
				
				display: flex;
				justify-content: center;
				align-items: center;
			}
		 </style>
         <?php
	 }
	 
 }
 
 new Zip_Code_Checker_popup;