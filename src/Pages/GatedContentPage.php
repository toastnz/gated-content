<?php
namespace Toast\GatedContent;

use SilverStripe\Control\Cookie;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\GridField\GridField;
use Toast\GatedContent\GatedContentChildPage;
use SilverStripe\UserForms\Model\UserDefinedForm;
use Toast\GatedContent\GatedContentPageController;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
/**
 * GatedContentPage
 *
 * A page with a form that grants access to child pages via cookie after submission.
 *
 * @package Toast\GatedContent
 */
class GatedContentPage extends UserDefinedForm
{
    private static $table_name = 'GatedContentPage';
    private static $description = 'A page with a form that grants access to child pages via cookie after submission.';

    // allowed children
    private static $allowed_children = [
        GatedContentChildPage::class,
    ];

    private static $db = [
        'CookieName' => 'Varchar(255)',
        'CookieExpiry' => 'Int', // Days
        'AccessDeniedMessage' => 'HTMLText',
        'SuccessRedirectMessage' => 'HTMLText',
        'EnableChildPageProtection' => 'Boolean',
    ];

    private static $has_one = [
        'RedirectAfterSubmission' => GatedContentChildPage::class
    ];

    private static $defaults = [
        'CookieName' => 'form_access_granted',
        'CookieExpiry' => 30, // 30 days default
        'EnableChildPageProtection' => true,
        'AccessDeniedMessage' => '<p>You must complete the form on the parent page to access this content.</p>',
        'SuccessRedirectMessage' => '<p>Thank you for submitting the form. You now have access to protected content.</p>',
    ];


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        // Only show child pages of this page
    $children = $this->Children()->map('ID', 'Title')->toArray();

        
        $fields->addFieldsToTab('Root.AccessControl', [
            HeaderField::create('AccessControlHeader', 'Access Control Settings'),
            CheckboxField::create('EnableChildPageProtection', 'Enable child page protection')
                ->setDescription('When enabled, child pages will require form submission to access'),
            DropdownField::create(
                'RedirectAfterSubmissionID',
                'Redirect to this child page after successful submission (optional)',
                $children
            )->setEmptyString('-- None --'),
            TextField::create('CookieName', 'Cookie Name')
                ->setDescription('Name of the cookie that grants access (default: form_access_granted)'),
            NumericField::create('CookieExpiry', 'Cookie Expiry (Days)')
                ->setDescription('Number of days the access cookie should remain valid'),
            TextareaField::create('AccessDeniedMessage', 'Access Denied Message')
                ->setDescription('Message shown when users try to access protected content without permission'),
            TextareaField::create('SuccessRedirectMessage', 'Success Message')
                ->setDescription('Message shown after successful form submission'),
        ]);

        
        return $fields;
    }
    
    public function canView($member = null)
    {
        // Always allow viewing the form page itself
        return parent::canView($member);
    }
    
    /**
     * Check if the current user has access based on cookie
     */
    public function hasFormAccess()
    {
        $cookieName = $this->CookieName ?: 'form_access_granted';
        $cookieValue = Cookie::get($cookieName . '_' . $this->ID);
        
        return !empty($cookieValue);
    }
    
    /**
     * Grant access by setting the cookie
     */
    public function grantAccess($submissionData = [])
    {
        $cookieName = $this->CookieName ?: 'form_access_granted';
        $expiry = $this->CookieExpiry ?: 30;
        
        // Set cookie with expiry
        Cookie::set(
            $cookieName . '_' . $this->ID,
            hash('sha256', serialize($submissionData) . time()),
            $expiry,
            null,
            null,
            false,
            true // HTTP only for security
        );
        
        // Record the submission for tracking
        $this->recordSubmission($submissionData);
    }
    
    
    /**
     * Get protected child pages
     */
    public function getProtectedChildren()
    {
        if (!$this->EnableChildPageProtection) {
            return $this->Children();
        }
        
        return $this->Children()->filter('ClassName:not', [
            'SilverStripe\\ErrorPage\\ErrorPage'
        ]);
    }

    public function getControllerName()
    {
        return GatedContentPageController::class;
    }
}
