<?php

namespace Dynamic\Jobs\Model;

use \Page;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\File;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\ValidationResult;

/**
 * Class JobCollection
 * @package Dynamic\Jobs\Model
 */
class JobCollection extends Page
{
    /**
     * @var string
     */
    private static $singular_name = "Job Collection";

    /**
     * @var string
     */
    private static $plural_name = "Job Collection";

    /**
     * @var string
     */
    private static $description = 'Display a list of available jobs';

    /**
     * @var array
     */
    private static $db = [
        'Message' => 'HTMLText',
        'FromAddress' => 'Varchar(255)',
        'EmailSubject' => 'Varchar(255)',
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Application' => File::class,
    ];

    /**
     * @var array
     */
    private static $owns = [
        'Application',
    ];

    /**
     * @var string
     */
    private static $default_child = Job::class;

    /**
     * @var array
     */
    private static $allowed_children = [
        Job::class,
    ];

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $app = new UploadField('Application', 'Application Form');
        $app->allowedExtensions = ['pdf', 'PDF'];
        $fields->addFieldToTab('Root.ApplicationFile', $app);

        $fields->addFieldsToTab('Root.Configuration', [
            EmailField::create('FromAddress', 'Submission From Address'),
            TextField::create('EmailSubject', 'Submission Email Subject Line'),
            HTMLEditorField::create('Message', 'Submission Message'),
        ]);

        return $fields;
    }

    /**
     * @return ValidationResult
     */
    public function validate()
    {
        $result = parent::validate();

        return $result;
    }

    /**
     * @return DataList
     */
    public function getPostedJobs()
    {
        $now = DBDatetime::now();

        $jobs = Job::get()
            ->filter([
                'PostDate:LessThanOrEqual' => $now,
                'EndPostDate:GreaterThanOrEqual' => $now,
            ])
            ->sort('PostDate DESC');

        return $jobs;
    }
}
