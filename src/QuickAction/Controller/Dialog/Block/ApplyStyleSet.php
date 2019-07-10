<?php
namespace Kalmoya\QuickAction\Controller\Dialog\Block;

use Concrete\Core\View\View;
use Concrete\Core\Block\Block;
use Concrete\Core\View\DialogView;
use Concrete\Controller\Backend\UserInterface\Block as BackendInterfaceBlockController;
use Concrete\Core\Support\Facade\Database;
use Concrete\Core\StyleCustomizer\Inline\StyleSet;
use Concrete\Core\Block\CustomStyle;

class ApplyStyleSet extends BackendInterfaceBlockController
{
    protected $viewPath = '/dialogs/block/applystyleset';

    protected $helpers = [
        'form',
    ];

    public function view()
    {
        $presets = [];
        $issID = null;
        $currentPresetName = null;
        $currentPresetID = null;

        if (is_object($this->block)) {
            if (BLOCK_HANDLE_SCRAPBOOK_PROXY === $this->block->getBlockTypeHandle()) {
                $bID = $this->block->getController()->getOriginalBlockID(); // block from clipboard
            } else {
                $bID = $this->block->getBlockID();
            }

            $style = $this->block->getCustomStyle();
            if (is_object($style)) {
                $styleSet = $style->getStyleSet();
                if (is_object($styleSet)) {
                    $issID = $styleSet->getID();
                }
            }

            $db = Database::connection();
            $r = $db->executeQuery('SELECT * FROM StyleCustomizerInlineStylePresets ORDER BY pssPresetName ASC');

            $presets[] = t("No style preset (clear existing styles)");
            while ($row = $r->fetch()) {
                $presets[$row['pssPresetID']] = $row['pssPresetName'];
                if ($issID && (int) $issID === (int) $row['issID']) {
                    $currentPresetName = $row['pssPresetName'];
                    $currentPresetID = $row['pssPresetID'];
                }
            }
        }

        $this->set('bID', $bID);
        $this->set('issID', $issID);
        $this->set('presets', $presets);
        $this->set('currentPresetName', $currentPresetName);
        $this->set('currentPresetID', $currentPresetID);
        $this->set('dialogName', 'block-apply-style');

        $this->view = new DialogView('dialogs/block/applystyleset');
        $this->view->setPackageHandle('kalmoya_quick_action');
        $this->view->setController($this);
    }

    public function submit()
    {
        if ($this->validateAction()) {
            /** @var Block $block */
            $block = $this->getBlockToEdit();
            $presetID = $this->getRequest()->get('presets');
            $oldIssID = $this->getRequest()->get('issID');

            $isError = true;

            if (!empty($presetID) && (int) $presetID == $presetID) {
                $db = Database::connection();

                $issID = $db->GetOne(
                    'SELECT issID FROM StyleCustomizerInlineStylePresets WHERE pssPresetID = ?',
                    [$presetID]
                );

                $message = t("The preset you selected couldn't be found.");

                if ($issID) {
                    $styleSet = StyleSet::getByID($issID);
                    if ($styleSet) {
                        $block->setCustomStyleSet($styleSet);
                        $message = t("Your custom design was successfully applied to the block.");
                        $isError = false;
                    }
                }
            } else {
                $block->resetCustomStyle();
                $message = t("This block's design was successfully cleared.");
                $isError = false;
            }

            $pr = $this->getEditResponse($block);

            if ($oldIssID) {
                $pr->setAdditionalDataAttribute('oldIssID', $oldIssID);
            }

            if (is_object($styleSet)) {
                $pr->setAdditionalDataAttribute('issID', $styleSet->getID());
                if ($this->area->isGlobalArea()) {
                    $block->setBlockAreaObject($this->area); // We need this for CSS: https://github.com/concrete5/concrete5/issues/3135
                }
                $style = new CustomStyle($styleSet, $block, $this->page->getCollectionThemeObject());
                $css = $style->getCSS();
                if ('' !== $css) {
                    $pr->setAdditionalDataAttribute('css', $style->getStyleWrapper($css));
                }

                if (!$oldIssID) {
                    $pr->setAdditionalDataAttribute('customStyleClass', $style->getCustomStyleClass());
                }
            }

            $pr->setAdditionalDataAttribute('aID', $this->area->getAreaID());
            $pr->setAdditionalDataAttribute('bID', $block->getBlockID());

            $pr->setAdditionalDataAttribute('success', !$isError);
            if ($isError) {
                $pr->setError($message);
            } else {
                $pr->setMessage($message);
            }

            $pr->outputJSON();
        }
    }

    protected function canAccess()
    {
        $config = $this->app->make('config');

        return $this->permissions->canEditBlockDesign() && true == $config->get('concrete.design.enable_custom');
    }
}
