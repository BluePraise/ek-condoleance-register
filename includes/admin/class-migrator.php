<?php
/**
 * Data Migrator from Tahlil Plugin
 *
 * @package CondoleanceRegister
 * @since 2.0.0
 */

declare(strict_types=1);

namespace CondoleanceRegister\Admin;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles migration of data from old Tahlil plugin.
 *
 * @since 2.0.0
 */
class Migrator
{
    /**
     * Migrate all data from cpt_condolances to condoleance post type.
     *
     * @since 2.0.0
     * @return array<string, mixed> Migration result.
     */
    public function migrate(): array
    {
        global $wpdb;

        $result = [
            'success' => false,
            'migrated' => 0,
            'errors' => [],
        ];

        try {
            // Get all old condoleance posts.
            $old_posts = get_posts([
                'post_type' => 'cpt_condolances',
                'posts_per_page' => -1,
                'post_status' => 'any',
            ]);

            if (empty($old_posts)) {
                $result['success'] = true;
                return $result;
            }

            foreach ($old_posts as $old_post) {
                try {
                    $this->migrate_single_post($old_post);
                    $result['migrated']++;
                } catch (\Exception $e) {
                    $result['errors'][] = sprintf(
                        'Failed to migrate post %d: %s',
                        $old_post->ID,
                        $e->getMessage()
                    );
                    error_log('Condoleance Register Migration Error: ' . $e->getMessage());
                }
            }

            $result['success'] = true;
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            error_log('Condoleance Register Migration Error: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Migrate a single post.
     *
     * @since 2.0.0
     * @param \WP_Post $old_post The old post to migrate.
     * @return int The new post ID.
     * @throws \Exception If migration fails.
     */
    private function migrate_single_post(\WP_Post $old_post): int
    {
        // Update post type.
        $result = wp_update_post([
            'ID' => $old_post->ID,
            'post_type' => 'condoleance',
        ], true);

        if (is_wp_error($result)) {
            throw new \Exception($result->get_error_message());
        }

        // Migrate meta data.
        $this->migrate_meta_data($old_post->ID);

        // Migrate comments meta data.
        $this->migrate_comments_meta($old_post->ID);

        return $old_post->ID;
    }

    /**
     * Migrate post meta data.
     *
     * @since 2.0.0
     * @param int $post_id Post ID.
     * @return void
     */
    private function migrate_meta_data(int $post_id): void
    {
        // Meta field mapping: old_key => new_key.
        $meta_mapping = [
            'cmb_condalances_birthday' => 'condoleance_birth_date',
            'cmb_condalances_deathday' => 'condoleance_death_date',
            'cmb_condalances_photos' => 'condoleance_photos',
            'cmb_condalances_candles' => 'condoleance_candles_data',
        ];

        foreach ($meta_mapping as $old_key => $new_key) {
            $value = get_post_meta($post_id, $old_key, true);

            if ($value) {
                // Special handling for candles data.
                if ($old_key === 'cmb_condalances_candles') {
                    $value = $this->transform_candle_data($value);
                }

                // Add new meta.
                update_post_meta($post_id, $new_key, $value);

                // Keep old meta for backup (don't delete yet).
                update_post_meta($post_id, '_migrated_' . $old_key, $value);
            }
        }

        // Mark as migrated.
        update_post_meta($post_id, '_condoleance_migrated', time());
        update_post_meta($post_id, '_condoleance_migration_version', CONDOLEANCE_REGISTER_VERSION);
    }

    /**
     * Transform candle data to new format.
     *
     * @since 2.0.0
     * @param mixed $old_data Old candle data.
     * @return array<string, mixed> Transformed data.
     */
    private function transform_candle_data($old_data): array
    {
        if (!is_array($old_data)) {
            return ['count' => 0, 'users' => []];
        }

        $new_data = [
            'count' => $old_data['count'] ?? 0,
            'users' => [],
        ];

        // Transform authors array if exists.
        if (isset($old_data['authors']) && is_array($old_data['authors'])) {
            foreach ($old_data['authors'] as $author) {
                if (!is_array($author) || empty($author)) {
                    continue;
                }

                // Handle nested array structure from old plugin.
                // Check if it's a nested array with numeric index [0].
                if (isset($author[0]) && is_array($author[0])) {
                    $author_data = $author[0];
                } else {
                    $author_data = $author;
                }

                // Only add if we have valid data.
                if (isset($author_data['candle_name']) || isset($author_data['candle_date'])) {
                    $new_data['users'][] = [
                        'name' => $author_data['candle_name'] ?? '',
                        'date' => $author_data['candle_date'] ?? current_time('mysql'),
                        'ip' => '', // Old plugin didn't store IP.
                    ];
                }
            }
        }

        return $new_data;
    }

    /**
     * Migrate comment meta data.
     *
     * @since 2.0.0
     * @param int $post_id Post ID.
     * @return void
     */
    private function migrate_comments_meta(int $post_id): void
    {
        $comments = get_comments([
            'post_id' => $post_id,
            'status' => 'all',
        ]);

        foreach ($comments as $comment) {
            // Migrate comment meta if exists.
            $old_meta_keys = [
                'pmg_comment_attachment',
                'pmg_comment_type',
                'pmg_comment_youtube_id',
                'pmg_comment_music_name',
            ];

            foreach ($old_meta_keys as $old_key) {
                $value = get_comment_meta($comment->comment_ID, $old_key, true);

                if ($value) {
                    // Create new key name.
                    $new_key = str_replace('pmg_comment_', 'condoleance_comment_', $old_key);
                    update_comment_meta($comment->comment_ID, $new_key, $value);

                    // Keep old for backup.
                    update_comment_meta($comment->comment_ID, '_migrated_' . $old_key, $value);
                }
            }

            // Mark comment as migrated.
            update_comment_meta($comment->comment_ID, '_condoleance_migrated', time());
        }
    }

    /**
     * Rollback migration if needed.
     *
     * @since 2.0.0
     * @return bool Whether rollback was successful.
     */
    public function rollback(): bool
    {
        global $wpdb;

        try {
            // Find all migrated posts.
            $migrated_posts = get_posts([
                'post_type' => 'condoleance',
                'posts_per_page' => -1,
                'meta_key' => '_condoleance_migrated',
                'post_status' => 'any',
            ]);

            foreach ($migrated_posts as $post) {
                // Revert post type.
                wp_update_post([
                    'ID' => $post->ID,
                    'post_type' => 'cpt_condolances',
                ]);

                // Remove new meta and restore old meta.
                delete_post_meta($post->ID, 'condoleance_birth_date');
                delete_post_meta($post->ID, 'condoleance_death_date');
                delete_post_meta($post->ID, 'condoleance_photos');
                delete_post_meta($post->ID, 'condoleance_candles_data');
                delete_post_meta($post->ID, '_condoleance_migrated');
                delete_post_meta($post->ID, '_condoleance_migration_version');
            }

            return true;
        } catch (\Exception $e) {
            error_log('Condoleance Register Rollback Error: ' . $e->getMessage());
            return false;
        }
    }
}
