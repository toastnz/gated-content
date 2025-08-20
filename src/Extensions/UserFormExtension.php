<?php

namespace Toast\Extensions;

use SilverStripe\Core\Extension;
use Toast\GatedContent\GatedContentPage;

class UserFormExtension extends Extension
{
    /**
     * Update the form to add a custom class if NoAjax is set
     *
     * @param \SilverStripe\Forms\Form $form
     */
    public function updateForm()
    {   
        if ($this->owner->controller->data() instanceof GatedContentPage) {
            $this->owner->setAttribute('class', 'no-ajax');
        }
    }
}