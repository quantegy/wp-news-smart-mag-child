<?php
ini_set('display_errors', false);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).DIRECTORY_SEPARATOR.'includes');

add_action('after_setup_theme', 'my_custom_init', 12);
function my_custom_init() {
    add_theme_support('post-formats', array(
        'aside',
        'gallery',
        'link',
        'image',
        'video',
        'audio'
    ));
    
    add_image_size('main-slider-full', 1078, 516, true);
    add_image_size('full-size-image', 0, 0, false);
}

add_action('wp_print_scripts', 'ucinews_wp_print_scripts');
function ucinews_wp_print_scripts() {
    wp_enqueue_style('smart-mag-child', get_theme_root_uri().'/smart-mag-child/css/child.css');
}

add_filter('content_template', 'ucinews_content_template');
/**
 * Need to force the use of child content.php
 * @global WP_Post $post
 * @param string $content_template
 * @return string
 */
function ucinews_content_template($content_template) {
    global $post;
    
    $content_template = dirname(__FILE__) . '/content.php';
    
    return $content_template;
}

/**
 * migration business
 */
//add_submenu_page('tools.php', 'Migration', 'Migration', 'edit_users', 'migration', 'ucinews_migration');
function ucinews_migration() {
    //require_once 'migration/migration.php';
}

/**
 * widget for adding simple fields items
 */
add_action('widgets_init', 'ucinews_widgets_init');
function ucinews_widgets_init() {
    require_once 'News_Widget.php';

    register_widget('Wordpress\UCI\News_Widget');
}

/**
 * @return bool True is featured image is square or portrait aspect ratio
 */
function ucinews_is_featured_portrait() {
    $isPortrait = false;
    $thumbID = get_post_thumbnail_id();
    $thumbURL = wp_get_attachment_image_src($thumbID, 'full-size-image');
    $width = $thumbURL[1];
    $height = $thumbURL[2];
    if($height/$width >= 1){
        $isPortrait = true;
    }
    return $isPortrait;
}


/**
 * Filters the sidebar markup for accessibility. Removes invalid span for
 * comments and removes links for thumbnails when no thumbnail is present
 *
 * @param $sidebarName The name of the sidebar to be filtered
 * @return string   The markup for the filtered sidebar
 */
function ucinews_filter_sidebar($sidebarName){
    ob_start();
    $str = '';
    $bool = dynamic_sidebar( $sidebarName );
    if ( $bool ){
        $str = ob_get_contents();
        $str = "<li><ul>".$str."</ul></li>";
        $doc = new DOMDocument();
        $doc->loadXML($str);
        $xpath = new DomXpath($doc);
        $elements = $xpath->query("//*[@id='bunyad-latest-posts-widget-2']/ul/li/div/span");
        if($elements!==False){
            foreach($elements as $element){
                  $element->parentNode->removeChild($element);
            }
        }
        $elements = $xpath->query("//*[@id='bunyad-latest-posts-widget-2']/ul/li/a");
        if($elements!==False){
            foreach($elements as $element){
                if($element->firstChild->nodeType!=XML_ELEMENT_NODE){
                    $element->parentNode->removeChild($element);
                }
            }
        }
        $str = $doc->saveXML($doc->documentElement, LIBXML_NOXMLDECL);
    }
    ob_end_clean();


    return $str;
}