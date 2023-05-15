# Forms plugin for October CMS

This plugin provides a simple way to create frontend forms.

## Installation

Install the plugin using composer:

```
composer require offline/oc-forms-plugin
```

## Features

- Create forms in the backend
- Display forms on the frontend
- View submissions in the backend
- Send email notifications on form submission
- Export submissions to CSV
- Multisite support
- Optional file uploads using [Responsiv.Uploader](https://octobercms.com/plugin/responsiv-uploader)
- Integrates with [OFFLINE.Boxes](https://octobercms.com/plugin/offline-boxes) out-of-the-box.


## Usage

In the backend, go to the Forms menu item and create a new form. You can use the `renderForm` component to display
the form on the frontend. All submissions of the form will be visible in the backend.

## Components

### `renderForm`

Use the `renderForm` component to display a form on the frontend.

#### AJAX framework dependency

The component requires the AJAX framework to be present on the page.
You can set the `includeFramework` property to `true` to include the framework automatically.

#### CSS classes

The default form markup comes with a few CSS classes that you can use to style your form.

You can set the `cssPrefix` property to change the prefix of the CSS classes.

#### Form classes

Use the `formClasses` property to add additional CSS classes to the form element.

#### Honeypot

The form comes with a honeypot field to prevent spam. You can disable this by setting the `honeypot` property to `false`.

#### File uploads

To enable file uploads, you need to install the [Responsiv.Uploader](https://octobercms.com/plugin/responsiv-uploader) plugin:

```
composer require responsiv/uploader-plugin
```

After installing the plugin, you can add file upload fields to your form in the backend.

**Important**: Responsiv.Uploader depends on jQuery. This means you need to include jQuery on your page.
Set the `includeJQuery` property to `true` if you want the component to include jQuery automatically.

#### Field overrides

The `renderForm` component comes with a powerful way to override the default form field partials.

To do so, create a partial with the proper name in your `partials` directory.

Overrides are process in the following order:

##### Name overrides

Override a field by its name. To do so, create a partial with the following name:

```bash
_name_{fieldName}.htm
# Example: _name_address.htm
# Important: The field name is sluggified. So "Your Name" becomes "your-name".
```

##### Type overrides

Override all fields with a given input type. To do so, create a partial with the following name:

```bash
_type_{fieldType}.htm
# Example: _type_email.htm
```

##### Generic overrides

Take a look at the [default partials](./components/renderform) of the `renderForm` component.
You can override any other partial like the `label` or the `validation` message.


## OFFLINE.Boxes integration

To make the `renderForm` component available in Boxes, use the following partial:

### form.htm

```twig
{% component box.uniqueComponentAlias('renderForm') %}
```

### form.yaml

```yaml
handle: OFFLINE.Forms::forms
name: 'offline.forms::lang.forms'
section: 'offline.forms::lang.boxes_section'

validation:
    rules:
        form: required

components:
    renderForm:
        uniqueAlias: true
        # properties:
        #   formClasses: 'floating-label'
        #   cssPrefix: 'prefix-'
        #   includeJQuery: true
        #   includeFramework: true

form:
    fields:
        form:
            label: 'offline.forms::lang.form'
            type: dropdown
            span: full
            emptyOption: 'offline.forms::lang.form_empty_option'
            options: '\OFFLINE\Forms\Models\Form::getFormOptions'
```
