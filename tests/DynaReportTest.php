<?php

namespace ApiDen\DynaReport;

use PHPUnit\Framework\TestCase;

class DynaReportTest extends TestCase
{

    public function testGenerateAndDownloadReport()
    {
        $outputFile = tempnam(sys_get_temp_dir(), 'dynareport');

        $dr = new DynaReport();
        $dr->setApiKey("2fd3a35e2a3af7c293e2a6321f846030");
        $dr->uploadTemplate(getcwd() . '/tests/template/template.docx');
        $dr->setData([
            'first_name' => 'Murat',
            'last_name' => 'Cileli',
            'desc' => 'Invoice example',
            'company' => 'API Den',
            'website' => 'www.apiden.com'
        ]);
        $dr->generateAndDownloadReport(
            $dr->getUploadedTemplateId(),
            $outputFile
        );

        $this->assertFileExists($outputFile);
    }
}
