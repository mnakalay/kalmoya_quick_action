<?php
namespace Kalmoya\QuickAction\Provider;

use Concrete\Core\Asset\Asset;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Application\Application;
use Concrete\Core\Routing\RouterInterface;
use Kalmoya\QuickAction\Block\Menu\MenuModifier;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Block\Menu\Manager as MenuManager;
use Concrete\Core\Page\Page;
use Concrete\Core\View\View;

class QuickActionServiceProvider
{
    /** @var Application */
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function register()
    {
        $this->registerBindings();
        // $this->registerAssets($pkg);
        $this->registerRoutes();
    }

    private function registerBindings()
    {
        $this->app->extend(
            MenuManager::class, function ($manager) {
                $manager->addMenuModifier(new MenuModifier());

                return $manager;
            }
        );
    }

    private function registerRoutes()
    {
        /** @var RouterInterface $router */
        $router = $this->app->make(RouterInterface::class);

        $router->registerMultiple(
            [
                '/ccm/system/dialogs/block/quickaction/delete' => [
                    '\Kalmoya\QuickAction\Controller\QuickAction::delete',
                ],
                '/ccm/system/dialogs/block/quickaction/copystyleset' => [
                    '\Kalmoya\QuickAction\Controller\Dialog\Block\CopyStyleSet::view',
                ],
                '/ccm/system/dialogs/block/quickaction/copystyleset/submit' => [
                    '\Kalmoya\QuickAction\Controller\Dialog\Block\CopyStyleSet::submit',
                ],
                '/ccm/system/dialogs/block/quickaction/applystyleset' => [
                    '\Kalmoya\QuickAction\Controller\Dialog\Block\ApplyStyleSet::view',
                ],
                '/ccm/system/dialogs/block/quickaction/applystyleset/submit' => [
                    '\Kalmoya\QuickAction\Controller\Dialog\Block\ApplyStyleSet::submit',
                ],
            ]
        );
    }

    public function registerAssets($pkg)
    {
        $al = AssetList::getInstance();

        $al->register(
            'javascript',
            'KalmoyaQuickAction',
            'js/kamoya-quick-action.js',
            [
                'version' => '1.0',
                'position' => Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $pkg
        );

        $page = Page::getCurrentPage();
        $view = View::getInstance();

        if (is_object($page)
            && is_object($view)
            && !$page->isError()
            && !$page->isAdminArea()
            && $page->isEditMode()
        ) {
            $view->requireAsset('javascript', 'KalmoyaQuickAction');
        }
    }
}
