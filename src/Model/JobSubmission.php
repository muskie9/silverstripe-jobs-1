<?php

namespace Dynamic\Jobs\Model;

use Dynamic\Jobs\Forms\SimpleHtmlEditorField;
use SilverStripe\Assets\File;
use SilverStripe\Dev\Debug;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FileField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Security\Permission;

/**
 * Class JobSubmission
 * @package Dynamic\Jobs\Model
 */
class JobSubmission extends DataObject
{
    /**
     * @var string
     */
    private static $singular_name = 'Job Application';

    /**
     * @var string
     */
    private static $plural_name = 'Job Applications';

    /**
     * @var string
     */
    private static $description = 'Online job application allowing for a resume upload';

    /**
     * @var array
     */
    private static $db = [
        'FirstName' => 'Varchar(255)',
        'LastName' => 'Varchar(255)',
        'Email' => 'Varchar(255)',
        'Phone' => 'Varchar(255)',
        'Available' => 'Date',
        'Content' => 'HTMLText',
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Job' => Job::class,
        'Resume' => File::class,
    ];

    /**
     * @var string
     */
    private static $default_sort = 'Created DESC';

    /**
     * @var array
     */
    private static $summary_fields = [
        'Name' => 'Applicant',
        'Job.Title' => 'Job',
        'Created.Nice' => 'Date',
    ];

    /**
     * @var array
     */
    private static $searchable_fields = [
        'FirstName' => [
            'title' => 'First',
        ],
        'LastName' => [
            'title' => 'Last',
        ],
        'Job.ID' => [
            'title' => 'Job',
        ],
        'Email' => [
            'title' => 'Email',
        ],
        'Phone' => [
            'title' => 'Phone',
        ],
        'Content' => [
            'title' => 'Cover Letter',
        ],
    ];

    /**
     * @return string
     */
    public function getName()
    {
        if ($this->FirstName) {
            return $this->FirstName . ' ' . $this->LastName;
        } else {
            return 'No Name';
        }
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getName();
    }

    /**
     * @param null $params
     * @return FieldList
     */
    public function getFrontEndFields($params = null)
    {
        // Resume Upload
        $ResumeField = FileField::create('Resume')->setTitle('Resume');
        $ResumeField->getValidator()->setAllowedExtensions([
            'pdf',
            'doc',
            'docx',
        ]);
        $ResumeField->setFolderName('Uploads/Resumes');
        $ResumeField->setRelationAutoSetting(false);
        $ResumeField->setAttribute('required', true);

        $fields = FieldList::create(
            TextField::create('FirstName', 'First Name')
                ->setAttribute('required', true),
            TextField::create('LastName', 'Last Name')
                ->setAttribute('required', true),
            EmailField::create('Email')
                ->setAttribute('required', true),
            TextField::create('Phone')
                ->setAttribute('required', true),
            DateField::create('Available', 'Date Available'),
            $ResumeField,
            SimpleHtmlEditorField::create('Content', 'Cover Letter')
        );

        $this->extend('updateFrontEndFields', $fields);

        return $fields;
    }

    /**
     * @return RequiredFields
     */
    public function getRequiredFields()
    {
        return new RequiredFields([
            'FirstName',
            'LastName',
            'Email',
            'Phone',
            'Resume',
        ]);
    }

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'Available',
            'JobID',
            'Resume',
            'Email',
            'FirstName',
            'LastName',
            'Email',
            'Phone',
            'Content',
        ]);

        $fields->addFieldsToTab(
            'Root.Main',
            [
                TextField::create('JobTitle')
                    ->setValue(
                        DBField::create_field(
                            'HTMLText',
                            '<a href="' . $this->Job()->AbsoluteLink() . '" target="_blank">' . $this->Job()->Title . '</a>'
                        )
                    )
                    ->performReadonlyTransformation(),
                FieldGroup::create(
                    'Applicant',
                    TextField::create('FirstName')
                        ->setTitle('First Name')
                        ->performReadonlyTransformation(),
                    TextField::create('LastName')
                        ->setTitle('Last Name')
                        ->performReadonlyTransformation()
                ),
                FieldGroup::create(
                    'Applicant Contact Information',
                    TextField::create('EmailLink')
                        ->setValue(DBField::create_field('HTMLText',
                            '<a href="mailto:' . $this->Email . '">' . $this->Email . '</a>'))
                        ->setTitle('Email')
                        ->performReadonlyTransformation(),
                    TextField::create('Phone')
                        ->setTitle('Phone Number')
                        ->performReadonlyTransformation()
                ),
                TextField::create('Availability')
                    ->setValue(DBField::create_field('Date', $this->Available)->Nice())
                    ->setTitle('Availablity Starting')
                    ->performReadonlyTransformation(),
                'Application Information',
                HTMLEditorField::create('Content')
                    ->setTitle('Cover Letter')
                    ->performReadonlyTransformation(),
                TextField::create('ApplicationLink')
                    ->setValue(DBField::create_field('HTMLText',
                        '<a href="' . $this->Resume()->AbsoluteLink() . '" target="_blank">Application File</a>'))
                    ->setTitle('Application')
                    ->performReadonlyTransformation(),
            ]
        );

        $fields->fieldByName('Root.Main')->setTitle('Applicant Information');

        return $fields;
    }

    /**
     * @param null $member
     *
     * @return bool|int
     */
    public function canEdit($member = null)
    {
        return Permission::check('Job_EDIT', 'any', $member);
    }

    /**
     * @param null $member
     *
     * @return bool|int
     */
    public function canDelete($member = null)
    {
        return Permission::check('Job_DELETE', 'any', $member);
    }

    /**
     * @param null $member
     *
     * @return bool|int
     */
    public function canCreate($member = null, $contect = [])
    {
        return Permission::check('Job_CREATE', 'any', $member);
    }

    /**
     * @param null $member
     *
     * @return bool
     */
    public function canView($member = null)
    {
        return true;
    }
}
