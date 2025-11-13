<?php
/**
 * Autoloader for plugin classes.
 *
 * @package CondoleanceRegister
 * @since 2.0.0
 */

declare(strict_types=1);

namespace CondoleanceRegister;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Autoload classes.
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function (string $class): void {
    // Project-specific namespace prefix.
    $prefix = 'CondoleanceRegister\\';

    // Base directory for the namespace prefix.
    $base_dir = CONDOLEANCE_REGISTER_PATH . 'includes/';

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get the relative class name.
    $relative_class = substr($class, $len);

    // Replace namespace separators with directory separators.
    $relative_class = str_replace('\\', '/', $relative_class);

    // Split into path parts.
    $parts = explode('/', $relative_class);

    // Convert the last part (class name) to kebab-case with class- prefix.
    $class_name = array_pop($parts);
    $class_file = 'class-' . strtolower(preg_replace('/([A-Z])/', '-$1', lcfirst($class_name)));
    $class_file = str_replace('--', '-', $class_file);

    // Convert directory names to lowercase kebab-case as well.
    $parts = array_map(function($part) {
        return strtolower(preg_replace('/([A-Z])/', '-$1', lcfirst($part)));
    }, $parts);

    // Remove any leading dashes and clean up double dashes.
    $parts = array_map(function($part) {
        return trim(str_replace('--', '-', $part), '-');
    }, $parts);

    // Rebuild the path.
    $parts[] = $class_file . '.php';
    $file = $base_dir . implode('/', $parts);

    // If the file exists, require it.
    if (file_exists($file)) {
        require_once $file;
    }
});
