
# Probance Optin

Plugin displaying a checkbox and a Newsletter banner to manage the optin on Probance side.

## Tech Stack

**Client:** HTML, Javascript (jQuery & Ajax), CSS

**Server:** PHP


## Tree Structure

```
probance-optin
├── assets
│   ├── admin.css
│   ├── admin.js
│   ├── newsletter-form-css.css
│   ├── probance_lang.js
│   └── probance_newsletter.js
├── inc
│   ├── Base
│   │   ├── Activate.php
│   │   ├── Ajax.php
│   │   ├── Desactivate.php
│   │   ├── Enqueue.php
│   │   └── SettingsField.php
│   ├── Common
│   │   ├── ProbanceAPI.php
│   │   └── Utils.php
│   ├── Data
│   │   ├── ApiFields.php
│   │   ├── Data.php
│   │   ├── NewsletterFields.php
│   │   ├── Translations.php
│   │   └── WebelementFields.php
│   ├── Init.php
│   ├── Pages
│   │   ├── Admin.php
│   │   ├── Newsletter.php
│   │   └── OptinConsents.php
│   └── Uninstall.php
├── index.php
├── probance-optin.php
├── src
│   └── images
│       ├── logo-icon.png
│       └── logo.png
├── templates
│   ├── admin_optin.php
│   ├── admin.php
│   └── admin_track.php
├── translations
│   ├── _default.json
│   ├── _en.json
│   ├── _es.json
│   └── _fr.json
├── uninstall.php
├── vendor
├── composer.json
└── composer.lock
```

### Folders & Files Descriptions

- [Assets](https://linktodocumentation) : location where JavaScript and CSS are stored.
- [inc/Base](https://linktodocumentation) : location where are declared and registered settings field, JS and CSS scripts, Ajax methods.
- [inc/Common](https://linktodocumentation) : location of the Probance API and utils fundttions (PHP rendring language).
- [inc/Data](https://linktodocumentation) : location where the variables are declared for the creation of the settings field and where traslations are managed.
- [inc/Pages](https://linktodocumentation) : location where front is handled (Admin page, Newlstter banner, Optin checkboxes)
- [inc/Init.php](https://linktodocumentation) : file that instantiates all classes and register all Wordpress actions. 
- [inc/Uninstall.php](https://linktodocumentation) : class that contains the delete option method (disabled by defaut). 
- [index.php](https://linktodocumentation) : empty file - can be ignored.
- [probance-optin.php](https://linktodocumentation) : main script containing the call of the inc/Init.php and utils constants.
- [uninstall.php](https://linktodocumentation) : script containing the call of the inc/Uninstall.php and utils constants.
- [src](https://linktodocumentation) : location of images (Probance logo).
- [templates](https://linktodocumentation) : location of PHP files called by inc/Pages/Admin.php - only admin_optin.php is not empty.
- [translations](https://linktodocumentation) : location where are stored defaults translations (default, fr, en and es).
- [vendor](https://linktodocumentation) : auto generated - can be ignored.
- [composer](https://linktodocumentation) : auto generated - can be ignored.

#### Assets

- **admin.js** contains all JS scrips used to handle admin form, properties & translations pop-ups.
- **admin.css** contains all style used by the admin BO.
- **probance_newsletter.js** contains all newsletter scripts used by the client to manage submiting and displaying.
- **newsletter-form-css.css** contains newsletter styles.
- **probance_lang.js** contains scripts used to get client's current language.




