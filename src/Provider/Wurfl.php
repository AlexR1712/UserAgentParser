<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception;
use UserAgentParser\Model;
use Wurfl\CustomDevice;
use Wurfl\Manager as WurflManager;

class Wurfl extends AbstractProvider
{
    /**
     *
     * @var WurflManager
     */
    private $parser;

    /**
     *
     * @param WurflManager $parser
     */
    public function __construct(WurflManager $parser)
    {
        $this->setParser($parser);
    }

    public function getName()
    {
        return 'Wurfl';
    }

    public function getComposerPackageName()
    {
        return 'mimmi20/wurfl';
    }

    public function getVersion()
    {
        return $this->getParser()->getWurflInfo()->version;
    }

    /**
     *
     * @param WurflManager $parser
     */
    public function setParser(WurflManager $parser)
    {
        $this->parser = $parser;
    }

    /**
     *
     * @return WurflManager
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private function isRealBrand($value)
    {
        if ($value === '' || $value === null) {
            return false;
        }

        if ($value == 'Generic') {
            return false;
        }

        return true;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private function isRealModel($value)
    {
        if ($value === '' || $value === null) {
            return false;
        }

        if (substr((string) $value, 0, 7) == 'Android') {
            return false;
        }

        if (substr((string) $value, 0, 7) == 'Firefox') {
            return false;
        }

        if (substr((string) $value, 0, 7) == 'Generic') {
            return false;
        }

        if (substr((string) $value, 0, 12) == 'unrecognized') {
            return false;
        }

        if (substr((string) $value, 0, 14) == 'Windows Mobile') {
            return false;
        }

        if (substr((string) $value, 0, 13) == 'Windows Phone') {
            return false;
        }

        if (substr((string) $value, 0, 10) == 'Windows RT') {
            return false;
        }

        return true;
    }

    /**
     *
     * @param  CustomDevice $device
     * @return boolean
     */
    private function hasResult(CustomDevice $device)
    {
        if ($device->id !== null && $device->id != '' && $device->id !== 'generic') {
            return true;
        }

        return false;
    }

    public function parse($userAgent, array $headers = [])
    {
        $parser = $this->getParser();

        $deviceRaw = $parser->getDeviceForUserAgent($userAgent);

        /*
         * No result found?
         */
        if ($this->hasResult($deviceRaw) !== true) {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw([
            'virtual' => $deviceRaw->getAllVirtualCapabilities(),
            'all'     => $deviceRaw->getAllCapabilities(),
        ]);

        /*
         * Bot detection
         */
        if ($deviceRaw->getVirtualCapability('is_robot') === 'true') {
            $bot = $result->getBot();
            $bot->setIsBot(true);

            // brand_name seems to be always google, so dont use it

            return $result;
        }

        /*
         * browser
         */
        $browser = $result->getBrowser();

        $browser->setName($deviceRaw->getVirtualCapability('advertised_browser'));
        $browser->getVersion()->setComplete($deviceRaw->getVirtualCapability('advertised_browser_version'));

        /*
         * operatingSystem
         */
        $operatingSystem = $result->getOperatingSystem();

        $operatingSystem->setName($deviceRaw->getVirtualCapability('advertised_device_os'));
        $operatingSystem->getVersion()->setComplete($deviceRaw->getVirtualCapability('advertised_device_os_version'));

        /*
         * device
         */
        $device = $result->getDevice();

        if ($deviceRaw->getVirtualCapability('is_full_desktop') !== 'true') {
            if ($this->isRealModel($deviceRaw->getCapability('model_name')) === true) {
                $device->setModel($deviceRaw->getCapability('model_name'));
            }

            if ($this->isRealBrand($deviceRaw->getCapability('brand_name')) === true) {
                $device->setBrand($deviceRaw->getCapability('brand_name'));
            }

            if ($deviceRaw->getVirtualCapability('is_mobile') === 'true') {
                $device->setIsMobile(true);
            }

            if ($deviceRaw->getVirtualCapability('is_touchscreen') === 'true') {
                $device->setIsTouch(true);
            }
        }

        // @see the list of all types http://web.wurfl.io/
        $device->setType($deviceRaw->getVirtualCapability('form_factor'));

        return $result;
    }
}
