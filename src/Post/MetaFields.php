<?php

declare(strict_types=1);

namespace WipyAutos\AutomotiveSdk\Post;

class MetaFields
{
    protected string $post_type;
    protected array $post_meta_fields;

    public function __construct(string $post_type, array $post_meta_fields = [])
    {
        $this->post_type = $post_type;
        $this->post_meta_fields = $post_meta_fields;

        $this->registerHooks();
    }

    /**
     * Register hooks for meta boxes
     */
    public function registerHooks(): void
    {
        if (!empty($this->post_meta_fields)) {
            add_action('add_meta_boxes', [$this, 'registerMetaBoxes']);
            add_action('save_post_' . $this->post_type, [$this, 'saveMetaBoxes']);
        }
    }

    /**
     * Register meta boxes dynamically based on $post_meta_fields with sections.
     */
    public function registerMetaBoxes(): void
    {
        foreach ($this->post_meta_fields as $section_key => $section) {
            // Register a meta box for each section
            add_meta_box(
                $section_key,  // Section key as ID
                $section['label'],  // Label for the meta box
                function () use ($section) {
                    $this->renderMetaSection($section);
                },
                $this->post_type
            );
        }
    }

    /**
     * Render all fields within a section.
     */
    protected function renderMetaSection(array $section): void
    {
        $this->renderDescription($section);

        // Loop through all fields in the section and render them
        foreach ($section['fields'] as $field) {
            $this->renderMetaField($field);  // Reuse the existing field rendering logic
        }
    }

    /**
     * Render the correct input type based on the field configuration
     */
    protected function renderMetaField(array $field): void
    {
        global $post;
        $value = get_post_meta($post->ID, $field['name'], true);

        switch ($field['type']) {
            case 'group':
                echo '<div><h3>' . esc_html($field['label']) . '</h3>';
                $this->renderDescription($field);
                foreach ($field['fields'] as $sub_field_key => $sub_field_group) {
                    echo '<div style="background-color: #f1f1f1; border: 1px solid #ccc; border-radius: 5px; padding: 10px; margin: 10px 0;">';
                    echo '<h4>' . esc_html($sub_field_group['label'] ?? ucwords($sub_field_key)) . '</h4>';
                    $this->renderDescription($sub_field_group);
                    foreach ($sub_field_group['fields'] as $sub_field) {
                        $sub_value = $value[$sub_field['name']] ?? '';
                        $this->renderFieldType($sub_field, $sub_value);
                    }
                    echo '</div>';
                }
                echo '</div>';
                break;

            case 'post_multi_select':
                echo '<div><h3>' . esc_html($field['label']) . '</h3>';
                $this->renderDescription($field);
                $selected_posts = $value ?: [];
                $posts = $this->getPosts($field['post_type']);
                echo '<select name="' . esc_attr($field['name']) . '[]" multiple>';
                foreach ($posts as $post_id => $post_title) {
                    $selected = in_array($post_id, $selected_posts) ? 'selected' : '';
                    echo '<option value="' . esc_attr($post_id) . '" ' . $selected . '>' . esc_html($post_title) . '</option>';
                }
                echo '</select>';
                echo '</div>';
                break;

            case 'select_from_media_library':
            case 'image':
                echo '<div><h3>' . esc_html($field['label']) . '</h3>';
                $this->renderDescription($field);
                $selected_img_id = $value ?: '';
                $selected_img_url = wp_get_attachment_image_url($selected_img_id, 'thumbnail');
                echo '<input type="hidden" name="' . esc_attr($field['name']) . '" value="' . esc_attr($selected_img_id) . '" />';
                echo '<img src="' . esc_url($selected_img_url) . '" style="max-width: 100px; max-height: 100px;" />';
                echo '<button class="button" id="select-img-btn">Select Image</button>';
                echo '</div>';

                // Enqueue media library scripts
                // wp.media is not a function
                wp_enqueue_media();
                add_action('admin_footer', function () use ($field) {
?>
                    <script>
                        jQuery(document).ready(function($) {
                            $('#select-img-btn').on('click', function(e) {
                                e.preventDefault();
                                var image = wp.media({
                                        title: 'Select or Upload Image',
                                        multiple: false
                                    }).open()
                                    .on('select', function(e) {
                                        var uploaded_image = image.state().get('selection').first();
                                        var image_url = uploaded_image.toJSON().url;
                                        var image_id = uploaded_image.toJSON().id;
                                        $('input[name="<?php echo esc_attr($field['name']); ?>"]').val(image_id);
                                        $('img').attr('src', image_url);
                                    });
                            });
                        });
                    </script>
<?php
                });
                break;

            default:
                $this->renderFieldType($field, $value);
                break;
        }
    }

    // render description
    protected function renderDescription(array $field): void
    {
        if (!empty($field['description'])) {
            echo '<p class="description">' . esc_html($field['description']) . '</p>';
        }
    }

    // Render different field types
    protected function renderFieldType(array $field, string $value): void
    {
        $slug = $field['name'];
        echo '<div style="background-color: #f9f9f9; border: 1px solid #ccc; border-radius: 5px; padding: 10px; margin: 10px 0;">';

        switch ($field['type']) {
            case 'checkbox':
                $this->renderFieldInput($field, $value);
                $this->renderFieldLabel($field);
                break;

            default:
                $this->renderFieldLabel($field);
                $this->renderFieldInput($field, $value);
                break;
        }

        // If a description is present, display it
        if (isset($field['description'])) {
            echo '<p class="description">' . esc_html($field['description']) . '</p>';
        }

        echo '</div>';
    }

    protected function renderFieldLabel(array $field): void
    {
        $label = $field['label'] ?? ucwords($field['name']);
        echo ' <label for="' . esc_attr($field['name']) . '">' . esc_html($label) . '</label>';
        echo ' <small style="font-family: monospace; background-color: #ddd; padding: 2px 5px; border-radius: 3px;">' . esc_html($field['name']) . '</small>';
    }

    /**
     * Render different field types
     */
    protected function renderFieldInput(array $field, string $value): void
    {
        echo ' ';

        switch ($field['type']) {
            case 'checkbox':
                $checked = $value ? 'checked' : '';
                echo '<input type="checkbox" name="' . esc_attr($field['name']) . '" value="1" ' . $checked . ' />';
                break;

            case 'textarea':
                echo '<textarea name="' . esc_attr($field['name']) . '">' . esc_textarea($value) . '</textarea>';
                break;

            case 'select':
                echo '<select name="' . esc_attr($field['name']) . '">';
                foreach ($field['options'] as $option_value => $option_label) {
                    $selected = ($value == $option_value) ? 'selected' : '';
                    echo '<option value="' . esc_attr($option_value) . '" ' . $selected . '>' . esc_html($option_label) . '</option>';
                }
                echo '</select>';
                break;

            case 'multi-select':
                echo '<select name="' . esc_attr($field['name']) . '[]" multiple>';
                $values = is_array($value) ? $value : [];
                foreach ($field['options'] as $option_value => $option_label) {
                    $selected = in_array($option_value, $values) ? 'selected' : '';
                    echo '<option value="' . esc_attr($option_value) . '" ' . $selected . '>' . esc_html($option_label) . '</option>';
                }
                echo '</select>';
                break;

            case 'radio':
                foreach ($field['options'] as $option_value => $option_label) {
                    $checked = ($value == $option_value) ? 'checked' : '';
                    echo '<label>';
                    echo '<input type="radio" name="' . esc_attr($field['name']) . '" value="' . esc_attr($option_value) . '" ' . $checked . ' />';
                    echo esc_html($option_label);
                    echo '</label><br>';
                }
                break;

            case 'text':
                echo '<input type="text" name="' . esc_attr($field['name']) . '" value="' . esc_attr($value) . '" />';
                break;

            case 'number':
                echo '<input type="number" name="' . esc_attr($field['name']) . '" value="' . esc_attr($value) . '" />';
                break;

            case 'email':
                echo '<input type="email" name="' . esc_attr($field['name']) . '" value="' . esc_attr($value) . '" />';
                break;

            case 'url':
                echo '<input type="url" name="' . esc_attr($field['name']) . '" value="' . esc_attr($value) . '" />';
                break;

            case 'password':
                echo '<input type="password" name="' . esc_attr($field['name']) . '" value="' . esc_attr($value) . '" />';
                break;

            case 'hidden':
                echo '<input type="hidden" name="' . esc_attr($field['name']) . '" value="' . esc_attr($value) . '" />';
                break;
        }
    }

    /**
     * Save meta boxes
     */
    public function saveMetaBoxes(int $post_id): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        foreach ($this->post_meta_fields as $section_key => $section) {
            foreach ($section['fields'] as $field) {
                if ($field['type'] === 'group') {
                    $group_data = [];
                    foreach ($field['fields'] as $sub_field_group) {
                        foreach ($sub_field_group['fields'] as $sub_field) {
                            if (isset($_POST[$sub_field['name']])) {
                                $group_data[$sub_field['name']] = sanitize_text_field($_POST[$sub_field['name']]);
                            }
                        }
                    }
                    update_post_meta($post_id, $field['name'], $group_data);
                } elseif ($field['type'] === 'multi-select' or $field['type'] === 'post_multi_select') {
                    $selected_values = isset($_POST[$field['name']]) ? array_map('sanitize_text_field', $_POST[$field['name']]) : [];
                    update_post_meta($post_id, $field['name'], $selected_values);
                } else {
                    if (isset($_POST[$field['name']])) {
                        update_post_meta($post_id, $field['name'], sanitize_text_field($_POST[$field['name']]));
                    } elseif ($field['type'] === 'checkbox') {
                        update_post_meta($post_id, $field['name'], '0'); // Handle unchecked checkboxes
                    }
                }
            }
        }
    }

    /**
     * Fetch posts for a given post type
     */
    protected function getPosts(string $post_type): array
    {
        $posts = get_posts([
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);

        $options = [];
        foreach ($posts as $post) {
            $options[$post->ID] = $post->post_title;
        }

        return $options;
    }
}
