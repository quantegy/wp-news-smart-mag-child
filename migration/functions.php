<?php
function ucin_init() {
    global $wpdb;
    
    $features = News_DBO::getInstance()->query("
        SELECT a.*
        FROM wp_posts AS a
        WHERE a.post_type LIKE 'features'
        AND a.post_status LIKE 'publish'
        ORDER BY a.post_date
        DESC
        LIMIT 0,10
    ")->fetchAll(PDO::FETCH_OBJ);

    $coll = new ArrayObject();
    foreach($features as $num => $feature) {
        $item = new ArrayObject();
        $feature->post_title = iconv('windows-1252', 'UTF-8', $feature->post_title);
        $feature->post_content = iconv('windows-1252', 'UTF-8', $feature->post_content);
        $feature->post_excerpt = iconv('windows-1252', 'UTF-8', $feature->post_excerpt);

        $item->offsetSet('post', $feature);
        $item->offsetSet('meta', ucin_get_post_metas($feature->ID));
        $item->offsetSet('keywords', ucin_get_tags($feature->ID));
        $item->offsetSet('topics', ucin_get_topics($feature->ID));

        $coll->append($item);

    }

    ucin_do_import_file($coll);
}

function ucin_get_tags($post_id) {
    $tags = News_DBO::getInstance()->query("
        SELECT a.*
        FROM wp_terms AS a
        INNER JOIN wp_term_taxonomy AS b ON (b.term_id = a.term_id)
        INNER JOIN wp_term_relationships AS c ON (c.term_taxonomy_id = b.term_taxonomy_id)
        WHERE taxonomy = 'keywords' AND object_id = '" . $post_id . "'
    ")->fetchAll(PDO::FETCH_OBJ);
    
    return $tags;
}

function ucin_get_topics($post_id) {
    $tags = News_DBO::getInstance()->query("
        SELECT a.*
        FROM wp_terms AS a
        INNER JOIN wp_term_taxonomy AS b ON (b.term_id = a.term_id)
        INNER JOIN wp_term_relationships AS c ON (c.term_taxonomy_id = b.term_taxonomy_id)
        WHERE taxonomy = 'topics' AND object_id = '" . $post_id . "'
    ")->fetchAll(PDO::FETCH_OBJ);
    
    return $tags;
}

function create_custom_category($name, $slug, $description) {
    $isCategory = term_exists($name, 'category');
    
    if(!$isCategory) {
        $cat = wp_insert_term($name, 'category', array(
            'description' => $description,
            'slug' => $slug
        ));
    } else {
        $cat = $isCategory;
    }
    
    return $cat;
}

function ucin_do_import_file(ArrayObject $collection) {
    global $wpdb;
    
    /**
     * insert new Author category
     */
    $writerCat = create_custom_category('Writers', 'writers', 'Category reserved for UC Irvine content authors or writers.');
    $writerCatId = $writerCat['term_id'];
    
    /**
     * insert new Links category
     */
    $linkCat = create_custom_category('Links', 'links', 'Category reserved for all posts that are links.');
    $linkCatId = $linkCat['term_id'];
    
    /**
     * insert new Features category
     */
    
    $featCat = create_custom_category('Features', 'features', 'Detail stories that focus on recent news events.');
    $featCatId = $featCat['term_id'];
    
    /**
     * meta names:
     * - post_flickr_image (JSON object {id:, url:, title:, path:, caption:, credit:})
     * - featured_image: (serialized PHP: Flickr_Model class)
     * - featured_video: (serialized PHP: UCIVideo class)
     * - ucitube: (serialized PHP: UCIVideo class)
     * - ucitube_location: (predefined: may not be required for new design)
     * - related_links: (wp_link ID: move these to wp_posts as link type)
     * - content_author: (JSON object {name:, institution:})
     */
    
    foreach($collection as $num => $post_item) {
        $post = $post_item->offsetGet('post');
        $topics = $post_item->offsetGet('topics');
        $keywords = $post_item->offsetGet('keywords');
        $metas = $post_item->offsetGet('meta');
             
        $post_id = 0;
        $post_id = wp_insert_post(array(
            'post_status'   => 'publish',
            'post_type'     => 'post',
            'post_title'    => $post->post_title,
            'post_content'  => $post->post_content,
            'post_author'   => 1,
            'post_excerpt'  => $post->post_excerpt,
            'post_date'     => $post->post_date,
            'post_modified' => $post->post_modified,
            'menu_order'    => $post->menu_order,
            'comment_status' => 'closed'
        ));
        
        if($post_id !== 0 || !$post_id instanceof WP_Error || !empty($post_id)) {
            // assign post type features to category and assign to post
            wp_set_post_categories($post_id, array($featCatId), true);
            // assign categories to post
            ucin_assign_topics_to_post($post_id, $topics);
            // assign keywords/tags to post
            ucin_assign_keywords_to_post($post_id, $keywords);
            
            /**
             * Loop through all available meta info
             */
            foreach($metas as $meta) {
                switch($meta->meta_key) {
                    default: break;
                    case 'post_flickr_image':
                        // sideload and assign slideshow images from Flickr to post
                        ucin_process_post_flickr_meta($post_id, json_decode($meta->meta_value));
                        break;
                    case 'featured_image':
                        $flickr = unserialize($meta->meta_value);
                        if($flickr instanceof Flickr_Model) {
                            // sideload and assign featured image from Flickr to post
                            ucin_process_post_thumbnail($post_id, $flickr);
                        }
                        break;
                    case 'featured_video':
                        
                        break;
                    case 'ucitube':
                        
                        break;
                    case 'related_links':
                        // assign related links to post
                        $link = News_DBO::getInstance()->query(""
                                . "SELECT a.* "
                                . "FROM wp_links AS a "
                                . "WHERE a.link_id = " . News_DBO::getInstance()->quote($meta->meta_value, PDO::PARAM_INT) . " "
                                . "LIMIT 0,1")->fetchObject();
                        $linkId = ucin_create_link_post($post_id, $link, $linkCatId);
                        break;
                    case 'content_author':
                        // create an author post and assign to parent post
                        $author = ucin_create_author_post($post_id, json_decode($meta->meta_value), $writerCatId);
                        ucin_assign_author_user_to_post($post_id, $meta->meta_value);
                        break;
                }
            }
            
            echo ($num+1).") inserted feature post data for [ID: " . $post_id . "] " . $post->post_title . "\n";
        }
    }
}

/**
 * 
 * @param integer $parent_post_id
 * @param object $data Link object from old WP instance {link_name: "title", link_url: "URL"}
 */
function ucin_create_link_post($parent_post_id, $data, $cat_id) {
    $postId = wp_insert_post(array(
        'post_status' => 'publish',
        'post_title' => iconv('windows-1252', 'UTF-8', $data->link_name),
        'post_content' => $data->link_url,
        'comment_status' => 'closed',
        'post_parent' => $parent_post_id
    ));
    
    if(!is_wp_error($postId) || !empty($postId)) {
        set_post_format($postId, 'link');
        //$termsId = wp_set_post_terms($postId, array($cat_id), 'category');
        wp_set_post_categories($postId, array($cat_id));
        $add = add_post_meta($parent_post_id, POST_METADATA_LINK, $postId);
    }
    
    return $postId;
}

/**
 * NO LONGER USED!!!!
 * @param integer $parent_post_id
 * @param mixed $data
 * @param integer $category_id
 * @return boolean|integer
 */
function ucin_create_author_post($parent_post_id, $data, $category_id) {
    
    /**
     * 
     * $data['name'] Name
     * $data['institution'] Institution
     */
    $data = get_object_vars($data);
    
    $author = get_page_by_title($data['name'], OBJECT, 'post');
    
    if(empty($author)) {
        $postId = wp_insert_post(array(
            'post_status' => 'publish',
            'post_title' => $data['name'],
            'post_content' => implode(' ', $data),
            'comment_status' => 'closed'
        ));

        if(!is_wp_error($postId) || !empty($postId)) {
            $termIds = wp_set_post_terms($postId, array($category_id), 'category');
            $add = add_post_meta($parent_post_id, POST_METADATA_WRITER, $postId);

            return $postId;
        }
    } else {
        
        $termIds = wp_set_post_terms($author->ID, array($category_id), 'category');
        $add = add_post_meta($parent_post_id, POST_METADATA_WRITER, $author->ID);
        
        return $author->ID;
    }
    
    return false;
}

/**
 * 
 * @param integer $post_id
 * @param string $data Name, Institution
 */
function ucin_assign_author_to_post($post_id, $data) {
    
}

function get_attachment_id_from_src ($image_src) {
    global $wpdb;
    $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
    $id = $wpdb->get_var($query);
    return $id;
}

function ucin_process_post_thumbnail($post_id, Flickr_Model $flickrModel) {
    ucin_media_sideload_image($post_id, $flickrModel, true);
}

function ucin_media_sideload_image($post_id, Flickr_Model $flickrModel, $isFeatured = false) {
    require_once ABSPATH.'wp-admin/includes/media.php';
    require_once ABSPATH.'wp-admin/includes/file.php';
    require_once ABSPATH.'wp-admin/includes/image.php';
        
    $originalImage = $flickrModel->getOriginalUrl();
    
    $media = media_sideload_image($originalImage, $post_id, $flickrModel->getCredit());
    
    if(!empty($media) && !is_wp_error($media)) {
        $args = array(
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'post_parent' => $post_id
        );
        
        $attachments = get_posts($args);
        $attachment = $attachments[0];
        
        $attachMeta = get_post_meta($attachment->ID, '_wp_attachment_metadata', true);
        $uploadedFilename = get_attached_file($attachment->ID);
        if(unserialize($attachMeta) === false) {
            $attachMeta = wp_generate_attachment_metadata($attachment->ID, $uploadedFilename);
        }
        
        $flickrImgDesc = $flickrModel->getDescription();
        $flickrImgCaption = $flickrModel->getCaption();
        $imgDesc = (empty($flickrImgDesc)) ? iconv('windows-1252', 'UTF-8', $flickrModel->getTitle()) : iconv('windows-1252', 'UTF-8', $flickrModel->getDescription());
        $imgMetaTitle = (empty($attachMeta['image_meta']['title'])) ?  $imgDesc : $attachMeta['image_meta']['title'];
        $imgMetaCaption = (empty($flickrImgCaption)) ? $attachMeta['image_meta']['caption'] : iconv('windows-1252', 'UTF-8', $flickrModel->getCaption());
        $attachmentId = wp_update_post(array(
            'ID' => $attachment->ID,
            'post_content' => $imgMetaTitle,
            'post_excerpt' => $imgMetaCaption, //caption
        ));
        
        if($isFeatured === true) {
            set_post_thumbnail($post_id, $attachment->ID);
        }
        
        return $attachment->ID;
    }
    
    return false;
}

function ucin_process_post_flickr_meta($post_id, $flickr_data) {
    $url = $flickr_data->url;
    $title = $flickr_data->title;
    $caption = $flickr_data->caption;
    $credit = $flickr_data->credit;
    
    $flickrModel = new Flickr_Model();
    $flickrModel->setOriginalUrl($url);
    $flickrModel->setTitle($title);
    $flickrModel->setCaption($caption);
    $flickrModel->setCredit($credit);
}

/**
 * 
 * @param integer $post_id
 * @param array $keywords
 */
function ucin_assign_keywords_to_post($post_id, $keywords) {
    foreach($keywords as $keyword) {
        $tags = array();
        
        $cleanedTerm = iconv('windows-1252', 'UTF-8', $keyword->name);
        
        $doesExist = term_exists($cleanedTerm, 'post_tag');
        
        if($doesExist === 0 || is_null($doesExist)) {
            $term = wp_insert_term($cleanedTerm, 'post_tag', array(
                'description' => '',
                'slug' => $keyword->slug
            ));
            
            if(!is_wp_error($term)) {
                $newTerm = get_term($term['term_id'], 'post_tag');
                $tags[] = $newTerm->name;
            }
        } else {
            $tags[] = $doesExist->name;
        }
        
        $postTags = wp_set_post_tags($post_id, $tags, true);
    }
    
    return $postTags;
}

/**
 * Assign categories to posts
 * @param integer $post_id
 * @param array $topics
 * @return array
 */
function ucin_assign_topics_to_post($post_id, $topics) {
    $categoryIds = array();
    foreach($topics as $topic) { 
        $doesExist = term_exists($topic->name, 'category');
        
        if($doesExist === 0 || is_null($doesExist)) {
            $cleanedTerm = iconv('windows-1252', 'UTF-8', $topic->name);
            $term = wp_insert_term($cleanedTerm, 'category', array(
                'description' => '',
                'slug' => $topic->slug
            ));

            if(!is_wp_error($term)) {
                $categoryIds[] = $term['term_id'];
            }
        } else {
            $categoryIds[] = $doesExist['term_id'];
        }
    }
    
    $postCategoryIds = wp_set_post_categories($post_id, $categoryIds, true);
        
    return $postCategoryIds;
}

function ucin_get_post_metas($post_id) {
    $metas = News_DBO::getInstance()->query("
        SELECT a.*
        FROM wp_postmeta AS a
        WHERE a.post_id LIKE '" . $post_id . "'
        AND a.meta_key IN ('post_flickr_image', 'featured_image', 'featured_video', 'ucitube', 'ucitube_location', 'related_links', 'content_author')
    ")->fetchAll(PDO::FETCH_OBJ);
    
    return $metas;
}
