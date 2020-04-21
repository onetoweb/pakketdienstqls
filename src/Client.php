<?php

namespace Onetoweb\Pakketdienstqls;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\RequestOptions;
use Onetoweb\Pakketdienstqls\Exception\RequestException;
use Onetoweb\Pakketdienstqls\Exception\FileException;
use Onetoweb\Pakketdienstqls\Exception\LabelFormatException;
use Onetoweb\Pakketdienstqls\Exception\InputException;

/**
 * Pakketdienstqls Client API
 *
 * @author Jonathan van 't Ende <jvantende@onetoweb.nl>
 * @license MIT
 * 
 * @see https://api.pakketdienstqls.nl/swagger/
 */
class Client
{
    /**
     * @var string
     */
    const BASE_URI = 'https://api.pakketdienstqls.nl';
    
    /**
     * @var string
     */
    private $email;
    
    /**
     * @var string
     */
    private $password;
    
    /**
     * @var string
     */
    private $companyId;
    
    /**
     * @param string $email
     * @param string $password
     */
    public function __construct($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
    
    /**
     * Send a request.
     * 
     * @param string $method = 'GET'
     * @param string $endpoint
     * @param array $data = null
     * 
     * @throws RequestException with request errors
     * 
     * @return array
     */
    public function request($method, $endpoint, $data = null)
    {
        $client = new GuzzleClient(['base_uri' => self::BASE_URI]);
        
        try {
            
            $options = [
                RequestOptions::AUTH => [$this->email, $this->password],
                RequestOptions::HEADERS => [
                    'Accept: application/json',
                    'Content-Type: application/json'
                ]
            ];
            
            if ($method == 'POST') {
                $options[RequestOptions::FORM_PARAMS] = $data;
            }
            
            $result = $client->request($method, $endpoint, $options);
            
            $contents = $result->getBody()->getContents();
            
        } catch (GuzzleRequestException $requestException) {
            
            if ($requestException->hasResponse()) {
                
                $error = preg_replace('/\v(?:[\v\h]+)/', '', strip_tags((string) $requestException->getResponse()->getBody()->getContents()));
                
                throw new RequestException($error, $requestException->getCode(), $requestException);
            }
            
            throw new RequestException($requestException->getMessage(), $requestException->getCode(), $requestException);
        }
        
        return json_decode($contents, true);
    }
    
    /**
     * @param array $data
     * @param string $filename
     * @param string $format = 'a4'
     * @param string $offset = 'offset_0'
     * 
     * @throws FormatException if requested label formats do not exists
     * @throws FileException if the file is not writable
     * @throws RequestException if the file cannot be downloaded from the server
     */
    public function downloadLabel($data, $filename, $format = 'a4', $offset = 'offset_0')
    {
        $url = null;
        
        if (isset($data['labels'][$format])) {
            
            if (is_array($data['labels'][$format])) {
                
                if (isset($data['labels'][$format][$offset])) {
                    
                    $url = $data['labels'][$format][$offset];
                    
                }
                
            } else {
                
                $url = $data['labels'][$format];
                
            }
        }
        
        if ($url == null) {
            throw new LabelFormatException("label not found with format: '$format' and offset: '$offset'");
        }
        
        if (!is_writable(dirname($filename))) {
            throw new FileException("file: $filename is not writable");
        }
        
        $client = new GuzzleClient();
        $resonse = $client->request('GET', $url, ['sink' => $filename]);
        
        if ($resonse->getStatusCode() != 200) {
            
            throw new RequestException("failed to download label from: $url", $resonse->getStatusCode());
            
        }
    }
    
    /**
     * Send a GET request.
     *
     * @param string $endpoint
     *
     * @return array
     */
    public function get($endpoint)
    {
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Send a GET request.
     *
     * @param string $endpoint
     *
     * @return array
     */
    public function post($endpoint, $data)
    {
        return $this->request('POST', $endpoint, $data);
    }
    
    /**
     * Get companies.
     * 
     * @param int $page = 1
     * 
     * @return array
     */
    public function getCompanies($page = 1)
    {
        return $this->get("company?page=$page");
    }
    
    /**
     * Get company.
     * 
     * @param string $companyId = null
     * 
     * @return array
     */
    public function getCompany($companyId = null)
    {
        $results = $this->get("company/$companyId");
        
        return $results['data'];
    }
    
    /**
     * Get company brands.
     *
     * @param string $companyId
     *
     * @return array
     */
    public function getCompanyBrands($companyId)
    {
        return $this->get("company/$companyId/brand");
    }
    
    /**
     * Get company products.
     *
     * @param string $companyId
     *
     * @return array
     */
    public function getCompanyProducts($companyId)
    {
        return $this->get("company/$companyId/product");
    }
    
    /**
     * Get company shipments.
     *
     * @param string $companyId
     * @param int $page = 1
     *
     * @return array
     */
    public function getCompanyShipments($companyId, $page = 1)
    {
        return $this->get("company/$companyId/shipment?page=$page");
    }
    
    /**
     * Get company shipments.
     *
     * @param string $companyId
     * @param string $shipmentId
     * @param int $page = 1
     *
     * @return array
     */
    public function getCompanyShipment($companyId, $shipmentId)
    {
        $result = $this->get("company/$companyId/shipment/$shipmentId");
        
        return $result['data'];
    }
    
    /**
     * Create company shipments.
     *
     * @param string $companyId
     * @param array $data
     *
     * @throws InputException with input errors from the clients
     *
     * @return array
     */
    public function createCompanyShipment($companyId, $data)
    {
        $result = $this->post("company/$companyId/shipment/create", $data);
        
        if (isset($result['errors'])) {
            throw new InputException(json_encode($result['errors']));
        }
        
        return $result['data'];
    }
}