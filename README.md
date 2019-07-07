# DynaReport PHP Library

DynaReport is a cloud based, interactive report engine which helps in generating well formatted PDF reports from Word / DOCX templates.

To accelerate the process of creating your reports and applications, DynaReport takes advantage of Microsoft Word's design capabilites. Simply create a Microsoft Word file and design your report.

Detailed Documentation: https://www.apiden.com/dynareport/

## Example Usage

Add package to your project

`composer require api-den/dynareport-php`

then generate a report and download.

```php
$dynaReport = new DynaReport();

// API Key from apiden.com
$dynaReport->setApiKey("2fd3a35e2a3af7c293e2a6321f846030");

// Upload a template from your computer
$dynaReport->uploadTemplate(getcwd() . '/tests/template/template.docx');

// Set template variables and values
$dynaReport->setData([
    'first_name' => 'Murat',
    'last_name' => 'Cileli',
    'desc' => 'Invoice example',
    'company' => 'API Den',
    'website' => 'www.apiden.com'
    ]);
    
// Finally generate, download and save you report    
$dynaReport->generateAndDownloadReport(
    $dynaReport->getUploadedTemplateId(),
    getcwd() . '/tests/output/my-generated-report.pdf'
  );
```
