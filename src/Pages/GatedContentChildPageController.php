<?php
namespace Toast\GatedContent;


/**
 * GatedContentChildPageController
 *
 * Controller for GatedContentChildPage.
 *
 * @package Toast\GatedContent
 */
use Toast\GatedContent\GatedContentPage;

class GatedContentChildPageController extends \PageController
{

    /**
     * Check if this page requires protection and handle access control
     */
    protected function init()
    {
        parent::init();

        $page = $this->data();
        $parent = $page->Parent();
        if ($parent && $parent instanceof GatedContentPage) {
            if (!$page->hasAccess($parent)) {
                // Optionally set a session message here
                return $page->handleAccessDenied($parent);
            }
        }
    }

    
}
