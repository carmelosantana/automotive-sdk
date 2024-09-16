<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Vehicle;

class Search
{
    public function __construct()
    {
        add_filter('posts_join', [$this, 'vinSearchJoin']);
        add_filter('posts_where', [$this, 'vinSearchWhere']);
    }

    // add support to search for vin meta in the search box of edit.php
    public function vinSearchJoin($join)
    {
        global $wpdb;

        if (is_search()) {
            $join .= " INNER JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id ";
        }

        return $join;
    }

    // add support to search for vin meta in the search box of edit.php
    public function vinSearchWhere($where)
    {
        global $wpdb;

        if (is_search()) {
            $search = sanitize_text_field($_GET['s']);
            $where .= " OR ( $wpdb->postmeta.meta_key = 'vin' AND $wpdb->postmeta.meta_value LIKE '%$search%' ) ";
        }

        return $where;
    }
}
