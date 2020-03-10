<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://solusipress.com/
 * @since      1.0.0
 *
 * @package    Solusipress_Bisnis
 * @subpackage Solusipress_Bisnis/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Solusipress_Bisnis
 * @subpackage Solusipress_Bisnis/public
 * @author     Yerie Piscesa <yerie@solusipress.com>
 */
class Solusipress_Bisnis_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    protected $form_has_result = false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Solusipress_Bisnis_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Solusipress_Bisnis_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_register_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/solusipress-bisnis-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Solusipress_Bisnis_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Solusipress_Bisnis_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/solusipress-bisnis-public.js', array( 'jquery' ), $this->version, false );
        wp_register_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js?hl=id' );

	}
	
	public function redirect_after_submit_contact() {
		if ( !( false === ( $value = get_transient( 'spb_after_submit_contact' ) ) ) ) {
			if( !empty( $_POST ) && isset( $_POST['g-recaptcha-response'] ) ) {
				global $wp;
				wp_safe_redirect( home_url( $wp->request ) );
				exit;
			}
		}
	}
	
	protected $form_has_message = false;
	
	private function contact_form_errors( $page, $errors ) {
        ob_start();
        echo '<div class="grid-100 spb-errors-list">';
        echo "<h3>Terjadi kesalahan, mohon periksa isian Anda </h3><ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo '</ul>';
        echo '</div>';
        $page->page_errors = ob_get_contents();
        ob_end_clean();
	}
	
	public function public_bank_account( $atts ) {
		$html = '';
		$model = \SolusiPress\Model\Loader::get( 'Accounts' );
		$accounts = $model->find()->where(['public_account'=>'1'])->all();
		
		if( !empty( $accounts ) ) {
			wp_enqueue_style( $this->plugin_name );
			foreach( $accounts as $acct ) {
				$html .= '<div class="grid-100 public-account-list">';
				$html .= '<div class="grid-40 mobile-grid-100 logo">';
				if( $acct->logo_url != '' ) {
					$html .= '<img src="' . $acct->logo_url . '">';
				} else {
					$html .= '[Logo ' . $acct->bank . ']';
				}
				$html .= '</div>';
				$html .= '<div class="grid-60 mobile-grid-100 description">';
				$html .= '<strong>'.$acct->bank . '</strong><br>';
				$html .= 'No Rekening ' . $acct->account_number . '<br>';
				$html .= 'Atas nama ' . $acct->account_name;
				$html .= '</div>';
				$html .= '</div>';
			}
			$html .= '<div class="clearfix"></div>';
		}

		
		return $html;
	}
	
	public function contact_form( $atts ) {
		
		wp_enqueue_style( $this->plugin_name );
		wp_enqueue_script( $this->plugin_name );
		if ( !( false === ( $value = get_transient( 'spb_after_submit_contact' ) ) ) ) {
	        $return  = '<h3>Terima kasih telah mengisi form kontak.</h3>';
	        $return .= '<p>Form akan terbuka kembali dalam beberapa menit kedepan. Mohon kesediaannya menunggu.</p>';
	        
	        if( get_option( 'spb_contact_whatsapp_notif', '' ) != '' ) {
		        $return .= "Tidak ingin menunggu? Hubungi kami via ";
		        $return .= '<a href="https://wa.me/' . get_option( 'spb_contact_whatsapp_notif' ) . '" target="_blank">WhatsApp</a>';
	        }
		} else {
			
	        $atts = shortcode_atts( array(
		        'subject' => '',
		        'text_message'=> 'on'
		    ), $atts, 'solusipress_bisnis_contact_form' );
		    
			if( get_option( 'spb_activate_recaptcha', '0' ) == '1' && 
			    get_option( 'spb_recaptcha_site_key', '' ) != '' ) {
	        	wp_enqueue_script( 'google-recaptcha' );
	        }
			
	        $form = new Gregwar\Formidable\Form( plugin_dir_path( __FILE__ ) . 'partials/contact-form.php', [ 'atts' => $atts ] );
	        $page = $this;
	        $form->handle(function() use ( $form, $page, $atts ) {
				
				$go_process = false;
		        $secret = get_option( 'spb_recaptcha_secret_key', '' );
	            if( get_option( 'spb_activate_recaptcha', '0' ) == '1' && $secret != '' ) {		            
					$recaptcha = new \ReCaptcha\ReCaptcha($secret);
	                $resp = $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
	                                  ->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
					if( $resp->isSuccess() ) {
						$go_process = true;
					} else {
						$errors = [ 'Recaptcha tidak valid' ];
						$page->contact_form_errors( $page, $errors );
					}			  	
				} else {
					$go_process = true;
				}
				
				if( $go_process ) {
					$page->form_has_result = true;					
					$data = [
						'first_name' => $form->first_name,
						'last_name' => $form->last_name,
						'email' => $form->email,
						'whatsapp' => $form->whatsapp,
					];
					
					if( $atts['text_message'] == 'on' ) {
						$data['subject'] = $form->msg_subject;						
						$data['text'] = $form->msg_text;	
						if( trim( $form->msg_text ) != '' ) {
							$page->form_has_message = true;
						}
					}
					
					\SolusiPress\Model\Transact::contactMessageInput( $data );
					set_transient( 'spb_after_submit_contact', session_id(), 5 * MINUTE_IN_SECONDS );
				}
				
	        }, function($errors) use( $page ) {
	            
	            $page->contact_form_errors( $page, $errors );
	            
	        } );
	        
	        $sticky_subject = trim( $atts['subject'] );
			if( $atts['text_message'] == 'on' && $sticky_subject != '' ) {
	   			$form->msg_subject = $sticky_subject;
	   		}
	   		     
	        if( $page->form_has_result ) {
	            $return = '<h3 class="spb-contact-success">Terima kasih telah mengisi form kontak.</h3>';
	            if( $page->form_has_message ) {
		            $return .= '<p>Kami akan segera menghubungi Anda kembali melalui email/whatsapp berikut : ';
		            $return .= '<table class="spb-contact-table">
		            	<tr><td class="label">Email</td><td>' . $form->email . '</td></tr>
		            	<tr><td class="label">No WhatsApp</td><td>' . $form->whatsapp . '</td></tr>
		            </table>';
		            $return .= '</p>';
	            }
	        } else {
	            $return = $page->page_errors . $form;   
	        }
	        $page->form_has_result = false;
	    
	    }
        
		return $return;
	}

}
