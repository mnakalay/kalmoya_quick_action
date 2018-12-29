<?php
namespace Kalmoya\QuickAction\Controller;

use Concrete\Core\Page\Page;
use Concrete\Core\View\View;
use Concrete\Core\Block\Block;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Application\Application;
use Concrete\Core\Controller\Controller;
use Concrete\Core\User\User;
use Concrete\Core\Area\Area;
use Concrete\Core\Page\Stack\Stack;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Permission\Checker as PermissionsChecker;
use Concrete\Core\Support\Facade\Events;
use Concrete\Core\Block\Events\BlockDelete;

class QuickAction extends Controller
{
    protected $app;
    protected $page;
    protected $area;

    public function __construct(Application $app)
    {
        $this->app = $app;
        parent::__construct();
    }

    public function delete()
    {
        $u = new User();
        if (!$u->isRegistered()
            || !$this->app->make('token')->validate('kalmoya/quickaction/delete')
        ) {
            die(t("Access Denied."));
        }

        $request = $this->request;
        $arHandle = $request->query->get('arHandle');
        if (!$arHandle) {
            $arHandle = $request->request->get('arHandle');
        }
        $bID = $request->query->get('bID');
        if (!$bID) {
            $bID = $request->request->get('bID');
        }
        $cID = $request->query->get('cID');
        if (!$cID) {
            $cID = $request->request->get('cID');
        }
        $page = Page::getByID($cID, 'RECENT');
        $this->page = $page;

        $area = Area::get($page, $arHandle);
        if (!is_object($area)) {
            throw new UserMessageException('Invalid Area');
        }
        $this->area = $area;
        if (!$area->isGlobalArea()) {
            $b = Block::getByID($bID, $page, $area);
        } else {
            $stack = Stack::getByName($arHandle);
            $sc = Page::getByID($stack->getCollectionID(), 'RECENT');
            $b = Block::getByID($bID, $sc, STACKS_AREA_NAME);
            if ($b) {
                $b->setBlockAreaObject($area); // set the original area object
            }
        }
        if (!$b) {
            throw new UserMessageException(t('Access Denied'));
        }

        $permissions = new PermissionsChecker($b);

        if ($permissions->canDeleteBlock()) {
            $pr = $this->getEditResponse($b);

            $b->deleteBlock();

            $event = new BlockDelete($b, $page);
            Events::dispatch('on_block_delete', $event);

            $b->getBlockCollectionObject()->rescanDisplayOrder($arHandle);

            $pr->setMessage(t('Block deleted successfully.'));
            $pr->outputJSON();
        }
    }

    protected function getEditResponse($b, $e = null)
    {
        $pr = new \Concrete\Core\Page\EditResponse();
        $pr->setPage($this->page);
        $pr->setAdditionalDataAttribute('aID', intval($this->area->getAreaID()));
        $pr->setAdditionalDataAttribute('arHandle', $this->area->getAreaHandle());
        $pr->setAdditionalDataAttribute('bID', intval($b->getBlockID()));
        if ($e) {
            $pr->setError($e);
        }

        return $pr;
    }
}
