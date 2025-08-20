<?php
namespace Toast\GatedContent;

use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\Control\Cookie;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;

/**
 * GatedContentPageController
 *
 * Handles form submission and sets access cookie for gated content.
 *
 * @package Toast\GatedContent
 */
class GatedContentPageController extends UserDefinedFormController
{
    private static $allowed_actions = [
        'Form',   
        'finished',
        'checkAccess',
    ];


    public function Form()
    {
        $form = parent::Form();
        // add class to form
        $form->setAttribute('class', 'no-ajax');
        return $form;
    }

    public function process($data, $form)
    {
        $response = parent::process($data, $form);
        // Set a cookie to allow access to child pages
        $pageID = $this->data()->ID;
        $cookieName = $this->data()->CookieName ?: 'form_access_granted';
 
        Cookie::set("{$cookieName}_{$pageID}", 1, 86400 * 30); // 30 days
        return $response;
    }
    
    public function index(HTTPRequest $request = null)
    {
        $session = $this->getRequest()->getSession();
        // Check for access denied message and clear it
        $accessDeniedMessage = $session->get('GatedContentPage.AccessDenied');
        if ($accessDeniedMessage) {
            $session->clear('GatedContentPage.AccessDenied');
            $this->customise([
                'AccessDeniedMessage' => $accessDeniedMessage
            ]);
        }
        
        return parent::index();
    }

     public function finished()
    {
        // redirect to selected child or intended child
        $redirectPage = $this->data()->RedirectAfterSubmission();
        $intendedChildURL = $this->getRequest()->getSession()->get('GatedContentPage.IntendedChildURL');
        $this->getRequest()->getSession()->clear('GatedContentPage.IntendedChildURL');
      
        if ($redirectPage && $redirectPage->exists()) {
            return $this->redirect($redirectPage->Link());
        } 
        // only redirect to intended if redirect after submission is not set in UDF
        elseif ($intendedChildURL) {
            return $this->redirect($intendedChildURL);
        } else {
            return parent::finished();
        }
    }
    /**
     * Check if user has access (AJAX endpoint)
     */
    public function checkAccess()
    {
        $hasAccess = $this->data()->hasFormAccess();
        
        return $this->getResponse()->setBody(json_encode([
            'hasAccess' => $hasAccess,
            'message' => $hasAccess ? 'Access granted' : 'Access denied'
        ]))->addHeader('Content-Type', 'application/json');
    }
    
}
