## Limitations

- This module does not currently support AJAX-enabled UserDefinedForm submissions. For the gating and redirect logic to work, AJAX must be disabled on the form.

# Toast\GatedContent SilverStripe Module

This module provides gated content functionality for SilverStripe projects using UserDefinedForm and cookies.

## Features
- **GatedContentPage**: Extends UserDefinedForm. When a user submits the form, a cookie is set to allow access to child pages. You can configure:
	- Custom cookie name and expiry
	- Access denied and success messages
	- Enable/disable child page protection
	- Redirect to a specific child page after submission
- **GatedContentChildPage**: Only accessible if the user has submitted the parent form (cookie-based). Cannot be created at the root level.
- **Custom Controllers**: Handles form submission, cookie setting, and access logic.
- **Session-based Redirect**: If a user tries to access a child page before submitting the form, they are redirected to the parent form. After submission, they are redirected back to the intended child page (unless a specific redirect is set).
- **Submission Storage**: Uses the default UserDefinedForm mechanism. Prune old submissions if DB size is a concern.

## Usage
1. Place this module in your SilverStripe project root (or require via Composer).
2. Run `dev/build?flush=1`.
3. In the CMS:
	 - Create a GatedContentPage, add a UserDefinedForm to it.
	 - Add child pages of type GatedContentChildPage (these will be protected).
	 - Configure cookie name, expiry, and redirect options as needed in the CMS fields for GatedContentPage.
4. When a user submits the form, a cookie is set. They can then access any child pages under that GatedContentPage.
5. If a user tries to access a child page before submitting the form, they are redirected to the parent form. After submission, they are redirected back to the child page (unless a specific redirect is set).

## Customization
- To minimize DB growth, periodically delete old submissions from the CMS.
- You can further customize the logic in the PHP classes as needed.
- You can style the form by targeting the `.no-ajax` class added to the UserDefinedForm.

## Technical Details
- All classes are under the `Toast\GatedContent` namespace.
- Controllers and page types are in `src/Pages/`.
- The module uses SilverStripe's PSR-4 autoloading and standard conventions.

## License
MIT
