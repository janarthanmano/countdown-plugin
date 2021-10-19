<?php
/**
 * Plugin Name:     Countdown post
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     A custom plugin to create custom post type countdown and template pages
 * Author:          Jana
 * Author URI:      YOUR SITE HERE
 * Text Domain:     countdown-post
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Countdown_Post
 */

/*
 * Theme main class to activate and deactivate the plugin and to handle action hooks
 */
if ( !class_exists( 'Countdown' ) ) {
	class Countdown
	{
		/**
		* @var  string  $prefix  The prefix for storing custom fields in the postmeta table
		*/
		var $prefix = '_sbr_';

		/**
		* @var  array  $postTypes  An array of public custom post types, plus the standard "post" and "page" - add the custom types you want to include here
        */
        var $postTypes = array( "countdown" );

        /**
		* @var  array  $customFields  Defines the custom fields available
		*/
        var $customFields = array(
			array(
				"name"          => "expiry-date",
				"title"         => "Expiry Date",
				"description"   => "",
				"type"          => "datetime",
				"scope"         =>   array( "countdown" ),
			)
		);

		public function __construct()
		{
			register_activation_hook( __FILE__, array( 'Countdown', 'activation' ) );
			register_deactivation_hook( __FILE__, array( 'Countdown', 'deactivation' ) );
			add_action( 'init', array($this, 'activation') );
			add_action( 'admin_init', array( $this, 'countdown_settings_init' ) );
			add_action( 'admin_menu', array( $this, 'createCustomFields' ) );
			add_action( 'admin_menu', array( $this, 'countdown_options_page' ) );
            add_action( 'save_post', array( $this, 'saveCustomFields' ), 1, 2 );
			//Rearrange the columns on countdown post list view
			add_filter( 'manage_countdown_posts_columns', array($this, 'countdown_add_new_columns') );
			add_action( 'manage_countdown_posts_custom_column', array($this, 'countdown_expiry_column'), 10, 2 );
			add_filter( 'manage_edit-countdown_sortable_columns', array( $this, 'countdown_sortable_columns') );

			//overriding template for single and archive templates for countdown post.
			add_filter('template_include', array( $this, 'countdown_template') );

			add_action( 'admin_enqueue_scripts', array($this, 'admin_assets'), 10, 1);
			add_action( 'wp_enqueue_scripts', array($this, 'frontend_assets'), 10, 1);

            add_action('wp_ajax_countdown_create_dummy_posts', array($this, 'countdown_create_dummy_posts'));
            add_action( 'pre_get_posts', array($this, 'countdown_custom_query_vars' ) );

			add_action( 'wp_ajax_load_more_countdown', array($this, 'load_more_countdown') );
			add_action( 'wp_ajax_nopriv_load_more_countdown', array($this, 'load_more_countdown') );
		}

        /**
         * Plugin activation function
         */
		public static function activation() {
			/**
			 * countdown post type
			 */
			$countdown_labels = [
				'name' => _x('Countdown', 'plugin', 'SBR'),
				'singular_name' => _x('Countdown', 'plugin', 'SBR'),
				'all_items' => _x('All Countdowns', 'plugin', 'SBR'),
				'add_new' => _x('Add Countdown', 'plugin', 'SBR'),
				'add_new_item' => _x('Add New Countdown', 'plugin', 'SBR'),
				'edit_item' => _x('Edit Countdown', 'plugin', 'SBR'),
				'new_item' => _x('New Countdown', 'plugin', 'SBR'),
				'view_item' => _x('View Countdown', 'plugin', 'SBR'),
				'search_items' => _x('Search Countdown', 'plugin', 'SBR'),
				'not_found' => _x('No Countdown found', 'plugin', 'SBR'),
				'not_found_in_trash' => _x('No Countdown found in Trash', 'plugin', 'SBR'),
				'parent_item_colon' => _x('Parent Countdown:', 'plugin', 'SBR'),
				'menu_name' => _x('Countdowns', 'plugin', 'SBR'),
			];

			$countdown_args = [
				'labels' => $countdown_labels,
				'public' => true,
				'has_archive' => true,
				'rewrite' => ['slug' => 'countdown'],
				'menu_position' => 5,
				'menu_icon' => 'dashicons-hourglass',
				'map_meta_cap' => true,
				'supports' => ['title', 'editor', 'revisions'],
			];

			register_post_type('countdown', $countdown_args);

			// Clear the permalinks after the post type has been registered.
			flush_rewrite_rules();
		}

        /**
         * Plugin deactivation function
         */
        public static  function deactivation() {

            unregister_post_type( 'countdown' );
            //delete_option('countdown_options');
            // Clear the permalinks after the post type has been registered.
            flush_rewrite_rules();
        }

        /**
         * function to setup settings page for the plugin with sections and fields
         */
		public function countdown_settings_init(){
			// Register a new setting for "countdown settings" page.
			register_setting( 'countdown', 'countdown_options' );

			// Register a new section in the "countdown" page.
			add_settings_section(
				'countdown_section_developers',
				'', array($this, 'countdown_section_callback'),'countdown'
			);

			// Register a field in the "countdown_field_no_of_posts" section, inside the "countdown" page.
			add_settings_field(
				'countdown_field_no_of_posts',
				__( 'No of posts', 'sbr' ),
				array($this, 'countdown_field_no_of_posts_cb'),
				'countdown',
				'countdown_section_developers',
				array(
					'label_for'         => 'countdown_field_no_of_posts',
					'class'             => 'countdown_row',
					'countdown_custom_data' => 'custom',
				)
			);

			// Register a field in the "generate countdown post" section, inside the "countdown" page.
			add_settings_field(
				'countdown_field_generate_posts',
				__( 'Generate posts', 'sbr' ),
				array($this, 'countdown_field_generate_posts_cb'),
				'countdown',
				'countdown_section_developers',
				array(
					'label_for'         => 'countdown_field_generate_posts',
					'class'             => 'countdown_row',
					'countdown_custom_data' => 'custom',
				)
			);
		}

		/**
		 * Developers section callback function.
		 *
		 * @param array $args  The settings array, defining title, id, callback.
		 */
		public function countdown_section_callback( $args ) {
			?>
			<b id="<?php echo esc_attr( $args['id'] ); ?>">Click <a href="<?= get_option('siteurl') ?>/countdown">here</a> to see countdown archive page</b>
			<?php
		}

		/**
		 * No of posts field callbakc function.
		 *
		 * @param array $args
		 */
		public function countdown_field_no_of_posts_cb( $args ) {
			// Get the value of the setting we've registered with register_setting()
			$options = get_option( 'countdown_options' );
			?>
			<input type="number"
				id="<?php echo esc_attr( $args['label_for'] ); ?>"
				data-custom="<?php echo esc_attr( $args['countdown_custom_data'] ); ?>"
				name="countdown_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
				value="<?= isset( $options[$args['label_for']] ) ? $options[$args['label_for']] : ''; ?>"
			>
			<p class="description">
				<?php esc_html_e( 'Select the range for number of posts loaded. This is also set the plugins config to generate automatic posts.', 'sbr' ); ?>
			</p>
			<?php
		}

		/**
		 * Generate posts field callbakc function.
		 *
		 * @param array $args
		 */
		public function countdown_field_generate_posts_cb( $args ) {
			// Get the value of the setting we've registered with register_setting()
			$options = get_option( 'countdown_options' );
			?>
			<button type="button" id="generate-countdown">Generate</button>
            <div class="alert alert-success w-25 d-none" id="countdown_field_generate_posts" role="alert"></div>

			<p class="description">
				<?php esc_html_e( 'Click this button to generate dummy posts for testing, countdown date and time will be in random future date. Number of posts generted depends on above settings.	', 'sbr' ); ?>
			</p>
			<?php
		}

		/**
		 * Add the top level menu page.
		 */
		public function countdown_options_page() {
			add_menu_page(
				'Countdown settings',
				'Countdown Options',
				'manage_options',
				'countdown_settings',
				array($this, 'countdown_options_page_html')
			);
		}

		/**
		 * Top level menu callback function
		 */
		public function countdown_options_page_html() {
			// check user capabilities
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// add error/update messages


			if ( isset( $_GET['settings-updated'] ) ) {
				// add settings saved message with the class of "updated"
				add_settings_error( 'countdown_messages', 'countdown_message', __( 'Settings Saved', 'sbr' ), 'updated' );
			}

			// show error/update messages
			settings_errors( 'countdown_messages' );
			?>
			<div class="wrap">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<form action="options.php" method="post">
					<?php
					settings_fields( 'countdown' );

					do_settings_sections( 'countdown' );
					// output save settings button
					submit_button( 'Save Settings' );
					?>
				</form>
			</div>
			<?php
		}

		/**
		 * Add new columns to the post table
		 *
		 * @param Array $columns - Current columns on the list post
		 */
		public function countdown_add_new_columns( $columns ) {
			$rearranged_column = array();
			//unset($columns['title']);
			$columns = array_merge($columns, array('expiry' => __('Expiry date')));
			$rearranged_column['cb'] = $columns['cb'];
            $rearranged_column['title'] = $columns['title'];
			$rearranged_column['expiry'] = $columns['expiry'];
			$rearranged_column['date'] = $columns['date'];

			return $rearranged_column;
		}

        /**
         * Make the countdown post list view sortable with the meta field Expiry date
         */
		public function countdown_sortable_columns($columns){
			$columns['expiry'] = 'Expiry date';
			return $columns;
		}

        /**
         * Add a custom column to the countdown list view.
         */
		public function countdown_expiry_column($column, $post_id) {
			switch ( $column ) {
				case 'expiry':
					echo '<a class="row-title" href="'. get_option('siteurl') .'/wp-admin/post.php?post='.$post_id.'&action=edit" aria-label="'.get_post_meta( $post_id , '_sbr_expiry-date' , true ).' (Edit)">'.get_post_meta( $post_id , '_sbr_expiry-date' , true ).'</a>';
					break;
			}
		}

        /**
         * Load custom field for the countdown post
         */
		public function createCustomFields()
		{
			if ( function_exists( 'add_meta_box' ) ) {
				foreach ( $this->postTypes as $postType ) {
					add_meta_box( 'custom-fields', 'Countdown Custom Fields', array( $this, 'displayCustomFields' ), $postType, 'normal', 'high' );
                }
			}

		}

		/**
		 * Display the new Custom Fields meta box
		 */
		public function displayCustomFields() {
			global $post;
			?>
			<div class="form-wrap">
				<?php
				wp_nonce_field( 'custom-fields', 'custom-fields_wpnonce', false, true );
				foreach ( $this->customFields as $customField ) {
					// Check scope
					$scope = $customField[ 'scope' ];
					$output = false;
					foreach ( $scope as $scopeItem ) {
						switch ( $scopeItem ) {
							default: {
								if ( $post->post_type == $scopeItem )
									$output = true;
								break;
							}
						}
						if ( $output ) break;
					}

					// Output if allowed
					if ( $output ) { ?>
						<div class="form-field form-required">
							<?php
							switch ( $customField[ 'type' ] ) {
								case "datetime": {
									// Checkbox
									echo '<label for="' . $this->prefix . $customField[ 'name' ] .'" style="display:inline;"><b>' . $customField[ 'title' ] . '</b></label> ';
									echo '<input type="datetime-local" name="' . $this->prefix . $customField['name'] . '" id="' . $this->prefix . $customField['name'] . '" value="'.get_post_meta( $post->ID, $this->prefix . $customField['name'], true ).'"';
									echo '" style="width: auto;" />';
									break;
								}
								default: {
									// Plain text field
									echo '<label for="' . $this->prefix . $customField[ 'name' ] .'"><b>' . $customField[ 'title' ] . '</b></label>';
									echo '<input type="text" name="' . $this->prefix . $customField[ 'name' ] . '" id="' . $this->prefix . $customField[ 'name' ] . '" value="' . htmlspecialchars( get_post_meta( $post->ID, $this->prefix . $customField[ 'name' ], true ) ) . '" />';
									break;
								}
							}
							?>
							<?php if ( $customField[ 'description' ] ) echo '<p>' . $customField[ 'description' ] . '</p>'; ?>
						</div>
						<?php
					}
				} ?>
			</div>
			<?php
		}

		/**
		 * Save the new Custom Fields values
		 */
		public function saveCustomFields( $post_id, $post ) {
			if ( !isset( $_POST[ 'custom-fields_wpnonce' ] ) || !wp_verify_nonce( $_POST[ 'custom-fields_wpnonce' ], 'custom-fields' ) )
				return;
			if ( ! in_array( $post->post_type, $this->postTypes ) )
				return;
			foreach ( $this->customFields as $customField ) {
				if ( isset( $_POST[ $this->prefix . $customField['name'] ] ) && trim( $_POST[ $this->prefix . $customField['name'] ] ) ) {
					$value = $_POST[ $this->prefix . $customField['name'] ];
					// Auto-paragraphs for any WYSIWYG
					if ( $customField['type'] == "wysiwyg" ) $value = wpautop( $value );
					update_post_meta( $post_id, $this->prefix . $customField[ 'name' ], $value );
				} else {
					delete_post_meta( $post_id, $this->prefix . $customField[ 'name' ] );
				}
			}
		}

        /**
         * function to generate custom templates for single an archive pages of countdown custom  post.
         */
		public function countdown_template( $template ){
			if( is_singular('countdown') ){
				$theme_files = array('single-countdown.php', 'countdown-post/single-countdown.php');
				$exists_in_theme = locate_template($theme_files, false);
				if ( $exists_in_theme != '' ) {
					return $exists_in_theme;
				} else {
					return plugin_dir_path(__FILE__) . '/template/single-countdown.php';
				}
			}
			if ( is_post_type_archive('countdown') ) {
				$theme_files = array('archive-countdown.php', 'countdown-post/archive-countdown.php');
				$exists_in_theme = locate_template($theme_files, false);
				if ( $exists_in_theme != '' ) {
					return $exists_in_theme;
				} else {
					return plugin_dir_path(__FILE__) . '/template/archive-countdown.php';
				}
			}
			return $template;
		}

        /**
         * Custom query for archive page to list the number of posts based on the settings.
         */
        public function countdown_custom_query_vars( $query ) {#
            if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'countdown' ) ) {
                $options = get_option( 'countdown_options' );
                $query->set( 'posts_per_page', isset($options['countdown_field_no_of_posts']) ? $options['countdown_field_no_of_posts'] : 5 );
            }
            return $query;
        }

        /**
         * A function to generate dummy posts for testing countdown custom post.
         */
		public function countdown_create_dummy_posts(){
		    $no_of_posts = (isset($_POST['no_of_posts']) && $_POST['no_of_posts'] != '') ? $_POST['no_of_posts'] : 5;

		    for($i=1; $i <= $no_of_posts; $i++){
                $date = new DateTime("+".rand(-5, 15)." day");
                $expiry_date = date('Y-m-d\TH:i', $date->getTimestamp());
                $title = wp_trim_words( file_get_contents('http://loripsum.net/api/1/short/plaintext') , 10 );
                // Create post object
                $countdown = array(
                    'post_type' => 'countdown',
                    'post_title'    => $title,
                    'post_content'  => file_get_contents('http://loripsum.net/api/10/short/headers'),
                    'meta_input' => array(
                        '_sbr_expiry-date' => $expiry_date
                    ),
                    'post_status'   => 'publish',
                    'post_author'   => 1,
                );
                // Insert the post into the database
                wp_insert_post( $countdown );
            }
		    $data = array('html' => $no_of_posts.' countdown posts created successfully!', 'status' => 'success');

            wp_reset_postdata();
            wp_send_json_success( $data );
            wp_die();
        }

        /**
         * Load admin assets
         */
		public function admin_assets($hook)
		{
			wp_enqueue_script( 'countdown_admin_script', plugin_dir_url( __FILE__ ) . '/admin/js/build/app.js', array(), '1.0' );
            wp_localize_script( 'countdown_admin_script', 'countdown_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

			wp_register_style( 'countdown_admin_css', plugin_dir_url( __FILE__ ) . '/admin/css/build/app.css', false, '1.0.0' );
			wp_enqueue_style( 'countdown_admin_css' );
		}

        /**
         * Load assets for front end.
         */
		public function frontend_assets()
		{
			if( is_singular('countdown') || is_post_type_archive('countdown') ) {
				wp_enqueue_script('countdown_frontend_script', plugin_dir_url(__FILE__) . '/public/js/build/app.js', array(), '1.0');
                wp_localize_script( 'countdown_frontend_script', 'countdown_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

				wp_register_style('countdown_frontend_css', plugin_dir_url(__FILE__) . '/public/css/build/app.css', false, '1.0.0');
				wp_enqueue_style('countdown_frontend_css');
			}
		}

        /**
         * function to load countdown posts with infinite loading.
         */
		public function load_more_countdown()
        {
            $args = array();
            $args['post_type'] = 'countdown';
            $args['paged'] = esc_attr($_POST['page']);

            $options = get_option('countdown_options');

            $args['posts_per_page'] = isset($options['countdown_field_no_of_posts']) ? $options['countdown_field_no_of_posts'] : 5;
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            $args['post_status'] = 'publish';

            $query = new WP_Query($args);
            $countdowns = $query->posts;

            $html = '';

            if (is_array($countdowns) && count($countdowns)){
                foreach ($countdowns as $key => $countdown) {
                    $html .= '<article id="post-'.$countdown->ID.'">';

                    $html .= '<h2 class="entry-title default-max-width"><a href="'.get_permalink($countdown->ID).'">'.$countdown->post_title.'</a></h2>';

                    $html .= '<div class="entry-content">';
                        $html .= apply_filters('the_excerpt', get_the_excerpt($countdown));
                    $html .= '</div>';
                 $html .= '</article>';
                }
            }else{
                wp_die();
            }

            $args['paged'] = (int)$args['paged']+1;
            $nextPage = new WP_Query( $args );
            $data = array('html' => $html, 'nextPage' => $nextPage->have_posts());
            wp_reset_postdata();
            wp_send_json_success( $data );
            wp_die();

		}
	}


	$countdown = new Countdown();
}
