<?php if (!$append): ?>
<?= '<?php'; ?>


declare(strict_type=1);
<?php endif; ?>

$GLOBALS['TL_DCA']['tl_content']['palettes'] += [
    '<?= $element_name; ?>' => '
        {type_legend},type,headline;
        {expert_legend:hide},guests,cssID;
        {invisible_legend:hide},invisible,start,stop
    ',
];
