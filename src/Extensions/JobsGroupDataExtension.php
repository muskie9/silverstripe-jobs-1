<?php

namespace Dynamic\Jobs\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Group;

/**
 * Class JobsGroupDataExtension
 * @package Dynamic\Jobs\Extensions
 */
class JobsGroupDataExtension extends DataExtension
{
    /**
     *
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        if (!Group::get()->filter('Code', 'application-recipients')) {
            $group = Group::create();
            $group->Title = 'Application Recipients';
            $group->Code = 'application-recipients';
            $group->write();
        }
    }
}