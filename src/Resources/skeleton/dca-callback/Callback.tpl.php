<?= "<?php\n"; ?>

declare(strict_types=1);

namespace App\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;

/**
 * @Callback(table="<?= $table; ?>", target="<?= $target; ?>")
 */
class <?= $class_name; ?>

{
    <?= $signature; ?>

    {
        // Do something …
    }
}
