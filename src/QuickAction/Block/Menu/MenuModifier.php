<?php
namespace Kalmoya\QuickAction\Block\Menu;

use Concrete\Core\Block\Block;
use Concrete\Core\Application\UserInterface\ContextMenu\Item\LinkItem;
use Concrete\Core\Application\UserInterface\ContextMenu\Item\DividerItem;
use Concrete\Core\Application\UserInterface\ContextMenu\ModifiableMenuInterface;
use Concrete\Core\Application\UserInterface\ContextMenu\Modifier\ModifierInterface;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Block\Menu\Menu;

class MenuModifier implements ModifierInterface
{
    protected $block;
    protected $bID;

    public function modifyMenu(ModifiableMenuInterface $menu)
    {
        $block = $menu->getBlock();

        if (BLOCK_HANDLE_SCRAPBOOK_PROXY === $block->getBlockTypeHandle()) {
            $block = Block::getByID($block->getController()->getOriginalBlockID());
        }

        $this->block = $block;
        $this->bID = $block->getBlockID();

        $this->addItemTo($menu);
    }

    /**
     * @param Menu $menu
     */
    private function addItemTo($menu)
    {
        $app = Application::getFacadeApplication();

        /** @var ResolverManagerInterface $resolver URL Resolver */
        $resolver = $app->make(ResolverManagerInterface::class);

        $permissions = $menu->getPermissions();

        if ($permissions->canDeleteBlock()) {
            $menu->addItem(new DividerItem());
            $menu->addItem(
                new LinkItem(
                    'javascript:void(0)',
                    t('Quick Delete'),
                    [
                        'data-menu-action' => 'quick_delete_block',
                        'data-menu-href' => $resolver->resolve(['/ccm/system/dialogs/block/quickaction/delete']),
                        'data-token' => $app->make('token')->generate('kalmoya/quickaction/delete'),
                        'data-bID' => $this->block->getBlockID(),
                        'data-cID' => $menu->getPage()->getCollectionID(),
                        'data-arHandle' => $menu->getArea()->getAreaHandle(),
                    ]
                )
            );
        }

        $config = $app->make('config');

        if ($permissions->canEditBlock()
            && $this->block->isEditable()
            && $config->get('concrete.design.enable_custom')
        ) {
            $menu->addItem(new DividerItem());

            $menu->addItem(
                new LinkItem(
                    'javascript:void(0)',
                    t('Save Design as Preset'),
                    [
                        'data-menu-action' => 'block_dialog',
                        'data-menu-href' => $resolver->resolve(['/ccm/system/dialogs/block/quickaction/copystyleset']),
                        'dialog-title' => t("Copy this block's custom design"),
                        'dialog-width' => 600,
                        'dialog-height' => 400,
                    ]
                )
            );

            if ($permissions->canEditBlockDesign()) {
                $menu->addItem(
                    new LinkItem(
                        'javascript:void(0)',
                        t('Apply Design Preset'),
                        [
                            'data-menu-action' => 'block_dialog',
                            'data-menu-href' => $resolver->resolve(['/ccm/system/dialogs/block/quickaction/applystyleset']),
                            'dialog-title' => t("Apply custom design preset"),
                            'dialog-width' => 600,
                            'dialog-height' => 400,
                        ]
                    )
                );
            }
        }
    }
}
