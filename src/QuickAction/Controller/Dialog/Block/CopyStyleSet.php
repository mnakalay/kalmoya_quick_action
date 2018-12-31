<?php
namespace Kalmoya\QuickAction\Controller\Dialog\Block;

use Concrete\Core\View\View;
use Concrete\Core\Block\Block;
use Concrete\Core\View\DialogView;
use Concrete\Controller\Backend\UserInterface\Block as BackendInterfaceBlockController;
use Concrete\Core\Support\Facade\Database;

class CopyStyleSet extends BackendInterfaceBlockController
{
    protected $viewPath = '/dialogs/block/copystyleset';

    protected $helpers = [
        'form',
    ];

    public function view()
    {
        $issID = null;
        $preset = null;

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
                    if ($issID) {
                        $db = Database::connection();

                        $preset = $db->GetOne(
                            'SELECT pssPresetName FROM StyleCustomizerInlineStylePresets WHERE issID = ?',
                            [$issID]
                        );
                    }
                }
            }
        }

        $this->set('bID', $bID);
        $this->set('issID', $issID);
        $this->set('preset', $preset);
        $this->set('dialogName', 'block-copy-style');

        $this->view = new DialogView('dialogs/block/copystyleset');
        $this->view->setPackageHandle('kalmoya_quick_action');
        $this->view->setController($this);
    }

    public function submit()
    {
        if ($this->validateAction()) {
            /** @var Block $block */
            $block = $this->getBlockToEdit();
            $preset = $this->getRequest()->get('preset');
            $issID = $this->getRequest()->get('issID');
            $isError = true;

            if (!empty($issID) && (int) $issID == $issID) {
                $db = Database::connection();

                $existingPreset = $db->GetOne(
                    'SELECT pssPresetName FROM StyleCustomizerInlineStylePresets WHERE issID = ?',
                    [$issID]
                );

                if ($existingPreset) {
                    if ($preset) {
                        $db->executeQuery(
                            'UPDATE StyleCustomizerInlineStylePresets SET pssPresetName = ? WHERE pssPresetName = ? AND issID = ?',
                            [$preset, $existingPreset, $issID]
                        );

                        $message = t("Your design preset was updated.");
                    } else {
                        $db->executeQuery(
                            'DELETE FROM StyleCustomizerInlineStylePresets WHERE pssPresetName = ? AND issID = ?',
                            [$existingPreset, $issID]
                        );
                        $isError = false;

                        $message = t("Your design preset was deleted.");
                    }
                } else {
                    if ($preset) {
                        $db->executeQuery(
                            'INSERT INTO StyleCustomizerInlineStylePresets (pssPresetName, issID) VALUES (?,?)',
                            [$preset, $issID]
                        );
                        $isError = false;
                        $message = t("Your design preset was created.");
                    } else {
                        $message = t("Your didn't supply a preset name so nothing was saved.");
                    }
                }
            } else {
                $message = t("This block is not using any custom design.");
            }

            $pr = $this->getEditResponse($block);
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

        return true == $config->get('concrete.design.enable_custom');
    }
}
