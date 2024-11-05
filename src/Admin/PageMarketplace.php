<?php

declare(strict_types=1);

namespace WipyAutos\AutomotiveSdk\Admin;

class PageMarketplace extends Page
{
    protected $menu_title = 'Marketplace';
    protected $page_slug = 'marketplace';
    protected $marketplace_items;

    public function __construct()
    {
        parent::__construct();
        $this->marketplace_items = $this->getMarketplaceItems();

        // Register AJAX handler for logged-in users
        add_action('wp_ajax_search_marketplace_items', [$this, 'searchMarketplaceItems']);
        // Register AJAX handler for non-logged-in users (optional)
        add_action('wp_ajax_nopriv_search_marketplace_items', [$this, 'searchMarketplaceItems']);
    }

    /**
     * Displays the count of marketplace items.
     */
    public function adminCounts(): void
    {
        echo '<span class="title-count marketplace-count">' . count($this->marketplace_items) . '</span>';
    }

    /**
     * Renders the marketplace page content.
     */
    public function adminContent(): void
    {
?>
        <div class="marketplace">
            <form class="search-form search-themes">
                <p class="search-box">
                    <label for="wp-filter-search-input">Search Marketplace</label>
                    <input type="search" id="wp-filter-search-input" class="wp-filter-search" data-ajax-url="<?php echo admin_url('admin-ajax.php'); ?>" autocomplete="off">
                </p>
            </form>
            <div class="theme-browser rendered">
                <div id="marketplace-items" class="themes wp-clearfix">
                    <?php foreach ($this->marketplace_items as $item): ?>
                        <div class="theme <?php echo esc_attr($item['class']); ?>" data-slug="<?php echo esc_attr($item['slug']); ?>">
                            <div class="theme-screenshot">
                                <img src="<?php echo esc_url($item['thumbnail_url']); ?>" alt="">
                            </div>
                            <button type="button" aria-label="View Details for <?php echo esc_html($item['title']); ?>" class="more-details">
                                <?php echo esc_html($item['title']); ?> Details
                            </button>
                            <div class="theme-author">
                                By <a href="<?php echo esc_url($item['author_url']); ?>" target="_blank"><?php echo esc_html($item['author_name']); ?></a>
                            </div>
                            <div class="theme-id-container">
                                <h2 class="theme-name"><?php echo esc_html($item['title']); ?></h2>
                                <div class="theme-actions">
                                    <a href="<?php echo esc_url($item['product_url']); ?>" class="button button-primary" target="_blank">View Product</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX handler to search and filter marketplace items based on the search term.
     */
    public function searchMarketplaceItems(): void
    {
        // Verify nonce for security
        check_ajax_referer('marketplace_search_nonce', '_ajax_nonce');

        // Get the search term
        $term = sanitize_text_field($_POST['term']);
        $filtered_items = [];

        // Filter the marketplace items based on the search term
        foreach ($this->getMarketplaceItems() as $item) {
            if (stripos($item['title'], $term) !== false || stripos($item['category'], $term) !== false) {
                $filtered_items[] = $item;
            }
        }

        // Send the filtered items back to the client
        wp_send_json_success($this->renderMarketplaceItems($filtered_items));
    }

    /**
     * Renders the HTML for the filtered marketplace items.
     *
     * @param array $items The marketplace items to render.
     * @return string The HTML of the filtered items.
     */
    private function renderMarketplaceItems(array $items): string
    {
        ob_start();

        if (empty($items)) {
            echo '<p>No marketplace items found.</p>';
        } else {
            foreach ($items as $item) {
        ?>
                <div class="theme <?php echo esc_attr($item['class']); ?>" data-slug="<?php echo esc_attr($item['slug']); ?>">
                    <div class="theme-screenshot">
                        <img src="<?php echo esc_url($item['thumbnail_url']); ?>" alt="">
                    </div>
                    <button type="button" aria-label="View Details for <?php echo esc_html($item['title']); ?>" class="more-details">
                        <?php echo esc_html($item['title']); ?> Details
                    </button>
                    <div class="theme-author">
                        By <a href="<?php echo esc_url($item['author_url']); ?>" target="_blank"><?php echo esc_html($item['author_name']); ?></a>
                    </div>
                    <div class="theme-id-container">
                        <h2 class="theme-name"><?php echo esc_html($item['title']); ?></h2>
                        <div class="theme-actions">
                            <a href="<?php echo esc_url($item['product_url']); ?>" class="button button-primary" target="_blank">View Product</a>
                        </div>
                    </div>
                </div>
<?php
            }
        }

        return ob_get_clean();
    }

    /**
     * Gets the marketplace items.
     * This should be replaced with actual logic to fetch dynamic data.
     *
     * @return array The list of marketplace items.
     */
    private function getMarketplaceItems(): array
    {
        $marketplace_items = [
            [
                'title' => 'Awesome Plugin',
                'product_url' => 'https://example.com/awesome-plugin',
                'thumbnail_url' => 'https://example.com/images/awesome-plugin.jpg',
                'class' => 'active',
                'author_name' => 'John Doe',
                'author_url' => 'https://example.com',
                'category' => 'Plugin',
                'slug' => 'awesome-plugin'
            ],
            [
                'title' => 'Super SaaS',
                'product_url' => 'https://example.com/super-saas',
                'thumbnail_url' => 'https://example.com/images/super-saas.jpg',
                'class' => '',
                'author_name' => 'Jane Smith',
                'author_url' => 'https://example.com',
                'category' => 'SaaS',
                'slug' => 'super-saas'
            ],
            [
                'title' => 'Cool Service',
                'product_url' => 'https://example.com/cool-service',
                'thumbnail_url' => 'https://example.com/images/cool-service.jpg',
                'class' => '',
                'author_name' => 'Service Provider',
                'author_url' => 'https://example.com',
                'category' => 'Service',
                'slug' => 'cool-service'
            ]
        ];

        return $marketplace_items;
    }

    /**
     * Enqueue the JavaScript for the AJAX search functionality.
     */
    public function adminEnqueue(): void
    {
        wp_enqueue_script('marketplace-search-js', plugin_dir_url(ASDK__FILE__) . 'assets/js/marketplace-search.js', ['jquery'], null, true);

        // Pass AJAX URL and nonce to the script
        wp_localize_script('marketplace-search-js', 'marketplaceSearch', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('marketplace_search_nonce'),
        ]);
    }
}
