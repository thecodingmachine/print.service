# API

## Request headers

**Content-Type:** application/json

**Accept:** text/html 

or 

**Accept:** application/vnd.openxmlformats-officedocument.wordprocessingml.document 

or 

**Accept:** application/pdf

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

This request will throws an exception if you wanted a HTML or a Word document output.

## /api/v1/generate

Allows to generate a single document according to one or more templates and data.

## /api/v1/merge

Allows to generate many documents and merge them into one final document.

```json
[{
    "templates": [
        {
            "order": 0,
            "contentType": "application/pdf",
            "url": "http://adomain.com/yourTemplate.pdf"
        },
    ]
},
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
}]
```
