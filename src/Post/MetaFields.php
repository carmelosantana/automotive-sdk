<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Post;

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
            add_action('save_post', [$this, 'saveMetaBoxes']);
        }
    }

    /**
     * Register meta boxes dynamically based on $post_meta_fields
     */
    public function registerMetaBoxes(): void
    {
        foreach ($this->post_meta_fields as $field) {
            add_meta_box(
                $field['id'],
                $field['label'],
                function () use ($field) {
                    $this->renderMetaField($field);
                },
                $this->post_type
            );
        }
    }

    /**
     * Render the correct input type based on the field configuration
     */
    protected function renderMetaField(array $field): void
    {
        global $post;
        $value = get_post_meta($post->ID, $field['id'], true);

        echo '<label>' . esc_html($field['label']) . '</label><br>';

        // If a description is present, display it
        if (isset($field['description'])) {
            echo '<p class="description">' . esc_html($field['description']) . '</p>';
        }

        switch ($field['type']) {
            case 'group':
                echo '<div><h4>' . esc_html($field['label']) . '</h4>';
                foreach ($field['fields'] as $sub_field_key => $sub_field_group) {
                    $field_value = get_post_meta($post->ID, $field['id'], true);
                    foreach ($sub_field_group['fields'] as $sub_field) {
                        echo '<label>' . esc_html($sub_field['label']) . '</label>';
                        $sub_value = $field_value[$sub_field['id']] ?? '';
                        $this->renderFieldType($sub_field, $sub_value);
                    }
                    echo '<div style="border-bottom: 1px solid #ccc; margin: 10px 0;"></div>';

                }
                echo '</div>';
                break;

            case 'post_multi_select':
                $selected_posts = $value ?: [];
                $posts = $this->getPosts($field['post_type']);
                echo '<select name="' . esc_attr($field['id']) . '[]" multiple>';
                foreach ($posts as $post_id => $post_title) {
                    $selected = in_array($post_id, $selected_posts) ? 'selected' : '';
                    echo '<option value="' . esc_attr($post_id) . '" ' . $selected . '>' . esc_html($post_title) . '</option>';
                }
                echo '</select>';
                break;

            default:
                $this->renderFieldType($field, $value);
                break;
        }
    }

    /**
     * Render different field types
     */
    protected function renderFieldType(array $field, string $value): void
    {
        switch ($field['type']) {
            case 'textarea':
                echo '<textarea name="' . esc_attr($field['id']) . '">' . esc_textarea($value) . '</textarea>';
                break;

            case 'checkbox':
                $checked = $value ? 'checked' : '';
                echo '<input type="checkbox" name="' . esc_attr($field['id']) . '" value="1" ' . $checked . ' />';
                break;

            case 'select':
                echo '<select name="' . esc_attr($field['id']) . '">';
                foreach ($field['options'] as $option_value => $option_label) {
                    $selected = ($value == $option_value) ? 'selected' : '';
                    echo '<option value="' . esc_attr($option_value) . '" ' . $selected . '>' . esc_html($option_label) . '</option>';
                }
                echo '</select>';
                break;

            case 'multi-select':
                echo '<select name="' . esc_attr($field['id']) . '[]" multiple>';
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
                    echo '<input type="radio" name="' . esc_attr($field['id']) . '" value="' . esc_attr($option_value) . '" ' . $checked . ' />';
                    echo esc_html($option_label);
                    echo '</label><br>';
                }
                break;

            default:
                echo '<input type="text" name="' . esc_attr($field['id']) . '" value="' . esc_attr($value) . '" />';
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

        foreach ($this->post_meta_fields as $field) {
            if ($field['type'] === 'group') {
                $group_data = [];
                foreach ($field['fields'] as $sub_field_group) {
                    foreach ($sub_field_group['fields'] as $sub_field) {
                        if (isset($_POST[$sub_field['id']])) {
                            $group_data[$sub_field['id']] = sanitize_text_field($_POST[$sub_field['id']]);
                        }
                    }
                }
                update_post_meta($post_id, $field['id'], $group_data);
            } elseif ($field['type'] === 'multi-select') {
                $selected_values = isset($_POST[$field['id']]) ? array_map('sanitize_text_field', $_POST[$field['id']]) : [];
                update_post_meta($post_id, $field['id'], $selected_values);
            } else {
                if (isset($_POST[$field['id']])) {
                    update_post_meta($post_id, $field['id'], sanitize_text_field($_POST[$field['id']]));
                } elseif ($field['type'] === 'checkbox') {
                    update_post_meta($post_id, $field['id'], '0'); // Handle unchecked checkboxes
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
