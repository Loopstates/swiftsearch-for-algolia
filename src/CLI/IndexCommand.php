<?php
namespace SwiftSearchAlgolia\CLI;

use SwiftSearchAlgolia\Engine\Indexer;
use SwiftSearchAlgolia\Engine\ConfigLoader;
use SwiftSearchAlgolia\Client\Client;
use WP_CLI;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * CLI Commands for SwiftSearch for Algolia.
 */
class IndexCommand
{
    /**
     * Run full indexing of all selected post types into Algolia.
     *
     * ## OPTIONS
     *
     * [--batch-size=<size>]
     * : Number of items to index in each batch. Default is 250.
     *
     * [--offset=<offset>]
     * : Skip N items before starting. Default is 0.
     *
     * [--limit=<limit>]
     * : Max number of items to index. Default is no limit.
     *
     * ## EXAMPLES
     *
     *     wp swift-search-algolia index --batch-size=500 --offset=1000 --limit=5000
     *
     * @alias index
     */
    public function index($args, $assoc_args)
    {
        $batch_size = isset($assoc_args['batch-size']) ? intval($assoc_args['batch-size']) : 250;
        if ($batch_size <= 0) {
            $batch_size = 250;
        }

        $offset_arg = isset($assoc_args['offset']) ? intval($assoc_args['offset']) : 0;
        if ($offset_arg < 0) {
            $offset_arg = 0;
        }

        $limit_arg = isset($assoc_args['limit']) ? intval($assoc_args['limit']) : -1;

        $config_loader = new ConfigLoader();
        $config        = $config_loader->get_config();

        $post_types = isset($config['indexed_post_types']) ? (array) $config['indexed_post_types'] : ConfigLoader::get_default_post_types();

        WP_CLI::line('Querying post IDs to index...');
        WP_CLI::line('Selected Post Types: ' . implode(', ', $post_types));

        $query_args = array(
            'post_type'        => $post_types,
            'post_status'      => 'publish',
            'fields'           => 'ids',
            'posts_per_page'   => -1,
            'suppress_filters' => true,
        );

        $query    = new \WP_Query($query_args);
        $post_ids = $query->posts;
        $total    = count($post_ids);

        if ($total === 0) {
            WP_CLI::success('No posts found to index.');
            return;
        }

        if ($offset_arg > 0 || $limit_arg > 0) {
            if ($limit_arg > 0) {
                $post_ids = array_slice($post_ids, $offset_arg, $limit_arg);
            } else {
                $post_ids = array_slice($post_ids, $offset_arg);
            }
            $total = count($post_ids);
            WP_CLI::line(sprintf('Applied filters: offset %d, limit %d. Remaining: %d posts', $offset_arg, $limit_arg, $total));
        }

        WP_CLI::line(sprintf('Found %d posts to index. Starting Algolia sync in batches of %d...', $total, $batch_size));

        // Configure index settings and replicas
        $indexer = new Indexer();
        WP_CLI::line('Configuring Algolia index settings and replicas...');
        $indexer->create_collection();

        $progress = \WP_CLI\Utils\make_progress_bar('Indexing progress', $total);

        $offset = 0;
        $client = new Client();

        update_option('swift_search_algolia_index_status', array(
            'active'          => true,
            'total'           => $total,
            'processed'       => 0,
            'last_sync_start' => time(),
            'message'         => 'CLI Sync in progress...'
        ));

        while ($offset < $total) {
            $batch_ids = array_slice($post_ids, $offset, $batch_size);
            $documents = array();

            foreach ($batch_ids as $id) {
                try {
                    $doc = $indexer->index_post($id, true);
                    if ($doc) {
                        $documents[] = $doc;
                    }
                } catch (\Exception $e) {
                    WP_CLI::warning(sprintf('Failed to build document for ID %d: %s', $id, $e->getMessage()));
                }
                $progress->tick();
            }

            if (!empty($documents)) {
                $result = $client->save_objects($indexer->get_base_index_name(), $documents);
                if ($result === false) {
                    WP_CLI::warning('Algolia API batch save error: ' . $client->get_last_error());
                }
            }

            $offset += count($batch_ids);

            // Memory cleanup
            global $wpdb;
            $wpdb->queries = array();
            foreach ($batch_ids as $id) {
                clean_post_cache($id);
                wp_cache_delete($id, 'post_meta');
            }

            update_option('swift_search_algolia_index_status', array(
                'active'          => true,
                'total'           => $total,
                'processed'       => min($offset, $total),
                'last_sync_start' => time(),
                'message'         => sprintf('CLI Indexed %d / %d', min($offset, $total), $total)
            ));
        }

        $progress->finish();

        update_option('swift_search_algolia_index_status', array(
            'active'                 => false,
            'total'                  => $total,
            'processed'              => $total,
            'message'                => 'Complete!',
            'last_updated'           => time(),
            'last_sync_completed_at' => time()
        ));

        WP_CLI::success(sprintf('Successfully indexed %d documents into Algolia.', $total));
    }

    /**
     * Reset and reconfigure Algolia indices and replicas.
     *
     * ## EXAMPLES
     *
     *     wp swift-search-algolia reset
     */
    public function reset($args, $assoc_args)
    {
        WP_CLI::confirm('Are you sure you want to clear and reconfigure all SwiftSearch indices in Algolia? This will delete the search index data.');

        $indexer = new Indexer();
        WP_CLI::line('Clearing Algolia indices...');
        $indexer->delete_collection();

        WP_CLI::line('Configuring Algolia settings and replicas...');
        $indexer->create_collection();
        WP_CLI::success('Reset completed successfully.');
    }

    /**
     * Check connection status and get index details.
     *
     * ## EXAMPLES
     *
     *     wp swift-search-algolia status
     */
    public function status($args, $assoc_args)
    {
        $client = new Client();
        if ($client->test_connection()) {
            WP_CLI::success('Connected to Algolia successfully.');

            $indices = $client->list_indices();
            if (is_array($indices) && !empty($indices)) {
                WP_CLI::line('Algolia Indices status:');
                foreach ($indices as $idx) {
                    $entries = isset($idx['entries']) ? (int) $idx['entries'] : 0;
                    $size_kb = round(($idx['dataSize'] ?? 0) / 1024, 2);
                    WP_CLI::line(sprintf(' - %s: %d records (%s KB)', $idx['name'], $entries, $size_kb));
                }
            } else {
                WP_CLI::line('No indices found in Algolia application.');
            }
        } else {
            WP_CLI::error('Failed to connect to Algolia: ' . $client->get_last_error());
        }
    }
}
