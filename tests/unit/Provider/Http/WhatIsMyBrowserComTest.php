<?php
namespace UserAgentParserTest\Provider;

use GuzzleHttp\Psr7\Response;
use stdClass;
use UserAgentParser\Provider\Http\WhatIsMyBrowserCom;

/**
 * @covers UserAgentParser\Provider\Http\WhatIsMyBrowserCom
 */
class WhatIsMyBrowserComTest extends AbstractProviderTestCase
{
    public function testName()
    {
        $provider = new WhatIsMyBrowserCom($this->getClient(), 'apiKey123');

        $this->assertEquals('WhatIsMyBrowserCom', $provider->getName());
    }

    public function testGetHomepage()
    {
        $provider = new WhatIsMyBrowserCom($this->getClient(), 'apiKey123');

        $this->assertEquals('https://www.whatismybrowser.com/', $provider->getHomepage());
    }

    public function testGetPackageName()
    {
        $provider = new WhatIsMyBrowserCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getPackageName());
    }

    public function testVersion()
    {
        $provider = new WhatIsMyBrowserCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getVersion());
    }

    public function testDetectionCapabilities()
    {
        $provider = new WhatIsMyBrowserCom($this->getClient(), 'apiKey123');

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
                'version' => true,
            ],

            'device' => [
                'model'    => false,
                'brand'    => false,
                'type'     => false,
                'isMobile' => false,
                'isTouch'  => false,
            ],

            'bot' => [
                'isBot' => false,
                'name'  => false,
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

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('');
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

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testParseNoResultFoundException()
    {
        $rawResult               = new stdClass();
        $rawResult->message_code = 'no_user_agent';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');
    }

    /**
     * usage_limit_exceeded
     *
     * @expectedException \UserAgentParser\Exception\LimitationExceededException
     */
    public function testGetResultLimitationExceededException()
    {
        $rawResult               = new stdClass();
        $rawResult->message_code = 'usage_limit_exceeded';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * no_api_user_key
     *
     * @expectedException \UserAgentParser\Exception\InvalidCredentialsException
     */
    public function testGetResultInvalidCredentialsExceptionNoKey()
    {
        $rawResult               = new stdClass();
        $rawResult->message_code = 'no_api_user_key';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * user_key_invalid
     *
     * @expectedException \UserAgentParser\Exception\InvalidCredentialsException
     */
    public function testGetResultInvalidCredentialsExceptionInvalidKey()
    {
        $rawResult               = new stdClass();
        $rawResult->message_code = 'user_key_invalid';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * unknown
     *
     * @expectedException \UserAgentParser\Exception\RequestException
     */
    public function testGetResultRequestExceptionUnknown()
    {
        $rawResult         = new stdClass();
        $rawResult->result = 'unknown';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * missing data
     *
     * @expectedException \UserAgentParser\Exception\RequestException
     */
    public function testGetResultRequestExceptionMissingData()
    {
        $rawResult         = new stdClass();
        $rawResult->result = 'success';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundException()
    {
        $rawResult         = new stdClass();
        $rawResult->result = 'success';
        $rawResult->parse  = new stdClass();

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * Browser only
     */
    public function testParseBrowser()
    {
        $parseResult                       = new stdClass();
        $parseResult->browser_name         = 'Firefox';
        $parseResult->browser_version_full = '3.2.1';

        $rawResult         = new stdClass();
        $rawResult->result = 'success';
        $rawResult->parse  = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

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
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * OS only
     */
    public function testParseOperatingSystem()
    {
        $parseResult                                = new stdClass();
        $parseResult->operating_system_name         = 'BlackBerryOS';
        $parseResult->operating_system_version_full = '6.0.0';

        $rawResult         = new stdClass();
        $rawResult->result = 'success';
        $rawResult->parse  = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new WhatIsMyBrowserCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name'    => 'BlackBerryOS',
                'version' => [
                    'major' => 6,
                    'minor' => 0,
                    'patch' => 0,

                    'alias' => null,

                    'complete' => '6.0.0',
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
