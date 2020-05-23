<?php

declare(strict_types=1);

namespace OpenSumsWpPlugin;

/**
 * Configuration for an OpenSumsWp plugin.
 *
 * Access configuration with these methods:
 * * `$config->set('key', $anyValue)` to store $anyValue.
 * * `$config->get('key')` - to get a single value.
 * * `$config->all()` - to get all values.
 * * `$config->all(true)` - to get all loaded values.
 * * `$config->has('key')` - check if a key is defined.
 *
 * Configuration is set up using
 * * `$config->activate()
 * * `$config->uninstall()
 * 
 * Other
 * * `$config->flush() - to save all changes (also called by __destruct).
 */
class Config {

    /** @var string[] Dirty entries. */
    protected $dirty = [];

    /** @var array[] Entry definitions. */
    protected $entries = [
        'activated' => [
            'persist' => 'wp-option',
            'default' => false,
        ],
    ];

    /** @var mixed[] Values for entries that have been loaded. */
    protected $values = [];

    /** @var mixed[] Values for entries that have been loaded. */
    protected $wpPrefix;

    protected static $instance = null;

    public static function instance(string $name = null, string $version = null): self {
        if (self::$instance === null) {
            self::$instance = new self($name, $version);
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    protected function __construct(string $name, string $version) {
        $this->set('pluginName', $name);
        $this->set('version', $version);
        $this->wpPrefix = $this->kebabCaseToSnakeCase($name) . '_';
    }

    /**
     * Destructor.
     */
    public function __destruct() {
        $this->flush();
    }

    /**
     * Magic getter.
     */
    public function __get($key) {
        return $this->get($key, null, true);
    }

    /**
     * Activate config for the plugin.
     *
     * @return self  Chainable
     */
    public function activate(): self {
        // Persist any entries that don't exist.
        foreach ($this->entries as $key => $entry) {
            $this->persistCreate($key, $entry['default'] ?? null);
        }
        return $this;
    }

    /**
     * Get the values of all entries, or all loaded entries.
     *
     * @param bool $loaded Iff true returns only loaded entries
     * @return mixed[] The entry values
     */
    public function all(bool $loaded = null): array {
        if (!$loaded) {
            throw new \Exception('Config->all() cannot yet return unloaded entries'); 
        }
        return $this->values;
    }

    /**
     * Flush all persistent entries.
     *
     * @return self  Chainable
     */
    public function flush(): self {
        foreach ($this->dirty as $key => $isDirty) {
            $this->persistUpdate($key, $this->values[$key]);
            unset($this->dirty[$key]);
        }
        return $this;
    }

    /**
     * 
     * Get the value of an entry.
     *
     * @param  string $key     The key
     * @param  mixed  $default Default value
     * @return mixed  The value or the default value if the entry does not exist
     */
    public function get($key, $default = null, $throw = null) {
        if (array_key_exists($key, $this->values)) {
            return $this->values[$key];
        }
        if (array_key_exists($key, $this->entries)) {
            $this->loadEntry($key);
            return $this->values[$key];
        }
        if ($throw) {
            throw new \Exception("Config key [${key}] does not exist");
        }
        return $default;
    }

    /**
     * Returns true iff the entry is defined.
     *
     * @param string $key The key
     *
     * @return bool true if the entry exists, false otherwise
     */
    public function has($key) {
        return array_key_exists($key, $this->entries);
    }

    /**
     * Returns entry keys.
     *
     * @param bool   $loaded Iff true returns only loaded entries.
     * @return array An array of parameter keys
     */
    public function keys(bool $loaded = null): array {
        return array_keys($loaded ? $this->values : $this->entries);
    }

    /**
     * Sets the value of an entry.
     *
     * @param string $key   The key
     * @param mixed  $value The value
     * @return self  Chainable
     */
    public function set(string $key, $value): self {
        if (!array_key_exists($key, $this->entries)) {
            $this->inMemoryCreate($key, $value, true);
            return $this;
        }
        $this->values[$key] = $value;
        $this->setDirty($key);
        return $this;
    }

    /**
     * Persist if not exists.
     */
    protected function inMemoryCreate(string $key, $value, bool $overwrite = null): void {
        if ($overwrite) {
            $this->entries[$key] = [];
        }
        $this->values[$key] = $value;
    }

    /**
     * Convert a string to snake case.
     */
    protected function kebabCaseToSnakeCase(string $key): string {
        return str_replace('-', '_', $key);
    }

    /**
     * Create persistence if it does not exist.
     */
    protected function persistCreate($key, $value): void {
        $entry = $this->entries[$key] ?? [];
        switch ($entry['persist'] ?? null) {
            case 'wp-option':
                $this->wpOptionCreate($key, $value);
                return;
            default:
                $this->inMemoryCreate($key, $value);
        }
    }

    /**
     * Delete a persisted value.
     */
    protected function persistDelete($key): void {
        $entry = $this->entries[$key] ?? [];
        switch ($entry['persist'] ?? null) {
            case 'wp-option':
                $this->wpOptionDelete($key);
                return;
        }
    }

    /**
     * Update persisted value.
     */
    protected function persistUpdate($key, $value): void {
        $entry = $this->entries[$key] ?? [];
        switch ($entry['persist'] ?? null) {
            case 'wp-option':
                $this->wpOptionUpdate($key, $value);
                return;
        }
    }

    /** Marks an entry as dirty. */
    protected function setDirty(string $key): void {
        $this->dirty[$key] = true;
    }

    /**
     * Delete all config for the plugin.
     *
     * @return self  Chainable
     */
    public function uninstall(): self {
        // Persist any entries that don't exist.
        foreach ($this->entries as $key => $entry) {
            $this->persistDelete($key);
        }
        return $this;
    }

    /** Create a wp_options entry without autoload. */
    protected function wpOptionCreate(string $name, $value) {
        \add_option("{$this->wpPrefix}$name", $value, null, false);
    }

    /** Update a wp_options entry. */
    public function wpOptionUpdate(string $name, $value) {
        \update_option("{$this->wpPrefix}$name", $value);
    }

    /** Update a wp_options entry. */
    public function wpOptionDelete(string $name) {
        \delete_option("{$this->wpPrefix}$name");
    }

    // === Deprecated after here ================================ Deprecated ===

    public function getWpSubOption(string $group, string $name, $default = null) {
        $prefixedGroup = "{$this->wpOptionsPrefix}$group";
        $current = \get_option($prefixedGroup);
        if (!is_array($current)) {
            return $default;
        }
        return array_key_exists($name, $current) ? $current[$name] : $default;
    }

    public function setWpSubOption(string $group, string $name, $value) {
        $prefixedGroup = "{$this->wpOptionsPrefix}$group";
        $current = \get_option($prefixedGroup);
        if (!is_array($current)) {
            $current = [];
        }
        $current[$name] = $value;
        \update_option($prefixedGroup, $value);
    }
}
