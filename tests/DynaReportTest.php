<?php

namespace ApiDen\DynaReport;

use PHPUnit\Framework\TestCase;

class DynaReportTest extends TestCase
{

    public function testGenerateAndDownloadReport()
    {
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
            getcwd() . '/tests/output/my-generatred-report.pdf'
        );

        $this->assertFileExists(getcwd() . '/tests/output/my-generatred-report.pdf');
    }
}
