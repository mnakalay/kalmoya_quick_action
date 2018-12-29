<?php
namespace Kalmoya\QuickAction\Block\Menu;

use Concrete\Core\Block\Block;
use Concrete\Core\Application\UserInterface\ContextMenu\Item\LinkItem;
use Concrete\Core\Application\UserInterface\ContextMenu\Item\DividerItem;
use Concrete\Core\Application\UserInterface\ContextMenu\ModifiableMenuInterface;
use Concrete\Core\Application\UserInterface\ContextMenu\Modifier\ModifierInterface;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Block\Menu\Menu;

class MenuModifier implements ModifierInterface
{
    protected $block;

    public function modifyMenu(ModifiableMenuInterface $menu)
    {
        $block = $menu->getBlock();

        if (BLOCK_HANDLE_SCRAPBOOK_PROXY === $block->getBlockTypeHandle()) {
            $block = Block::getByID($block->getController()->getOriginalBlockID());
        }

        $this->block = $block;

        if ($menu->getPermissions()->canDeleteBlock()
        ) {
            $this->addItemTo($menu);
        }
    }

    /**
     * @param Menu $menu
     */
    private function addItemTo($menu)
    {
        $app = Application::getFacadeApplication();
        $menu->addItem(new DividerItem());
        $menu->addItem(
            new LinkItem(
                'javascript:void(0)',
                t('Quick Delete'),
                [
                    'data-menu-action' => 'quick_delete_block',
                    'data-menu-href' => \URL::to('/ccm/system/dialogs/block/quickaction/delete'),
                    'data-token' => $app->make('token')->generate('kalmoya/quickaction/delete'),
                    'data-bID' => $this->block->getBlockID(),
                    'data-cID' => $menu->getPage()->getCollectionID(),
                    'data-arHandle' => $menu->getArea()->getAreaHandle(),
                ]
            )
        );
    }
}
