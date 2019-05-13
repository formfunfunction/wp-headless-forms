## WordPress Headless Forms

*Note that this plugin is currently in development. Try it, but you'll probably need to make modifications to get it to work in your own project.*

Creates simple contact forms for use over a REST API. It comes with an ACF-built form builder in the dashboard, but forms can also be defined programatically with custom field-types and custom validation.

This plugin is useful if you're using WordPress as a headless CMS, for instance using [Vue](https://github.com/vuejs/vue) or [React](https://github.com/facebook/react) as a frontend and you need something to process forms on the server-side. Most other WordPress form plugins require the use of PHP shortcodes or php functions which isn't very simple to serve over a REST API without some custom development. This plugin is designed as a solition to that.

## Installation

You can either install this plugin manually, or with composer.

### Composer
*To do*

### Manually
1. Clone this repository into the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

## Usage
This plugin creates two new post types `forms` and `form-entries`. To create a form, simply navigate to Forms, click `Add New` and add a few fields to get started.

This will expose new endpoints in the WordPress REST API.

`GET /forms/v1/forms/`

`GET /forms/v1/forms/{form-id}`

`POST /forms/v1/forms/{form-id}`

### Nonce tokens
To submit a form, you'll need to pass a nonce supplied in the response of the GET request.

### Example request

`GET https://example.com/wp-json/forms/v1/forms/{form-id}`

**Response**

```json
{
  "id": 1136,
  "fields": [
    {
      "name": "name",
      "label": "Name",
      "required": false,
      "field_type": "text",
      "id": 0
    },
    {
      "name": "email",
      "label": "Email Address",
      "required": true,
      "user_confirmations": false,
      "field_type": "email",
      "id": 1
    },
    {
      "name": "message",
      "label": "Message",
      "field_type": "text_area",
      "id": 2
    }
  ],
  "nonce": "a96bbc99db"
}
```

`POST https://example.com/wp-json/forms/v1/forms/{form-id}`

**Request body**

```json
{
  "name": "John Smith",
  "message": "Hi there! I'd like to enquire about one of your products.",
  "email": "john@example.com",
  "nonce": "a96bbc99db"
}
```

**Response**
```json
{
  "success": true,
  "form_data": {
    "name": "John Smith",
    "email": "john@example.com",
    "message": "Hi there! I'd like to enquire about one of your products."
  },
  "errors": null
}
```

## Notes:
* This plugin has been developed for a specific project, we intend to continue to develop it but it's currently not suitable for wider use. Feel free to clone it and make your own adjustments. If you create something that you feel would benefit others, please feel free to submit a pull request.
* There are email templates in the `/templates/` directory. These templates and functions require the fields in the example above, you'll need to either use the same fields or modify the templates to suit your use case. There's also hardcoded references to these fields in `WPHF_Submit->send_mail()` and `WPHF_Submit->create_post()`.

## Todo:
This is the current list of improvements we're intending to make.
* Make the email template content editible in the CMS.
* Create hooks for modifying validations and plugin functions.
* Create functions for programatically registering forms and fields.
* Add more field types in CMS.
* Remove dependency on ACF.
* Improve documentation.
* Submit to WordPress Plugin Directory.

## Changelog
**0.0.1-dev**
* Initial development version of the plugin.
