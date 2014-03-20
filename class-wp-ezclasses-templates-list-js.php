<?php
/** 
 * Extend your own plugin on top of this template to add List.js (Listjs.com) to your WordPress theme or plugin. Also includes additional js for more robust filtering capabilities.
 *
 * More info on List.js: (@link http://Listjs.com)
 *
 * PHP version 5.3
 *
 * LICENSE: TODO
 *
 * @package WP ezClasses
 * @author Mark Simchock <mark.simchock@alchemyunited.com>
 * @since 0.5.0
 */
/*
 * CHANGE LOG
 *
 */

if ( ! class_exists('Class_WP_ezClasses_Templates_List_js')){
	class Class_WP_ezClasses_Templates_List_js extends Class_WP_ezClasses_Master_Singleton {

		protected $_file;	
		protected $_url;
		protected $_path;
		protected $_str_wp_localize;
		
		protected $_obj_wp_enqueue;
		protected $_obj_conditional_tags;
		
		public function __construct(){}
		
		public function ezc_init(){
			
			$this->_file = __FILE__ ;
			$this->_url = plugin_dir_url(__FILE__);
			$this->_path = plugin_dir_path(__FILE__);
			$this->_str_wp_localize = 'listjs_ezfilters';
		
			$this->_obj_wp_enqueue = Class_WP_ezClasses_ezCore_WP_Enqueue::ezc_get_instance();
			$this->_obj_conditional_tags = Class_WP_ezClasses_ezCore_Conditional_Tags::ezc_get_instance();
			
			add_action( 'wp_enqueue_scripts', array($this,'wp_enqueue_list_js') );
			
			// while admin usage is probably less likely, we'll add it and use the wp_enqueue_conditional_tags() method to do the deciding
			add_action( 'admin_enqueue_scripts', array($this,'wp_enqueue_list_js') );
		}
		
		/*
		 * enqueues the js
		 */
		public function wp_enqueue_list_js(){
		
			// Only load the js when? 
			$arr_load_when = $this->wp_enqueue_conditional_tags();
								
			$arr_cond_tag = $this->_obj_conditional_tags->conditional_tags_evaluate($arr_load_when);
				
			if ( isset($arr_cond_tag) && isset($arr_cond_tag['status']) && $arr_cond_tag['status'] === true ){
			
				$this->_obj_wp_enqueue->wp_enqueue_do($this->list_js_enqueue());
			}
		}
		
		/*
		 * Class_WP_ezClasses_Core_WP_Enqueue takes an array of this format / args and "automates" the enqueue'ing. 
		 *
		 * Do not change (unless you feel you must)
		 */ 
		protected function list_js_enqueue(){
		
			$arr_scripts_and_styles = array(
										'list_js_min'			=> array(	
																		'active'			=> true,
																		'conditional_tags'	=> array(),
																		'type'				=> 'script',
																		'note'				=> "http://listjs.com/ - pure js, no jQuery necessary.",
																		'handle'			=> 'listjs_min',
																		'src'				=> $this->_url . 'js/list.min.js',
																		'deps'				=> false,
																		'ver'				=> '1.1.0',
																		//	'media'			=> NULL,	
																		'in_footer'			=> true,
																		),
		
										'list_js_ezfilters'		=> array(	
																		'active'			=> true,
																		'conditional_tags'	=> array(),
																		'type'				=> 'script',
																		'note'				=> "pure js (no jQuery necessary), custom js to enhance filter capabilities of http://listjs.com/",
																		'handle'			=> $this->_str_wp_localize,
																		'src'				=> $this->_url . 'js/list.js.ezfilters.min.js',
																		'deps'				=> false,
																		'ver'				=> '0.5.1',
																		//	'media'			=> NULL,	
																		'in_footer'			=> true,
																		),
											);							
			return $arr_scripts_and_styles;
		}
		
		/**
		 * Define when the js should / should not be enqueue'd based on WP ezClasses conditional_tags
		 *
		 * In short Class_WP_ezClasses_Core_Conditional_Tags takes a tag and value and evaluates them. 
		 * for example, 
		 *  -- Instead of updating a conditional if ( is_admin() && is_x() && ! is_y() && ...)  
		 *  -- You would have $x = array( 'is_admin' => true, 'is_x' => true, 'is_y' => false, ...) and  then (pseudo code) if ( $obj_init->conditional_tags_evaluate($x) ) {}
		 *
		 * Which means you can update logic / rules by updating an array (instead of actual code). 
		 *
		 * IMPORTANT: You should probably over-ride this method with your own in your child class / plugin
		 */		
		public function wp_enqueue_conditional_tags(){
		
			$arr_return = array(	
								'tags'	=> array(
											'is_admin' => false,
											)
								);
			
			return 	$arr_return;
		
		}
		
		/*
		 * 1) Takes the args defined in list_js_presets() and spits out the associated markup for the search box, sort, filters and search buttons. 
		 * 2) Also does the wp_localize_script() for the request list args.
		 */
		public function list_js_controls_view( $str_preset = NULL ){

			if ( is_null($str_preset) || empty($str_preset) || ! is_string($str_preset) ){
				return false;
			}
			
			// get the preset's arr args
			$arr_presets = $this->list_js_controls_view_presets($str_preset);
			
			// do some checking
			if ( ! isset($arr_presets['controls']) ||  ! is_array($arr_presets['controls']) ){
				return false;
			}
			
			// do a bit more checking. 
			if ( ! isset($arr_presets['localize']) ||  ! is_array($arr_presets['localize']) ){
				return false;
			}
			
			// all good! get the defaults...let's the magic begin
			$arr_defaults = $this->list_js_controls_view_defaults();

			$arr_args = $arr_presets['controls'];
			$arr_localize = array_merge( $arr_defaults['localize'], $arr_presets['localize']);
			wp_localize_script( $this->_str_wp_localize, 'localizeListJSezFilters', $arr_localize );
			
			$str_to_return = '';
			foreach ( $arr_args as $str_key => $arr_values){
			
				if ( ! is_array($arr_values) ||  ! isset($arr_values['active'] ) || ( isset($arr_values['active']) && $arr_values['active'] !== true ) ){
					continue;
				}
				
				//id =
				$str_id = $arr_defaults['id'] . trim($str_key);
				if ( isset($arr_values['id']) ){
					$str_id = ' id="' . sanitize_text_field( $arr_values['id'] ) . '" '; 
				}
				// class =
				$str_class = $arr_defaults['class'] . trim($str_key);
				if ( isset($arr_values['class']) ){
					$str_class = ' class="' . sanitize_text_field( $arr_values['class'] ) . '" '; 
				}
				
				if ( isset($arr_values['control']) && strtolower($arr_values['control']) == 'input' ){
				
					$str_placeholder = $arr_defaults['placeholder']; // TODO - set via _default method
					if ( isset($arr_values['placeholder']) ){
						$str_placeholder = ' placeholder="' . sanitize_text_field($arr_values['placeholder']) . '"'; 
					}
					 
					$str_to_return .= '<input ' . $str_id . $str_class . $str_placeholder . '/>';
				
				} elseif ( isset($arr_values['control']) && strtolower($arr_values['control']) == 'button' && isset($arr_values['button_text'])  ){
				
					if ( ( isset($arr_values['button_type']) && strtolower($arr_values['button_type']) == 'sort' ) && ( isset($arr_values['data_sort']) ) ){
						// button - sort
						$str_to_return .=  '<button ' . $str_id . $str_class . ' data-sort="' . $arr_values['data_sort'] . '">'. $arr_values['button_text'] . '</button>';
					
					} elseif ( isset($arr_values['button_type']) && strtolower($arr_values['button_type']) == 'filter' && isset($arr_values['data_filtername']) && isset($arr_values['data_filter_value']) ){
						// button - filter
						$str_to_return .=  '<button ' . $str_id . $str_class . 'data-filtername="' . $arr_values['data_filtername'] . '" data-' . trim( $arr_values['data_filtername'] ) . '="' . $arr_values['data_filter_value'] . '">'. $arr_values['button_text'] . '</button>';
					}
				}	
			}
			
			if ( isset( $arr_presets['echo_do'] ) && $arr_presets['echo_do'] === true ){
				echo $str_to_return;
			}
			
			return $str_to_return;
		}
		
		/*
		 * Defines the defaults for the view. You probably won't need to change theses, but they're here if you feel you must.
		 */
		public function list_js_controls_view_defaults(){
		
			$arr_defaults = array(
								'id'			=> 'id-listjs-',
								'class'			=> 'class-listjs-',
								'placeholder'	=> '',
								'localize'		=> array(
														'wrapClass'				=> 'listjs-wrap-outer',	// List.js: class name of the List.js outer most wrapped
														'listClass'				=> 'listjs-wrap',		// List.js: class name of the list
														'valueNames'			=> array(),				// List.js: classes of list sub-elements to be "indexed" / "watched" by List.js
														'searchClass'			=> 'listjs-search',		// List.js:  class of the search input
														'sortClass'				=> 'sort',				// List.js: default: 'sort'
														'indexAsync'			=> false,				// List.js: default: false
														'page'					=> 200,					// List.js: default: 200
														'i'						=> 1,					// List.js: default: 1
														'plugins'				=> array(),				// List.js: see Listjs.com for details
														'dataAll'				=> 'all',				// ezFilters: what is the data- value of the "show all" filter? Basically, allow for non English values (although, it's never displayed).
														'classFilterButtons' 	=> 'listjs-filter',  	// ezFilters: "styling" classes should not be included. this is stritly the name of the selector class for the js to find the correct buttons for this set of filter
														'defaultDelimiter'		=> ',',					// ezFilters: if the filtered "column" is a delimited list, what is the delimiter?
														'filterDelimiter'		=> array(),				// ezFilters: if the filtered columns have different delimiters. Format: filter_name => '{delimiter}'
														'stripTags'				=> array(),				// ezFilters: by default html tags are stripped from filters. This allows you to overide that
																										//		...default dehavior. Format: filter_name => false
													),	
							);
							
			return $arr_defaults;
		}

		
		/*
		 * Define the buttons and js args for a given set of search+buttons. 
		 *
		 * In other words, here is where you can customize this template so that it works for your markup (as opposed to changing your markup to work with the template / js). 
		 *
		 * * IMPORTANT * - This is just an example and it should be over-ridden in your child class / plugin
		 */
		public function list_js_controls_view_presets($str_preset=NULL){
		
			if ( is_null($str_preset) ){
				return false;
			}
			
			$arr_list_js_presets = array();
			
			switch($str_preset){
			
				case 'case_1':	

					break;
				
				case 'case_x':
				
					break;
					
				default:
				
					$arr_list_js_presets = array(
								
												/*
												 * IMPORTANT - Key names of 'echo_do', 'controls' and 'localize' are all required 
												 */
												 
												'echo_do'		=> true,
												
												// define the controls for this list
												'controls'		=> array(
												
																		// *Important* - The list.js search must be within the outer wrapper. The buttons on the other hand can be anywhere - more or less												 

																		'search_box'			=> array(
																									'active' 			=> false,
																									'control'			=> 'input',
																									'id'				=> 'listjs-search',
																									'class'				=> 'listjs-search',
																									'placeholder'		=> 'Search',
																									'button_text'		=> NULL,
																									'button_type'		=> NULL, 			// 'sort' or 'filter'
																									'data_sort'			=> NULL,			// if 'sort' then what's the arg?
																									'data_filtername'	=> NULL,			// if filter then the name of the filter 
																									'data_filter_value'	=> NULL, 			// if filter the value
																								),
																								
																		'filter_all'			=> array(
																									'active' 			=> true,
																									'control'			=> 'button',
																									'id'				=> 'filter-all',
																									'class'				=> 'filter-button btn btn-default',
																									'placeholder'		=> NULL,			// note: when button, this will be ignored anyway. 
																									'button_text'		=> 'All',
																									'button_type'		=> 'filter', 		// 'sort' or 'filter'
																									'data_sort'			=> NULL,			// if 'sort' then what's the arg?
																									'data_filtername'	=> 'some-filter',	// if filter then the name of the filter 
																									'data_filter_value'	=> 'all',			// if filter the value
																								),
																								
																		'filter_print'			=> array(
																									'active' 			=> true,
																									'control'			=> 'button',																			
																									'id'				=> 'filter-print',
																									'class'				=> 'filter-button btn btn-default',
																									'placeholder'		=> NULL,			// note: when button, this will be ignored anyway. 
																									'button_text'		=> 'Printable',
																									'button_type'		=> 'filter',		// 'sort' or 'filter'
																									'data_sort'			=> NULL,			// if 'sort' then what's the arg?
																									'data_filtername'	=> 'some-filter',	// if filter then the name of the filter 
																									'data_filter_value'	=> 'print',			// if filter the value
																								),
																								
																		'filter_code'			=> array(
																									'active' 			=> true,
																									'control'			=> 'button',																			
																									'id'				=> 'filter-code',
																									'class'				=> 'filter-button btn btn-default',
																									'placeholder'		=> NULL,			// note: when button, this will be ignored anyway. 
																									'button_text'		=> 'Code',
																									'button_type'		=> 'filter',		// 'sort' or 'filter'
																									'data_sort'			=> NULL,			// if 'sort' then what's the arg?
																									'data_filtername'	=> 'some-filter',	// if filter then the name of the filter 
																									'data_filter_value'	=> 'code',			// if filter the value
																								),
																	),
												
												/*
												 * for this list, what are the options values to be passed to the js
												 *
												 * For more details on List.js options: http://listjs.com/docs/options
												 *
												 * For a complete list of what can be passed, see list_js_controls_view_defaults() above
												 */
												'localize'		=> array(
																		'wrapClass'				=> 'loop-wrap-outer',						// class name of the List.js outer most wrapped
																		'listClass'				=> 'loop-wrap',								// class name of the list
																		'searchClass'			=> 'listjs-search',							// class of the search input
																		'classFilterButtons' 	=> 'filter-button',  						// "styling" classes should not be included. this is stritly the name of the selector class for the js to find the correct buttons for this set of filter
																		'valueNames'			=> array('search-this', 'coupon-filter'),	// classes of list sub-elements to be "indexed" by List.js
																		'stripTags'				=> array(),
																		'filterDelimiter'		=> array(),
																	),		
											);					
					
					break;
			}
			
			return $arr_list_js_presets;	
		}
	
	
	} // close class
} // close if class_exists()
?>