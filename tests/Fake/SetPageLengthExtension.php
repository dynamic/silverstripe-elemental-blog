<?php

namespace Dynamic\Elements\Blog\Tests\Fake;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Model\List\PaginatedList;

/**
 * Test only extension used to prove that the Overview block applies its own PageLength before the
 * `updatePaginatedList` extension point runs, so that an extension still has the final word
 *
 * Class SetPageLengthExtension
 *
 * @package Dynamic\Elements\Blog\Tests\Fake
 */
class SetPageLengthExtension extends Extension implements TestOnly
{
    /**
     * @param PaginatedList|null $paginatedList
     */
    public function updatePaginatedList(?PaginatedList $paginatedList): void
    {
        if ($paginatedList !== null) {
            $paginatedList->setPageLength(4);
        }
    }
}
