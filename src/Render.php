<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk;

use Brick\Money\Money;
use Mustache_Engine;
use WpAutos\AutomotiveSdk\Vehicle\Fields;

/**
 * Class Render
 *
 * Handles the rendering of content using Mustache templates and output buffering.
 */
class Render
{
    /**
     * Constructor
     *
     * Starts output buffering and sets up shutdown actions for rendering.
     */
    public function __construct()
    {
        // Start output buffering at the very beginning
        ob_start();

        add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueBlockEditorAssets']);

        // On shutdown use Mustache to process our variables
        add_action('shutdown', [$this, 'processFinalOutput'], 0);
    }

    public function enqueueFontAwesome()
    {
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css');
    }

    public function enqueueBlockEditorAssets()
    {
        $this->enqueueFontAwesome();
    }

    public function enqueueMainStyles()
    {
        wp_enqueue_style('main-styles', ASDK_ASSETS_URL . '/css/style.css', []);
    }

    public function enqueueScripts()
    {
        $this->enqueueFontAwesome();
        $this->enqueueMainStyles();
    }

    /**
     * Process the final output buffer and render Mustache templates
     *
     * @return void
     */
    public function processFinalOutput(): void
    {
        // Only proceed if not in the admin area
        if (is_admin()) {
            return;
        }

        $finalOutput = '';

        // Get the number of output buffering levels
        $levels = ob_get_level();

        // Collect all output buffers into the final output
        for ($i = 0; $i < $levels; $i++) {
            $finalOutput .= ob_get_clean();
        }

        // Apply Mustache rendering to the final output
        echo $this->renderContent($finalOutput);
    }

    /**
     * Render content using Mustache templates
     *
     * @param string $content The content to render
     * @return string Rendered content
     */
    public function renderContent(string $content): string
    {
        global $post;

        // Proceed only if we're dealing with a 'vehicle' post type
        if (!isset($post) or $post->post_type !== 'vehicle') {
            return $content;
        }

        $fields = Fields::get();
        $meta = get_post_meta($post->ID);

        $data = [];

        // Collect custom fields data
        foreach ($fields as $header => $section) {
            foreach ($section['fields'] as $field) {
                $data[$field['name']] = $meta[$field['name']][0] ?? '';
            }
        }

        // Collect taxonomy terms
        $data['make']  = strip_tags(get_the_term_list($post->ID, 'make', '', ', ', ''));
        $data['model'] = strip_tags(get_the_term_list($post->ID, 'model', '', ', ', ''));
        $data['year']  = strip_tags(get_the_term_list($post->ID, 'year', '', ', ', ''));

        // Initialize Mustache Engine
        $m = new Mustache_Engine(['entity_flags' => ENT_QUOTES]);

        // Render the content with Mustache
        return $m->render($content, $data);
    }
}
