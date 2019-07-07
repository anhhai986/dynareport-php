<?php

namespace ApiDen\DynaReport;

use Exception;

/**
 * Class DynaReport
 *
 * DynaReport is Cloud based, interactive report engine
 * which helps in generating well formatted PDF reports
 * from Word / DOCX templates.
 *
 * @package ApiDen\DynaReport
 * @see https://www.apiden.com/dynareport
 */
class DynaReport
{
    protected $apiKey;
    protected $templateId;
    protected $outputType = 'pdf';
    protected $data = [];

    /**
     * @throws Exception
     */
    private function checkApikey(): void
    {
        if (isset($this->apiKey) === false) {
            throw new Exception('API Key not set');
        }
    }

    /**
     * setApiKey
     *
     * @param string $apiKey
     *
     * @return void
     * @throws Exception
     */
    public function setApiKey(string $apiKey): void
    {
        if (strlen($apiKey) !== 32) {
            throw new Exception("Invalid API key format", 1);
        }

        $this->apiKey = $apiKey;
    }

    /**
     * setOutputType
     *
     * @param string $outputType
     *
     * @return void
     * @throws Exception
     */
    public function setOutputType(string $outputType): void
    {
        if (in_array($outputType, ['docx', 'pdf']) === false) {
            throw new Exception("Invalid output type. Accepted values: pdf, docx");
        }

        $this->outputType = $outputType;
    }

    /**
     * @param string $templateFileName
     * @return string
     * @throws Exception
     */
    public function uploadTemplate(string $templateFileName): string
    {
        $this->checkApiKey();

        if (file_exists($templateFileName) === false) {
            throw new Exception("File not found: {$templateFileName}");
        }

        $mime = mime_content_type($templateFileName);

        if ($mime == false || $mime != 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            throw new Exception("Invalid file format");
        }

        $templateFileBase64 = base64_encode(file_get_contents($templateFileName));
        $templateFileName = pathinfo($templateFileName)['filename'];

        if (isset($templateFileName) === false || isset($templateFileBase64) === false) {
            throw new Exception('No template file specified to upload.');
        }

        $postValues = [];
        $postValues['apiKey'] = $this->apiKey;
        $postValues['fileName'] = $templateFileName;
        $postValues['fileBase64'] = $templateFileBase64;

        $postValuesJson = json_encode($postValues);

        $ch = curl_init('https://api.apiden.com/dynareport/template');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postValuesJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postValuesJson)
            ]);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        switch ($statusCode) {
            case 200:
                $response = json_decode($response, true);
                $this->templateId = $response['id'];
                return $this->templateId;
            case 401:
                throw new Exception('Invalid API key. Please contact support@apiden.com');
            default:
                throw new Exception('An error occured while processing your request. Please contact support@apiden.com');
        }
    }

    public function getUploadedTemplateId(): string
    {
        if (isset($this->templateId)) {
            return $this->templateId;
        }

        throw new Exception("No template uploaded in this session. Specify a template ID manually or please contact support@apiden.com");
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function generateAndDownloadReport(string $templateId, string $outputFile): void
    {
        $this->checkApikey();

        $postValues = [];
        $postValues['apiKey'] = $this->apiKey;
        $postValues['templateId'] = $templateId;
        $postValues['data'] = $this->data;
        $postValues['outputType'] = $this->outputType;

        $postValuesJson = json_encode($postValues);

        $ch = curl_init('https://api.apiden.com/dynareport');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postValuesJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postValuesJson)
            ]);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        switch ($statusCode) {
            case 200:
                $response = json_decode($response, true);
                $downloadUrl = $response['downloadUrl'];
                $resGetContents = file_get_contents($downloadUrl);
                $resPutContents = file_put_contents($outputFile, $resGetContents);
                if ($resGetContents === false || $resPutContents === false) {
                    throw new Exception("Error while downloading report. Please check write permissions for {$outputFile} or contact support@apiden.com");
                }
                break;

            case 401:
                throw new Exception('Invalid API key. Please contact support@apiden.com');

            default:
                throw new Exception("An error occured while processing your request. Please contact support@apiden.com");
        }
    }
}
