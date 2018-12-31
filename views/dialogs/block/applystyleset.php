<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<div class="ccm-ui">
    <form method="post" id="<?php echo $dialogName; ?>" data-dialog-form="<?php echo $dialogName; ?>" action="<?php echo $controller->action('submit'); ?>">
        <div class="settings-container">
            <?php
            if (count($presets)) {
                echo $form->label('preset', t("Select a preset to apply"));
                echo $form->select('presets', $presets, $currentPresetID);
                echo $form->hidden('issID', $issID);

                if ($issID) {
                    // this block already has some styles
                    if ($currentPresetID && $currentPresetName) {
                        // and it's a preset?>
                    <div class="alert alert-warning">
                    <?php echo t("%sThis block is already using the preset %s. If you select another one it will be applied.%s %sIf you select none, the block styles will be cleared.%s", '<p>', '&ldquo;' . $currentPresetName . '&rdquo;', '</p>', '<p><strong>', '</strong></p>'); ?>
                    </div>
                    <?php
                    } else {
                        // this block already has styles but not from a preset
                        ?>
                        <div class="alert alert-warning">
                        <?php echo t("%sThis block is already using some styles but not from a preset. If you select a preset it will be applied.%s %sIf you select none, the block styles will be cleared.%s", '<p>', '</p>', '<p><strong>', '</strong></p>'); ?>
                        </div>
                        <?php
                    }
                }
            } else {
                ?>
                    <div class="alert alert-info">
                    <?php echo t("You don't have any saved style presets to use yet."); ?>
                    </div>
                    <?php
            }

                ?>
        </div>

        <div class="dialog-buttons">
            <button class="btn btn-default pull-left" data-dialog-action="cancel"><?php echo t('Cancel'); ?></button>
            <?php
            if (count($presets)) {
                ?>
            <button type="button" data-dialog-action="submit" class="btn btn-primary pull-right"><?php echo t('Save'); ?></button>
            <?php
            }
        ?>
        </div>
    </form>
</div>
<script>
$(function() {
    // this is here to refresh the block after applying the styles
    // AjaxFormSubmitSuccess is a core C5 event
    ConcreteEvent.subscribe('AjaxFormSubmitSuccess', function(e, data) {
        if (data.form == "<?php echo $dialogName; ?>") {
            if (data.response.oldIssID) {
                $('head').find('style[data-style-set=' + data.response.oldIssID +'][data-block-style-block-id=' + data.response.bID +']').remove();
            }
            if (data.response.issID && data.response.css) {
                $('head').append(data.response.css);
                var editor = Concrete.getEditMode();
                var area = editor.getAreaByID(parseInt(data.response.aID));
                var block = area.getBlockByID(parseInt(data.response.bID));
                var elem = block.getElem();
                if (data.response.customStyleClass
                && !elem.hasClass(data.response.customStyleClass)
                && !elem.parents(data.response.customStyleClass).length
                ) {
                    elem.addClass(data.response.customStyleClass);
                }
            }
        }
    });
});
</script>