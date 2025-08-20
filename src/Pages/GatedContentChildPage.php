<?php
namespace Toast\GatedContent;


/**
 * GatedContentChildPage
 *
 * A child page that requires form submission on parent GatedContentPage.
 *
 * @package Toast\GatedContent
 */
use SilverStripe\Control\Cookie;
use SilverStripe\Control\Director;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\LiteralField;
use Toast\GatedContent\GatedContentPage;

class GatedContentChildPage extends \Page
{
    private static $table_name = 'GatedContentChildPage';
    private static $description = 'A child page that requires form submission on parent GatedContentPage.';
    private static $allowed_parents = [GatedContentPage::class];
    private static $can_be_root = false;
    private static $show_in_sitetree = true;

    public function canView($member = null)
    {
        // $parent = $this->Parent();
        // if ($parent && $parent instanceof GatedContentPage) {
        //     return $this->hasAccess($parent);
        // }
        // return parent::canView($member);
        return true;
    }

    
    /**
     * Check if user has access to this protected page
     */
    public function hasAccess($gatedContentPage)
    {
        // Allow CMS users to always access
        if (Director::is_cli() || $this->canEdit()) {
            return true;
        }
        
        // Check for valid access cookie
        $cookieName = $gatedContentPage->CookieName ?: 'form_access_granted';
        $cookieValue = Cookie::get($cookieName . '_' . $gatedContentPage->ID);
 
        return !empty($cookieValue);
    }
    
    /**
     * Handle access denied - redirect to parent form page
     */
    public function handleAccessDenied($gatedContentPage)
    {
        $controller = Controller::curr();

        $request = $controller->getRequest();

        // Store intended URL in session
        $request->getSession()->set('GatedContentPage.IntendedChildURL', $request->getURL());


        // Set error message in session
        $controller->getRequest()->getSession()->set(
            'GatedContentPage.AccessDenied',
            $gatedContentPage->AccessDeniedMessage ?: 
            'You must complete the form to access this content.'
        );
        // Redirect to parent form page
        return $controller->redirect($gatedContentPage->Link());
    }
    
    /**
     * Add protected status indicator to CMS
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $parent = $this->Parent();
        
        if ($parent && $parent->ClassName === GatedContentPage::class && $parent->EnableChildPageProtection) {
            $fields->addFieldToTab(
                'Root.Main',
                LiteralField::create(
                    'ProtectedStatus',
                    '<div class="message notice"><strong>Protected Page:</strong> This page requires form submission on the parent page for access and will not be redirected if another child page is set on parent page.</div>'
                ),
                'Title'
            );
        }
        return $fields;
    }
    
}
