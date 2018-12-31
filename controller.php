<?php
namespace Concrete\Package\KalmoyaQuickAction;

defined('C5_EXECUTE') or die('Access denied.');

use Concrete\Core\Page\Page;
use Concrete\Core\View\View;
use Concrete\Core\Package\Package;
use Kalmoya\QuickAction\Provider\QuickActionServiceProvider;
use Concrete\Core\Support\Facade\Events;

class Controller extends Package
{
    protected $pkgHandle = 'kalmoya_quick_action';
    protected $appVersionRequired = '8.1.0';
    protected $pkgVersion = '0.9.1';
    protected $pkgAutoloaderRegistries = [
        'src/QuickAction' => '\Kalmoya\QuickAction',
    ];
    protected $pkg;

    public function getPackageDescription()
    {
        return t("A set of quick actions when working with blocks in edit mode %s Developed by Nour Akalay @ %sKALMOYA - bespoke Concrete5 development%s", '<br /><span style="font-size:11px;">', '<a target="_blank" href="https://kalmoya.com">', '</a></span>');
    }

    public function getPackageName()
    {
        return t("Block Quick Actions");
    }

    public function on_start()
    {
        $provider = $this->app->make(QuickActionServiceProvider::class);
        $provider->register($this);

        Events::addListener(
            'on_start',
            function ($event) {
                $provider = $this->app->make(QuickActionServiceProvider::class);
                $provider->registerAssets($this);

                return $event;
            }
        );
    }
}
