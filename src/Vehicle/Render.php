<?php

declare(strict_types=1);

namespace WpAutos\VehiclesSdk\Vehicle;

use Brick\Money\Money;
use Mustache_Engine;

class Render
{
    // This is the vehicle post type.
    public function __construct()
    {
        // render mustache template for the_content and any other area on screen
        add_filter('the_content', [$this, 'renderContent']);
    }

    // Render content
    public function renderContent($content)
    {
        global $post;

        if ($post->post_type !== 'vehicle') {
            return $content;
        }

        $fields = Fields::get();
        $meta = get_post_meta($post->ID);

        $data = [];
        foreach ($fields as $section => $field) {
            foreach ($field['fields'] as $field) {
                $data[$field['name']] = $meta[$field['name']][0] ?? '';
            }
        }

        // get taxonomy terms
        $data['make'] = get_the_term_list($post->ID, 'make', '', ', ', '');
        $data['model'] = get_the_term_list($post->ID, 'model', '', ', ', '');
        $data['year'] = get_the_term_list($post->ID, 'year', '', ', ', '');

        $m = new Mustache_Engine(['entity_flags' => ENT_QUOTES]);
        $content = $m->render($content, $data);

        return $content;
    }
}
