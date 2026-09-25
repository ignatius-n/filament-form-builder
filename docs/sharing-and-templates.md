# Sharing and templates

## Share

**Share** on a form (list and edit page) opens the public link with copy and open, the availability window (**Opens at** / **Closes at**, saved from the dialog) and the iframe snippet.

A **private** form (Settings › Access › Visibility) is not served at `/forms/{slug}`, nor by the definition endpoint: only a **share link** opens it, a signed URL the dialog generates, valid until the date you pick or without expiry. Anyone with the link can open the form while it is published and within its window; change the visibility back to public to drop the links, or set a closing date.

From code: `$form->shareUrl()` and `$form->shareUrl(now()->addDays(7))`.

## Password

A form with a **Password** shows a prompt first, in every renderer and on the hosted page. Once typed, the browser keeps an encrypted key in the session (or in the `fb_key` query parameter on a session-less site) and the form submits with it. JSON clients post the password to `POST /forms/{slug}/unlock` and send the returned `key` as `_fb_key`.

## Templates

**Use a template** on the Forms list creates a form from one of the twenty built-in templates (contact, demo request, newsletter, event registration, feedback and surveys, job application, support ticket, client onboarding, patient intake, catering order…), some with sections and conditions, ready to edit.

Offer your own: put files like the built-in ones (`resources/templates/*.php` in the package, each returning `name`, `category`, `description` and a portable `form` array) in a directory and register it:

```php
use Packstub\FormBuilder\Templates\Templates;

Templates::add(resource_path('form-templates'));
```

A portable form array is what **Export JSON** produces and `Form::fromArray()` reads: `name`, `description`, `fields`, `settings` and the other columns except the id, slug and dates.
