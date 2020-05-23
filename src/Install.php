<?php

declare(strict_types=1);

namespace OpenSumsWpPlugin;

class Install {

    /**
     * Called when 'activate plugin' is selected in the WP dashboard.
     */
    public static function activate(): void {
        Config::instance()
            ->activate()
            ->set('activated', true);
    }

    /**
     * Called when 'deactivate plugin' is selected in the WP dashboard.
     */
    public static function deactivate(): void {
        Config::instance()
            ->set('activated', [false]);
    }

    /**
     * Called when 'uninstall plugin' is selected in the WP dashboard.
     */
    public static function uninstall(): void {
        Config::instance()
            ->uninstall();
    }
}
