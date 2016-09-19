// loads libraries
var DocxTemplater = require('docxtemplater'),
    ImageModule = require('docxtemplater-image-module'),
    sizeOf = require('image-size'),
    LinkModule = require('docxtemplater-link-module'),
    ChartModule = require('docxtemplater-chart-module'),
    fs = require('fs');

// initializes working variables
var templateRealPath, jsonDataRealPath, jsonData, populatedTemplateRealPath, document, content, buffer = null;

// retrieves the arguments
var arguments = process.argv.slice(2);

// first step, parses and checks the arguments
if (arguments.length > 3) {
    throw new Error('Too many arguments.');
}

if (typeof arguments[0] !== 'undefined') {
    templateRealPath = arguments[0];

    if (!fs.existsSync(templateRealPath)) {
        throw new Error('Unable to find the template at the location "' + templateRealPath + '".');
    }
} else {
    throw new Error('Please provide the template real path.')
}

if (typeof arguments[1] !== 'undefined') {
    jsonDataRealPath = arguments[1];

    if (!fs.existsSync(jsonDataRealPath)) {
        throw new Error('Unable to fin the JSON file at the location "' + jsonDataRealPath + '".')
    }

    try {
        content = fs.readFileSync(jsonDataRealPath);
        jsonData = JSON.parse(content);
    } catch (e) {
        throw new Error ('Please provide a valid JSON.')
    }

} else {
    throw new Error('Please provide the JSON data real path.')
}

if (typeof arguments[2] !== 'undefined' && arguments[2].length > 0) {
    populatedTemplateRealPath = arguments[2];

} else {
    throw new Error('Please provide the populated template real path.');
}

var imageOptions = {};

imageOptions.getImage=function(tagValue, tagName) {
    return fs.readFileSync(tagValue, 'binary');
};

imageOptions.getSize = function(image) {
    var sizeObj = sizeOf(image);
    return [ sizeObj.width, sizeObj.height ];
};

// last but not least, generates the populated template
fs.readFile(templateRealPath, function(err, data) {
    document = new DocxTemplater();
    document.attachModule(new ImageModule(imageOptions));
    document.attachModule(new LinkModule());
    document.attachModule(new ChartModule());
    document.load(data);
    document.setData(jsonData);
    document.render();
    buffer = document.getZip().generate({type: 'nodebuffer'});
    fs.writeFileSync(populatedTemplateRealPath, buffer);
});
