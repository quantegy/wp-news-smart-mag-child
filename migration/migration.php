<?php
set_include_path(get_include_path().PATH_SEPARATOR.TEMPLATEPATH.'-child/includes');

require_once 'Util.php';
require_once 'FlickrModel.php';
require_once 'News/DBO.php';
require_once 'functions.php';
require_once 'UCI/LDAP.php';

@define('POST_METADATA_WRITER', '_writer');
@define('POST_METADATA_LINK', '_link');

$db = new News_DBO('cwisdb2.cwis.uci.edu', 'wp-news', 'news', 'newsdotucidotedu');

//ucin_init();

$features = News_DBO::getInstance()->query("
        SELECT a.*
        FROM wp_posts AS a
        WHERE a.post_type LIKE 'features'
        AND a.post_status LIKE 'publish'
        AND a.post_date < '2013-08-20'
        ORDER BY a.post_date
        DESC
        LIMIT 0,10
    ")->fetchAll(PDO::FETCH_OBJ);

foreach($features as $feature) {
    $meta = News_DBO::getInstance()->query("
        SELECT a.*
        FROM wp_postmeta AS a
        WHERE a.post_id LIKE '" . $feature->ID . "'
        AND a.meta_key = 'content_author'
    ")->fetchObject();
    $author = json_decode($meta->meta_value);
    Util::debug($author); 
}
