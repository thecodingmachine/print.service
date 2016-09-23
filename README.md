# Goals

Simple API for generating documents according to various types of templates (.twig, .docx, .pdf) and a set of data.

# Setting up your development environment

## Docker, docker-compose

Install Docker (**>= 1.10**) for MacOSX / Linux following the official instructions: <https://docs.docker.com/engine/installation/>

Install docker-compose (**>= 1.8.0**) for MacOSX / Linux following the official instructions: <https://docs.docker.com/compose/install/>

Move to the root directory of the project. You'll have to launch the following commands:

## Install php packages using:

```
./bin/composer $(pwd) install
```

## Build the docker container:

```
./bin/build $(pwd) dev
```

## Start the docker container:

```
./bin/up $(pwd) dev
```

## Last but not least
 
Install Mouf framework: <http://localhost/vendor/mouf/mouf>

# Candies

## Clean your docker cache:

```
./bin/clean
```

## Stop the container:

```
./bin/stop dev
```

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

## API stack

* twig: <http://twig.sensiolabs.org/> (twig to HTML)
* PDFtk: <https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/> (merging PDF)
* wkhtmltopdf: <http://wkhtmltopdf.org/> (HTML to PDF)
* LibreOffice: <https://www.libreoffice.org/download/libreoffice-fresh/> (Word to PDF conversion with soffice command)
* Node 4_x with the following libraries:
    * <https://github.com/open-xml-templating/docxtemplater> (for populating a word template)
    * <https://github.com/prog666/docxtemplater-chart-module> (module for populating charts of a word template)
    * <https://github.com/open-xml-templating/docxtemplater-image-module> (module for images charts of a word template)
    * <https://github.com/sujith3g/docxtemplater-link-module> (module for populating links of a word template)

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
* headerUrl: _optional_
* footerUrl: _optional_

**Note:** the **headerUrl** and **footerUrl** are only available for twig templates.

### Data properties

The data object is optional. Except for image and link special cases (see below), as long as the JSON is valid the API will be able to populate your templates.

### Populating templates

#### Twig template

Considering this data object:

```json
"data": {
    "property1": "value",
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
```

Your twig template will be filled using:

```php
<?php
[
    "property1" => "value",
    "image" => "http://adomaine.com/yourimage.png",
    "link" => [
        "text" => "yourtextlink",
        "url" => "http//adomain.com"
    ]
];
?>
```

#### Word template

Considering this data object:

```json
"data": {
    "property1": "value",
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
```

Your Word template will be filled using:

```json
{
    "property1": "value",
    "image": "yourimagerealpath.png",
    "link": {
        "text": "yourtextlink",
        "url": "http//adomain.com"
    }
}
```

**Note:** as you can see, the image specified in the original JSON has been downloaded by the API, because of a limitation of the docx templater image module which is not able to work with remote files.

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

## API configuration

### Defining user(s) for HTTP basic authentification

Go to <http://localhost/vendor/mouf/mouf/ajaxinstance/?name=httpBasicAuthenticationMiddleware> and update the options parameter.
    
# FAQ / Known issues
    
## The docker container is not running
    
You might have to stop your local apache.
    
## I've some permissions issues in Mouf
    
You might have to stop the container (`./bin/stop dev`) and start it again (`/bin/up $(pwd) dev`).

## I can't generate a Word document with many Word templates

Yep, this is currently a limitation. For now, you are only able to generate one Word document with one Word template.