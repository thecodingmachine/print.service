var XlsxTemplate = require('xlsx-template'),
  fs = require('fs');

// initializes working variables
var templateRealPath, jsonData, populatedTemplateRealPath, document, content, buffer = null;

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
    try {
        jsonData = JSON.parse(arguments[1]);
    } catch (e) {
        throw new Error ('Please provide a valid JSON.')
    }

} else {
   // throw new Error('Please provide the JSON data real path.')
    throw new Error('Please provide the JSON data.')
}

if (typeof arguments[2] !== 'undefined' && arguments[2].length > 0) {
    populatedTemplateRealPath = arguments[2];

} else {
    throw new Error('Please provide the populated template real path.');
}

// Load an XLSX file into memory
fs.readFile(templateRealPath, function(err, data) {

    // Create a template
    var document = new XlsxTemplate(data);
    // Perform substitution
    document.substitute(1, jsonData);

    buffer = document.generate();
    fs.writeFile(populatedTemplateRealPath, buffer, 'binary');
    //fs.writeFileSync(populatedTemplateRealPath, buffer);

});