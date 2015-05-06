<?php
/**
 * Created by PhpStorm.
 * User: walshcj
 * Date: 4/17/15
 * Time: 10:56 AM
 */

namespace Wordpress\UCI;


class News_Widget extends \WP_Widget {
    public function __construct() {
        parent::__construct('wordpress_uci_news_widget', 'News Widget');
    }

    public function widget($args, $instance) {
        $contacts = simple_fields_fieldgroup('contact_info_group');
        ?>
        <?php if(!empty($contacts)): ?>
        <h3 class="widgettitle">Contact Information</h3>
        <?php echo $args['before_widget']; ?>
        <div class="widget-text wp_widget_plugin_box">
            <ul>
                <?php foreach($contacts as $contact): ?>
                <li>
                    <div class="contactName"><?php echo $contact['contact_fullname']; ?></div>
                    <div class="contactPhone"><?php echo $contact['contact_phone']; ?></div>
                    <div class="contactEmail">
                        <a href="mailto:<?php echo $contact['contact_email']; ?>"><?php echo $contact['contact_email']; ?></a>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php echo $args['after_widget']; ?>
        <?php endif; ?>
        <?php
    }

    public function form($instance) {
        echo '';
    }
}