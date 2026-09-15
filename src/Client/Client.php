<?php

namespace SwiftSearchAlgolia\Client;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Client
 *
 * Lightweight Algolia REST API Client using wp_remote_request.
 * Avoids heavy Composer or Guzzle dependency conflicts.
 */
class Client
{
    /**
     * Algolia Application ID.
     *
     * @var string
     */
    protected $app_id;

    /**
     * Algolia Admin API Key (server-side operations only).
     *
     * @var string
     */
    protected $admin_api_key;

    /**
     * Algolia Search-Only API Key (frontend queries).
     *
     * @var string
     */
    protected $search_api_key;

    /**
     * Index prefix (e.g. "wp_").
     *
     * @var string
     */
    protected $index_prefix = '';

    /**
     * Last recorded error message.
     *
     * @var string
     */
    protected $last_error = '';

    /**
     * Constructor.
     *
     * @param array|null $config Configuration array.
     */
    public function __construct($config = null)
    {
        if (!$config) {
            $config = get_option('swift_search_algolia_settings', array());
        }

        $this->app_id         = trim($config['app_id'] ?? ($config['application_id'] ?? ''));
        $this->admin_api_key  = trim($config['admin_api_key'] ?? ($config['api_key'] ?? ''));
        $this->search_api_key = trim($config['search_api_key'] ?? ($config['search_key'] ?? ''));
        $this->index_prefix   = trim($config['index_prefix'] ?? '');
    }

    /**
     * Get read base URL (uses Distributed Search Network).
     *
     * @return string
     */
    protected function get_read_base_url()
    {
        if (empty($this->app_id)) {
            return '';
        }
        return 'https://' . strtolower($this->app_id) . '-dsn.algolia.net';
    }

    /**
     * Get write base URL.
     *
     * @return string
     */
    protected function get_write_base_url()
    {
        if (empty($this->app_id)) {
            return '';
        }
        return 'https://' . strtolower($this->app_id) . '.algolia.net';
    }

    /**
     * Format full index name with optional prefix.
     *
     * @param string $index
     * @return string
     */
    public function format_index_name($index)
    {
        if (!empty($this->index_prefix) && strpos($index, $this->index_prefix) !== 0) {
            return $this->index_prefix . $index;
        }
        return $index;
    }

    /**
     * Get Last Error.
     *
     * @return string
     */
    public function get_last_error()
    {
        return $this->last_error;
    }

    /**
     * Make an HTTP request to the Algolia REST API.
     *
     * @param string $endpoint Endpoint path (e.g. "/1/indexes").
     * @param string $method   HTTP Method ('GET', 'POST', 'PUT', 'DELETE').
     * @param array  $body     Request body data.
     * @param bool   $is_write Whether this is a write operation.
     * @return array|bool Decoded JSON response or false on failure.
     */
    public function request($endpoint, $method = 'GET', $body = array(), $is_write = false)
    {
        $this->last_error = '';

        if (empty($this->app_id) || empty($this->admin_api_key)) {
            $this->last_error = __('Algolia Application ID and Admin API Key are required.', 'swiftsearch-for-algolia');
            return false;
        }

        $base_url = $is_write ? $this->get_write_base_url() : $this->get_read_base_url();
        $url      = $base_url . $endpoint;

        $args = array(
            'method'  => $method,
            'headers' => array(
                'X-Algolia-Application-Id' => $this->app_id,
                'X-Algolia-API-Key'        => $this->admin_api_key,
                'Content-Type'             => 'application/json',
                'User-Agent'               => 'SwiftSearch-for-Algolia/1.0.0; WordPress/' . get_bloginfo('version'),
            ),
            'timeout' => 15,
        );

        if (!empty($body)) {
            $args['body'] = wp_json_encode($body);
        }

        $response = wp_remote_request($url, $args);

        // Fallback for write operations if primary host fails
        if (is_wp_error($response) && $is_write) {
            $fallback_url = 'https://' . strtolower($this->app_id) . '-1.algolianet.com' . $endpoint;
            $response     = wp_remote_request($fallback_url, $args);
        }

        if (is_wp_error($response)) {
            $this->last_error = $response->get_error_message();
            return false;
        }

        $code          = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $json          = json_decode($response_body, true);

        if ($code < 200 || $code >= 300) {
            $msg = "HTTP $code";
            if (is_array($json) && isset($json['message'])) {
                $msg .= ': ' . $json['message'];
            } elseif (!empty($response_body)) {
                $msg .= ': ' . substr(strip_tags($response_body), 0, 200);
            }
            $this->last_error = $msg;
            return false;
        }

        return is_array($json) ? $json : array();
    }

    /**
     * Test connection to Algolia.
     *
     * @return bool
     */
    public function test_connection()
    {
        $response = $this->request('/1/indexes', 'GET');
        if ($response === false) {
            return false;
        }
        return isset($response['items']) || is_array($response);
    }

    /**
     * List all indices and their metadata.
     *
     * @return array|bool
     */
    public function list_indices()
    {
        $response = $this->request('/1/indexes', 'GET');
        if ($response === false) {
            return false;
        }
        return $response['items'] ?? array();
    }

    /**
     * Get aggregate statistics across indices.
     *
     * @return array
     */
    public function get_stats()
    {
        $indices = $this->list_indices();
        if (!is_array($indices)) {
            return array('num_documents' => 0, 'indices' => array());
        }

        $count   = 0;
        $details = array();
        foreach ($indices as $item) {
            $entries = (int) ($item['entries'] ?? 0);
            $count  += $entries;
            $details[$item['name']] = array(
                'entries'    => $entries,
                'data_size'  => $item['dataSize'] ?? 0,
                'updated_at' => $item['updatedAt'] ?? '',
            );
        }

        return array(
            'num_documents' => $count,
            'indices'       => $details,
        );
    }

    /**
     * Set index settings.
     *
     * @param string $index    Index name.
     * @param array  $settings Settings payload.
     * @param bool   $forward_to_replicas Whether to propagate settings to replicas.
     * @return array|bool
     */
    public function set_settings($index, $settings, $forward_to_replicas = false)
    {
        $index_name = $this->format_index_name($index);
        $endpoint   = '/1/indexes/' . rawurlencode($index_name) . '/settings';
        if ($forward_to_replicas) {
            $endpoint .= '?forwardToReplicas=true';
        }

        return $this->request($endpoint, 'PUT', $settings, true);
    }

    /**
     * Get index settings.
     *
     * @param string $index Index name.
     * @return array|bool
     */
    public function get_settings($index)
    {
        $index_name = $this->format_index_name($index);
        return $this->request('/1/indexes/' . rawurlencode($index_name) . '/settings', 'GET');
    }

    /**
     * Save a batch of objects into an Algolia index.
     *
     * @param string $index     Index name.
     * @param array  $documents Array of associative arrays (each MUST have 'objectID').
     * @param string $action    'addObject' or 'updateObject' (partial update).
     * @return array|bool
     */
    public function save_objects($index, $documents, $action = 'addObject')
    {
        if (empty($documents)) {
            return array('success' => true, 'num_items' => 0);
        }

        $index_name = $this->format_index_name($index);
        $requests   = array();

        foreach ($documents as $doc) {
            if (!isset($doc['objectID'])) {
                continue;
            }
            $requests[] = array(
                'action' => $action,
                'body'   => $doc,
            );
        }

        if (empty($requests)) {
            return array('success' => true, 'num_items' => 0);
        }

        $endpoint = '/1/indexes/' . rawurlencode($index_name) . '/batch';
        $response = $this->request($endpoint, 'POST', array('requests' => $requests), true);

        if ($response === false) {
            return false;
        }

        return array(
            'success'   => true,
            'taskID'    => $response['taskID'] ?? 0,
            'num_items' => count($requests),
        );
    }

    /**
     * Delete objects from an index by their objectIDs.
     *
     * @param string $index      Index name.
     * @param array  $object_ids Array of string objectIDs.
     * @return array|bool
     */
    public function delete_objects($index, $object_ids)
    {
        if (empty($object_ids)) {
            return array('success' => true, 'num_items' => 0);
        }

        $index_name = $this->format_index_name($index);
        $requests   = array();

        foreach ((array) $object_ids as $id) {
            $requests[] = array(
                'action' => 'deleteObject',
                'body'   => array('objectID' => (string) $id),
            );
        }

        $endpoint = '/1/indexes/' . rawurlencode($index_name) . '/batch';
        $response = $this->request($endpoint, 'POST', array('requests' => $requests), true);

        if ($response === false) {
            return false;
        }

        return array(
            'success'   => true,
            'taskID'    => $response['taskID'] ?? 0,
            'num_items' => count($requests),
        );
    }

    /**
     * Clear all objects from an index.
     *
     * @param string $index Index name.
     * @return array|bool
     */
    public function clear_objects($index)
    {
        $index_name = $this->format_index_name($index);
        return $this->request('/1/indexes/' . rawurlencode($index_name) . '/clear', 'POST', array(), true);
    }

    /**
     * Delete an entire index.
     *
     * @param string $index Index name.
     * @return array|bool
     */
    public function delete_index($index)
    {
        $index_name = $this->format_index_name($index);
        return $this->request('/1/indexes/' . rawurlencode($index_name), 'DELETE', array(), true);
    }

    /**
     * Save synonyms to an index.
     *
     * @param string $index    Index name.
     * @param array  $synonyms Array of synonym objects according to Algolia format.
     * @param bool   $forward_to_replicas
     * @param bool   $replace_existing
     * @return array|bool
     */
    public function save_synonyms($index, $synonyms, $forward_to_replicas = true, $replace_existing = true)
    {
        $index_name = $this->format_index_name($index);
        $endpoint   = '/1/indexes/' . rawurlencode($index_name) . '/synonyms/batch';

        $query_params = array();
        if ($forward_to_replicas) {
            $query_params['forwardToReplicas'] = 'true';
        }
        if ($replace_existing) {
            $query_params['replaceExistingSynonyms'] = 'true';
        }

        if (!empty($query_params)) {
            $endpoint .= '?' . http_build_query($query_params);
        }

        return $this->request($endpoint, 'POST', $synonyms, true);
    }

    /**
     * Clear all synonyms on an index.
     *
     * @param string $index Index name.
     * @return array|bool
     */
    public function clear_synonyms($index)
    {
        $index_name = $this->format_index_name($index);
        return $this->request('/1/indexes/' . rawurlencode($index_name) . '/synonyms/clear', 'POST', array(), true);
    }
}
