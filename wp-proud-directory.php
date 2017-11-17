<?php
/*
Plugin Name: Proud Directory
Plugin URI: http://proudcity.com/
Description: Declares an Directory custom post type.
Version: 1.0
Author: ProudCity
Author URI: http://proudcity.com/
License: Affero GPL v3
*/

namespace Proud\Directory;

// Load Extendible
// -----------------------
if (!class_exists('ProudPlugin')) {
  require_once(plugin_dir_path(__FILE__) . '../wp-proud-core/proud-plugin.class.php');
}

class ProudDirectory extends \ProudPlugin {

  public function __construct() {

    $this->hook('init', 'create_directory');
    $this->hook('admin_enqueue_scripts', 'agency_assets');
    $this->hook('rest_api_init', 'directory_rest_support');
  }

  //add assets
  public function agency_assets() {
    $path = plugins_url('assets/', __FILE__);
    wp_enqueue_script('proud-directory/js', $path . 'js/proud-directory.js', [
      'proud',
      'jquery',
    ], NULL, TRUE);
  }


  public function create_directory() {
    $labels = [
      'name'               => _x('Directory Locations', 'post name', 'wp-directory'),
      'singular_name'      => _x('Directory Location', 'post type singular name', 'wp-directory'),
      'menu_name'          => _x('Directories', 'admin menu', 'wp-directory'),
      'name_admin_bar'     => _x('Directory Location', 'add new on admin bar', 'wp-directory'),
      'add_new'            => _x('Add New', 'directory', 'wp-directory'),
      'add_new_item'       => __('Add New Directory Location', 'wp-directory'),
      'new_item'           => __('New Directory Location', 'wp-directory'),
      'edit_item'          => __('Edit Directory Location', 'wp-directory'),
      'view_item'          => __('View Directory Location', 'wp-directory'),
      'all_items'          => __('All Directory Locations', 'wp-directory'),
      'search_items'       => __('Search directory', 'wp-directory'),
      'parent_item_colon'  => __('Parent directory:', 'wp-directory'),
      'not_found'          => __('No directory locations found.', 'wp-directory'),
      'not_found_in_trash' => __('No directory locations found in Trash.', 'wp-directory'),
    ];

    $args = [
      'labels'                => $labels,
      'description'           => __('Description.', 'wp-directory'),
      'public'                => TRUE,
      'publicly_queryable'    => TRUE,
      'show_ui'               => TRUE,
      'show_in_menu'          => TRUE,
      'query_var'             => TRUE,
      'rewrite'               => ['slug' => 'directories'],
      'capability_type'       => 'post',
      'has_archive'           => FALSE,
      'hierarchical'          => FALSE,
      'menu_position'         => NULL,
      'show_in_rest'          => TRUE,
      'rest_base'             => 'directories',
      'rest_controller_class' => 'WP_REST_Posts_Controller',
      'supports'              => ['title', 'editor'],
    ];

    register_post_type('directory', $args);
  }

  public function directory_rest_support() {
    register_rest_field('directory',
      'meta',
      [
        'get_callback'    => [$this, 'directory_rest_metadata'],
        'update_callback' => NULL,
        'schema'          => NULL,
      ]
    );
  }

  /**
   * Alter the REST endpoint.
   * Add metadata to t$forms = RGFormsModel::get_forms( 1, 'title' );he post
   * response
   */
  public function directory_rest_metadata($object, $field_name, $request) {
    $DirectoryMeta = new DirectoryMeta;
    return $DirectoryMeta->get_options($object['id']);
  }

} // class
$Directory = new ProudDirectory;


// Directorys meta box
class DirectoryMeta extends \ProudMetaBox {

  public $options = [  // Meta options, key => default
    'icon'                      => '',
    'directory_location_type'   => 'map',
    'directory_address'         => '',
    'directory_description'     => '',
    'directory_info_type'       => 'post',
    'directory_phone'           => '',
    'directory_post'            => '',
    'directory_url'             => '',
    'directory_location_method' => 'walking',
    'directory_image'           => '',
  ];

  public function __construct() {
    parent::__construct(
      'directory', // key
      'Directory information', // title
      'directory', // screen
      'normal',  // position
      'high' // priority
    );
  }

  /**
   * Called on form creation
   *
   * @param $displaying : false if just building form, true if about to
   *     display Use displaying:true to do any difficult loading that should
   *     only occur when the form actually will display
   */
  public function set_fields($displaying) {

    // Already set, no loading necessary
    if ($displaying) {
      return;
    }

    $this->fields = [];

    $this->fields['icon'] = [
      '#type'        => 'fa-icon',
      '#title'       => __('Icon'),
      '#description' => __('Select the icon to use in the Actions app'),
    ];

    $this->fields['directory_description'] = [
      '#type'        => 'textarea',
      '#title'       => __('Description'),
      '#description' => __pcHelp('This is the tagline about the map or floorplan image.'),
    ];

    $this->fields['directory_phone'] = [
      '#type'  => 'text',
      '#title' => __('Phone number'),
    ];

//    $this->fields['directory_info_type'] = [
//      '#type'    => 'radios',
//      '#title'   => __('Website Type'),
//      '#options' => [
//        'post' => __('WordPress Page (embedded)'),
//        'url'  => __('External Website'),
//      ],
//    ];
//
//    $this->fields['directory_post'] = [
//      '#type'        => 'text',
//      '#title'       => __('Post ID'),
//      '#description' => __pcHelp('Enter the post ID.  To find the post ID, find the page you would like to display on the backend and click the edit button. The post ID will be the number that appears in the URL: ?post=<strong>11066</strong>&amp;action=edit.'),
//      '#states'      => [
//        'visible' => [
//          'directory_info_type' => [
//            'operator' => '==',
//            'value'    => ['post'],
//            'glue'     => '||',
//          ],
//        ],
//      ],
//    ];

    $this->fields['directory_url'] = [
      '#type'        => 'text',
      '#title'       => __('Website URL'),
      '#description' => __pcHelp('Enter an external url.  This will appear as button pointing to the outside url.'),
//      '#states'      => [
//        'visible' => [
//          'directory_info_type' => [
//            'operator' => '==',
//            'value'    => ['url'],
//            'glue'     => '||',
//          ],
//        ],
//      ],
    ];


    $this->fields['directory_location_type'] = [
      '#type'    => 'radios',
      '#title'   => __('Location Type'),
      '#options' => [
        'map'   => __('Google Map with Directions to an Address'),
        'image' => __('Image and Description'),
      ],
    ];

    $this->fields['directory_address'] = [
      '#type'        => 'textarea',
      '#title'       => __('Address'),
      '#description' => __pcHelp('Enter the address. This will appear as Google Map.'),
      '#states'      => [
        'visible' => [
          'directory_location_type' => [
            'operator' => '==',
            'value'    => ['map'],
            'glue'     => '||',
          ],
        ],
      ],
    ];

    $this->fields['directory_location_method'] = [
      '#type'    => 'select',
      '#title'   => __('Directions Method'),
      '#options' => [
        'walking' => __('Walking'),
        'driving' => __('Driving'),
        'transit' => __('Transit'),
      ],
      '#states'  => [
        'visible' => [
          'directory_location_type' => [
            'operator' => '==',
            'value'    => ['map'],
            'glue'     => '||',
          ],
        ],
      ],
    ];

    $this->fields['directory_image'] = [
      '#title'         => __('Image', 'wp-proud-core'),
      '#type'          => 'select_media',
      '#default_value' => '',
      '#description'   => 'Upload a floorplan with directions.',
      '#states'        => [
        'visible' => [
          'directory_location_type' => [
            'operator' => '==',
            'value'    => ['image'],
            'glue'     => '||',
          ],
        ],
      ],
    ];

    //    $this->fields['directory_directions_description'] = [
    //      '#type'        => 'textarea',
    //      '#title'       => __pcHelp('Directions Description'),
    //      '#description' => __pcHelp('Images and HTML code are allowed.'),
    //      '#states'      => [
    //        'visible' => [
    //          'directory_location_type' => [
    //            'operator' => '==',
    //            'value'    => ['image'],
    //            'glue'     => '||',
    //          ],
    //        ],
    //      ],
    //    ];


  }

  /**
   * Displays the Directories metadata fieldset.
   */
  public function settings_content($post) {
    // Call parent
    parent::settings_content($post);
    // Add js settings
    global $proudcore;
    $settings = $this->get_field_names(['directory_category_type']);
    $proudcore->addJsSettings([
      'proud_directory' => $settings,
    ]);
  }
}

if (is_admin()) {
  new DirectoryMeta;
}
