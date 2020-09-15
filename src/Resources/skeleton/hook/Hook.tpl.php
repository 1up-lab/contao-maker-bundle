<?= "<?php\n"; ?>

declare(strict_types=1);

namespace App\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
<?php foreach ($uses as $use): ?>
use <?= $use ?>;
<?php endforeach; ?>

/**
* @Hook("<?= $hook; ?>")
*/
class <?= $className; ?>

{
    <?= $signature; ?>

    {
        // Do something …
    }
}
