<?php if (!$append): ?>
<?= '<?php'; ?>


declare(strict_type=1);
<?php endif; ?>

$GLOBALS['TL_DCA']['tl_module']['palettes'] += [
    '<?= $element_name; ?>' => '
        {title_legend},name,type;
    ',
];
