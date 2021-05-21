<?php declare (strict_types = 1);

namespace Test\BaseClass;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use stdClass;

abstract class BaseConnection extends TestCase
{

    const BASE_URL_DEVELOP = 'http://localhost:8080/api/v1/';
    const TOKEN            = "";

    /**
     * @var mixed
     */
    protected static $baseUrl; // Base URL
    /**
     * @var mixed
     */
    protected static $data; // Data return of the API with two property: StatusCode and Body

    public static function setUpBeforeClass(): void
    {
        self::$baseUrl = self::BASE_URL_DEVELOP;
    }

    /**
     * Call url with many protocols
     *
     * @param String $method GET, POST, PUT and DELETE
     * @param String $path API route
     * @param Array $body Attribute when necessary
     *
     * @return stdClass
     */
    public static function http(String $method, String $path, array $body = []): stdClass
    {

        $client = new Client(['base_uri' => self::$baseUrl, 'http_errors' => false]);

        $headerAndBody = [
            'body' => json_encode($body),
        ];

        $response = $client->{$method}($path, $headerAndBody);

        $data = new \stdClass;

        $data->statusCode = $response->getStatusCode();
        $data->body       = json_decode($response->getBody()->getContents());

        return $data;
    }

}
