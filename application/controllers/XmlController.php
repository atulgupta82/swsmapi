<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class XmlController extends CI_Controller {

    public function generate_xml() {
        // Create XML data
        $xml_data = array(
            'Expenditure' => array(
                'FIN_YEAR' => 'Sample Book',
                'PAYEEDETAILS' => 'John Doe',
                'SUBSIDIARY_ACCOUNTS' => '2023-08-09',
                'PAYEE_PARTY'=>[
                    'PAYEE_HEADER'=>[
                        'BANK_IFSC'=>'SBIN0RRELGB',
                        'SNA_ACCOUNT_NO'=>'5254512545555',
                        'BALANCE'=>'5254512545555',
                        'INTEREST'=>'5254512545555',
                        'PAYEECOUNT'=>'5254512545555',
                    ],
                ],
            )
        );

        // Create a new SimpleXMLElement
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><BankExp MsgDtTm="2023-07-01T12:56:34.869" MessageId="SBINExp01092023292" Source="SBIN" Destination="IFMS" StateName="Uttarakhand" RecordsCount="0" NetAmountSum=".00" xmlns="https://ifms.uk.gov.in/StateTreasuryExp"></BankExp>');

        // Convert the array to XML using a recursive function
        $this->array_to_xml($xml_data, $xml);

        // Define the path for the XML file
        $xml_file_path = '/var/www/api.uatesting.in/uploads/file.xml';

        // Save the XML data to the file
        $xml->asXML($xml_file_path);

        echo 'XML file generated successfully.';
    }

    private function array_to_xml($array, &$xml) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $xml->addChild($key);
                    $this->array_to_xml($value, $subnode);
                } else {
                    $subnode = $xml->addChild('item');
                    $this->array_to_xml($value, $subnode);
                }
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }
}
