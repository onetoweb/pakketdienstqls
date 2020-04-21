<?php

require 'vendor/autoload.php';

use Onetoweb\Pakketdienstqls\Client;

$email = 'info@example.com';
$password = 'password';

$client = new Client($email, $password);

// get companies
try {
    $companies = $client->getCompanies();
    $companyId = $companies['data'][0]['id'];
} catch (Exception $exception) { }

// get company
try {
    $company = $client->getCompany($companyId);
} catch (Exception $exception) { }

// get company brands
try {
    $companyBrands = $client->getCompanyBrands($companyId);
    $brandId = $companyBrands['data'][0]['id'];
} catch (Exception $exception) { }

// get company products
try {
    $companyProducts = $client->getCompanyProducts($companyId);
} catch (Exception $exception) { }

// get company shipments
try {
    $companyShipments = $client->getCompanyShipments($companyId);
    $shipmentId = $companyShipments['data'][0]['id'];
} catch (Exception $exception) { }

// get company shipment
try {
    $companyShipment = $client->getCompanyShipment($companyId, $shipmentId);
} catch (Exception $exception) { }

// create company shipment
try {
    
    $newShipment = $client->createCompanyShipment($companyId, [
        'reference' => 'reference example',
        'weight' => 1,
        'brand_id' => $brandId,
        'product_id' => 1,
        'product_combination_id' => 1,
        'cod_amount' => 0,
        'piece_total' =>  1,
        'receiver_contact' => [
            'name' => '',
            'companyname' => 'Onetoweb B.V.',
            'street' => 'Oudestraat',
            'housenumber' => '216',
            'address2' => '',
            'postalcode' => '8261CA',
            'locality' => 'Kampen',
            'country' => 'NL'
        ]
    ]);
    
    $filename = '/path/to/file.pdf';
    $client->downloadLabel($newShipment, $filename);
    
} catch (Exception $exception) { }
