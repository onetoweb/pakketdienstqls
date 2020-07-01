<?php

require 'vendor/autoload.php';

use Onetoweb\Pakketdienstqls\Client;

$email = 'info@example.com';
$password = 'password';

$client = new Client($email, $password);

// get companies
$companies = $client->getCompanies();
$companyId = $companies['data'][0]['id'];

// get company
$company = $client->getCompany($companyId);

// get company brands
$companyBrands = $client->getCompanyBrands($companyId);
$brandId = $companyBrands['data'][0]['id'];

// get company products
$companyProducts = $client->getCompanyProducts($companyId);

// get company shipments
$companyShipments = $client->getCompanyShipments($companyId);
$shipmentId = $companyShipments['data'][0]['id'];


// get company shipment
$companyShipment = $client->getCompanyShipment($companyId, $shipmentId);

// create company shipment
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
        'companyname' => 'companyname',
        'street' => 'street',
        'housenumber' => '1A',
        'address2' => '',
        'postalcode' => '1000AA',
        'locality' => 'locality',
        'country' => 'NL'
    ]
]);

// download label
$filename = '/path/to/file.pdf';
$client->downloadLabel($newShipment, $filename);

// get label contents
$labelContents = $client->getLabelContents($newShipment);