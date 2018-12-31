<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<div class="ccm-ui">
    <form method="post" data-dialog-form="<?php echo $dialogName; ?>" action="<?php echo $controller->action('submit'); ?>">
        <div class="settings-container">
            <?php
            if ($issID) {
                echo $form->label('preset', t("Give your preset a name"));
                echo $form->text('preset', $preset, ['required' => 'required']);
                echo $form->hidden('issID', $issID);
                if ($preset) {
                    ?>
                    <div class="alert alert-warning">
                    <?php echo t("%sThis block's custom styles are already saved as a preset under the name %s. If you modify the preset's name and save it will only update the existing preset and not create a new one.%s %sIf you leave the preset's name empty, it will delete the existing preset. %sIt will not affect blocks using this preset as the styles themselves will remain in place%s.%s", '<p>', '&ldquo;' . $preset . '&rdquo;', '</p>', '<p><strong>', '<br><u>', '</u>', '</strong></p>'); ?>
                    </div>
                    <?php
                }
            } else {
                ?>
                    <div class="alert alert-info">
                    <?php echo t("This block doesn't use any custom styles so there's nothing to copy."); ?>
                    </div>
                    <?php
            }

            ?>
        </div>

        <div class="dialog-buttons">
            <button class="btn btn-default pull-left" data-dialog-action="cancel"><?php echo t('Cancel'); ?></button>
            <?php
            if ($issID) {
                ?>
            <button type="button" data-dialog-action="submit" class="btn btn-primary pull-right"><?php echo t('Save'); ?></button>
            <?php
            }
                ?>
        </div>
    </form>
</div>