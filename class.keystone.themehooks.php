<?php if (!defined('APPLICATION')) {exit();}
/**
 * @author Isis Graziatto <isis.g@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

class KeystoneThemeHooks extends Gdn_Plugin {

    /**
     * Run once on enable.
     *
     * @return void
     */
    public function setup() {
        $this->structure();
    }

    /**
     * Run on utility/update.
     *
     * @return void
     */
    public function structure() {
        saveToConfig([
            'Garden.MobileTheme' => 'keystone',
            'Routes.DefaultController' => ['categories', 'Internal'],
            'Vanilla.Categories.Layout' => 'modern',
            'Vanilla.Discussions.Layout' => 'modern',
            'Badges.BadgesModule.Target' => 'AfterUserInfo',
        ]);
    }

    /**
     * Runs every page load
     *
     * @param Gdn_Controller $sender This could be any controller
     *
     * @return void
     */
    public function base_render_before($sender) {
        if (inSection('Dashboard')) {
            return;
        }
    }
}
