<?php

declare(strict_types=1);

namespace WipyAutos\AutomotiveSdk\Admin;

class PageImportTools extends PageImport
{
    public function adminContent(): void
    {
        $this->adminDebugTools();
        $this->adminRunTools();
        $this->adminFilesGetHeaders();
    }

    public function adminDebugTools(): void
    {
        $actions = [
            [
                'description' => 'Deletes all vehicles.',
                'label' => 'Delete All Vehicles',
                'buttons' => [
                    [
                        'label' => 'Delete All Vehicles',
                        'tab' => 'import',
                        'args' => [
                            'tool' => 'delete_all_vehicles',
                        ],
                        'confirm' => 'Are you sure you want to delete all vehicles?'
                    ]
                ]
            ],
            [
                'description' => 'Refreshes the file library.',
                'label' => 'Refresh Files',
                'buttons' => [
                    [
                        'label' => 'Refresh Files',
                        'tab' => 'import',
                        'args' => [
                            'tool' => 'refresh_files',
                        ]
                    ]
                ]
            ],
        ];

        echo '<table class="wp-list-table widefat fixed striped" style="width: 100%;">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Description</th>';
        echo '<th>Action</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Generate rows for each action
        foreach ($actions as $action) {
            echo '<tr>';
            echo '<td>' . esc_html($action['description']) . '</td>';
            echo '<td>';
            foreach ($action['buttons'] as $button) {
                // echo ' <a href="' . esc_url($this->generateTabUrl('tools', $button['args'])) . '" class="button">' . esc_html($button['label'] ?? 'Run') . '</a>';
                if (isset($button['confirm'])) {
                    echo ' <a href="' . esc_url($this->generateTabUrl('tools', $button['args'])) . '" class="button" onclick="return confirm(\'' . esc_js($button['confirm']) . '\');">' . esc_html($button['label'] ?? 'Run') . '</a>';
                } else {
                    echo ' <a href="' . esc_url($this->generateTabUrl('tools', $button['args'])) . '" class="button">' . esc_html($button['label'] ?? 'Run') . '</a>';
                }
            }
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    public function adminRunTools()
    {
        switch ($_GET['tool'] ?? null) {
            case 'delete_all_vehicles':
                $this->adminVehiclesDelete();
                break;

            case 'refresh_files':
                $this->adminFilesRefresh();
                break;
        }
    }

    public function adminFilesGetHeaders()
    {
        if (!isset($this->library)) {
            $this->library = $this->Files->getAll();
        }
        echo '<h3>Headers</h3>';
        echo '<textarea style="width: 100%; height: 200px;">';

        // output first row of all files
        foreach ($this->library as $key => $file) {
            $file_path = $this->library[$key];
            $file_handle = fopen($file_path, 'r');
            $file_data = fgetcsv($file_handle);
            fclose($file_handle);

            // base file name
            $file_info = pathinfo($file_path);
            echo $file_info['basename'] . PHP_EOL;
            echo implode(',', $file_data) . PHP_EOL;
            echo PHP_EOL;
        }
        echo '</textarea>';
    }

    public function adminFilesRefresh()
    {
        $library = $this->Files->getAll(true);
        if ($library) {
            $this->adminNotice('Files refreshed.', 'notice-success');
        } else {
            $this->adminNotice('Error refreshing files.', 'notice-error');
        }
    }

    public function adminVehiclesDelete()
    {
        $vehicles = get_posts(['post_type' => 'vehicle', 'posts_per_page' => -1]);
        foreach ($vehicles as $vehicle) {
            wp_delete_post($vehicle->ID, true);
        }

        if (count(get_posts(['post_type' => 'vehicle', 'posts_per_page' => -1])) === 0) {
            // add this to admin notice
            $out = '✔︎ All vehicles deleted.';
            $notice = 'notice-success';
        } else {
            $out = '✘ Error deleting vehicles.';
            $notice = 'notice-error';
        }

        $this->adminNotice($out, $notice);
    }
}
