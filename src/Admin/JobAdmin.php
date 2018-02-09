<?php

namespace Dynamic\Jobs\Admin;

use Dynamic\Jobs\Model\JobCategory;
use Dynamic\Jobs\Model\JobSubmission;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;

/**
 * Class JobAdmin
 * @package Dynamic\Jobs\Admin
 */
class JobAdmin extends ModelAdmin
{
    /**
     * @var array
     */
    private static $managed_models = [
        JobSubmission::class => [
            'title' => 'Application Submissions',
        ],
        JobCategory::class,
    ];

    /**
     * @var string
     */
    private static $url_segment = 'jobs';

    /**
     * @var string
     */
    private static $menu_title = 'Jobs';

    /**
     * @param null $id
     * @param null $fields
     * @return \SilverStripe\Forms\Form
     */
    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        if ($field = $form->Fields()->dataFieldByName($this->sanitiseClassName(JobSubmission::class))) {
            $field->setConfig($config = GridFieldConfig_RecordViewer::create());
        }

        return $form;
    }
}
