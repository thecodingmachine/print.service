# Goals

Simple API for generating documents according to various types of templates (.twig, .docx, .pdf) and a set of data.

# API

## Request headers

Your request must have the following headers:

**Content-Type:** application/json

If you want a HTML output:

**Accept:** text/html 

A Word document output:

**Accept:** application/vnd.openxmlformats-officedocument.wordprocessingml.document 

A PDF document output: 

**Accept:** application/pdf

Also this API works with basic authentification. Please look at [API configuration](#api-configuration)!

## Single document JSON

```json
{
    "templates": [
        {
            "order": 0,
            "contentType": "text/html",
            "url": "http://adomain.com/yourTemplate.twig",
            "headerUrl": "http://adomain.com/yourTemplate.twig",
            "footerUrl": "http://adomain.com/yourTemplate.twig"
        },
        {
            "order": 1,
            "contentType": "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "url": "http://adomain.com/yourTemplate.docx"
        },
        {
            "order": 2,
            "contentType": "application/pdf",
            "url": "http://adomain.com/yourTemplate.pdf"
        },
    ],
    "data": {
        "property1": "value",
        "integer": 1,
        "decimal": 2.33,
        "array1": [
            {
                "id":1,
                "objectProperty": "value"
            },
            {
                "id":2,
                "objectProperty": "value"
            },
        ],
        "image": {
            "type": "image",
            "url": "http://adomaine.com/yourimage.png"
        },
        "link": {
            "type": "link",
            "text": "yourtextlink",
            "url": "http//adomain.com"
        }
    }
}
```

## Rules

### Templates properties

* order: required
* contentType: required
* url: required
* headerUrl: optional
* footerUrl: optinal

**Note:** the **headerUrl** and **footerUrl** are only available for twig templates.

### Data properties



### Media type exception

Except for application/pdf, your accept header line must match your templates' content types.

For example :

```json
{
    "templates": [
        {
            "order": 0,
            "contentType": "text/html",
            "url": "http://adomain.com/yourTemplate.twig",
            "headerUrl": "http://adomain.com/yourTemplate.twig",
            "footerUrl": "http://adomain.com/yourTemplate.twig"
        },
        {
            "order": 1,
            "contentType": "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "url": "http://adomain.com/yourTemplate.docx"
        },
        {
            "order": 2,
            "contentType": "application/pdf",
            "url": "http://adomain.com/yourTemplate.pdf"
        },
    ],
    "data": {
        "property1": "value"
    }
}
```

This request will throw an exception if you are requesting a HTML or a Word document output.

## /api/v1/generate

Allows to generate a single document according to one or more templates and data.

## /api/v1/merge

Allows to generate many documents and merge them into one final document.

```json
[
    {
        "templates": [
            {
                "order": 0,
                "contentType": "application/pdf",
                "url": "http://adomain.com/yourTemplate.pdf"
            },
        ]
    },
    {
        "templates": [
            {
                "order": 0,
                "contentType": "text/html",
                "url": "http://adomain.com/yourTemplate.twig",
                "headerUrl": "http://adomain.com/yourTemplate.twig",
                "footerUrl": "http://adomain.com/yourTemplate.twig"
            },
            {
                "order": 1,
                "contentType": "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                "url": "http://adomain.com/yourTemplate.docx"
            },
            {
                "order": 2,
                "contentType": "application/pdf",
                "url": "http://adomain.com/yourTemplate.pdf"
            },
        ],
        "data": {
            "property1": "value"
        }
    }
]
```

# API configuration

## Defining user(s) for HTTP basic authentification

Go to http://yourdomain.com/vendor/mouf/mouf/ajaxinstance/?name=httpBasicAuthenticationMiddleware and updates the options parameter.

# API stack

This API use:

* PDFtk: https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/ (merging PDF)
* wkhtmltopdf: http://wkhtmltopdf.org/ (HTML to PDF)
* LibreOffice: https://www.libreoffice.org/download/libreoffice-fresh/ (Word to PDF conversion with soffice command)
* Node 4_x with the following libraries:
    * https://github.com/open-xml-templating/docxtemplater (for populating a word template)
    * https://github.com/prog666/docxtemplater-chart-module (module for populating charts of a word template)
    * https://github.com/open-xml-templating/docxtemplater-image-module (module for images charts of a word template)
    * https://github.com/sujith3g/docxtemplater-link-module (module for populating links of a word template)