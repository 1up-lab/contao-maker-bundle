<?= "<?php\n"; ?>

declare(strict_types=1);

namespace App\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;

/**
* @Hook("<?= $hook; ?>")
*/
class <?= $class_name; ?>
{
    <?= $signature; ?>
    {
        // Do something …
    }
}
