<?php
namespace UserAgentParserTest\Unit\Provider;

use GuzzleHttp\Psr7\Response;
use stdClass;
use UserAgentParser\Provider\Http\NeutrinoApiCom;

/**
 * @covers UserAgentParser\Provider\Http\NeutrinoApiCom
 */
class NeutrinoApiComTest extends AbstractProviderTestCase
{
    public function testName()
    {
        $provider = new NeutrinoApiCom($this->getClient(), 'apiUser', 'apiKey123');

        $this->assertEquals('NeutrinoApiCom', $provider->getName());
    }

    public function testGetHomepage()
    {
        $provider = new NeutrinoApiCom($this->getClient(), 'apiUser', 'apiKey123');

        $this->assertEquals('https://www.neutrinoapi.com/', $provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = new NeutrinoApiCom($this->getClient(), 'apiUser', 'apiKey123');

        $this->assertNull($provider->getPackageName());
    }

    public function testVersion()
    {
        $provider = new NeutrinoApiCom($this->getClient(), 'apiUser', 'apiKey123');

        $this->assertNull($provider->getVersion());
    }

    public function testDetectionCapabilities()
    {
        $provider = new NeutrinoApiCom($this->getClient(), 'apiUser', 'apiKey123');

        $this->assertEquals([

            'browser' => [
                'name'    => true,
                'version' => true,
            ],

            'renderingEngine' => [
                'name'    => false,
                'version' => false,
            ],

            'operatingSystem' => [
                'name'    => true,
                'version' => false,
            ],

            'device' => [
                'model'    => true,
                'brand'    => true,
                'type'     => true,
                'isMobile' => true,
                'isTouch'  => false,
            ],

            'bot' => [
                'isBot' => true,
                'name'  => true,
                'type'  => false,
            ],
        ], $provider->getDetectionCapabilities());
    }

    /**
     * Empty user agent
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testGetResultNoResultFoundExceptionEmptyUserAgent()
    {
        $responseQueue = [
            new Response(200),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $provider->parse('');
    }

    /**
     * 403
     *
     * @expectedException \UserAgentParser\Exception\InvalidCredentialsException
     */
    public function testGetResultInvalidCredentialsException()
    {
        $responseQueue = [
            new Response(403),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * 500
     *
     * @expectedException \UserAgentParser\Exception\RequestException
     */
    public function testGetResultRequestException()
    {
        $responseQueue = [
            new Response(500),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * No JSON returned
     *
     * @expectedException \UserAgentParser\Exception\RequestException
     */
    public function testGetResultRequestExceptionContentType()
    {
        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'text/html',
            ], 'something'),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * Error code 1
     *
     * @expectedException \UserAgentParser\Exception\RequestException
     */
    public function testGetResultRequestExceptionCode1()
    {
        $rawResult                = new stdClass();
        $rawResult->api_error     = 1;
        $rawResult->api_error_msg = 'something';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * Error code 2
     *
     * @expectedException \UserAgentParser\Exception\LimitationExceededException
     */
    public function testGetResultLimitationExceededExceptionCode2()
    {
        $rawResult                = new stdClass();
        $rawResult->api_error     = 2;
        $rawResult->api_error_msg = 'something';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * Error code something
     *
     * @expectedException \UserAgentParser\Exception\RequestException
     */
    public function testGetResultRequestExceptionCodeSomething()
    {
        $rawResult                = new stdClass();
        $rawResult->api_error     = 1337;
        $rawResult->api_error_msg = 'something';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * Missing data
     *
     * @expectedException \UserAgentParser\Exception\RequestException
     */
    public function testGetResultRequestExceptionNoData()
    {
        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], ''),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * no result found
     *
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $rawResult       = new stdClass();
        $rawResult->type = 'unknown';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $result = $provider->parse('A real user agent...');
    }

    /**
     * Bot
     */
    public function testParseBot()
    {
        $rawResult               = new stdClass();
        $rawResult->type         = 'robot';
        $rawResult->browser_name = 'Googlebot';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name'  => 'Googlebot',
                'type'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Browser only
     */
    public function testParseBrowser()
    {
        $rawResult               = new stdClass();
        $rawResult->type         = 'desktop-browser';
        $rawResult->browser_name = 'Firefox';
        $rawResult->version      = '3.2.1';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name'    => 'Firefox',
                'version' => [
                    'major' => 3,
                    'minor' => 2,
                    'patch' => 1,

                    'alias' => null,

                    'complete' => '3.2.1',
                ],
            ],

            'device' => [
                'model' => null,
                'brand' => null,
                'type'  => 'desktop-browser',

                'isMobile' => null,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * OS only
     */
    public function testParseOperatingSystem()
    {
        $rawResult                   = new stdClass();
        $rawResult->type             = 'desktop-browser';
        $rawResult->operating_system = 'Windows 7';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name'    => 'Windows 7',
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],

            'device' => [
                'model' => null,
                'brand' => null,
                'type'  => 'desktop-browser',

                'isMobile' => null,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device only
     */
    public function testParseDevice()
    {
        $rawResult               = new stdClass();
        $rawResult->type         = 'mobile-browser';
        $rawResult->mobile_model = 'iPhone';
        $rawResult->mobile_brand = 'Apple';
        $rawResult->is_mobile    = true;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json;charset=UTF-8',
            ], json_encode($rawResult)),
        ];

        $provider = new NeutrinoApiCom($this->getClient($responseQueue), 'apiUser', 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type'  => 'mobile-browser',

                'isMobile' => true,
                'isTouch'  => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
