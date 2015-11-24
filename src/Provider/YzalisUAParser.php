<?php
namespace UserAgentParser\Provider;

use UAParser\Result as UAResult;
use UAParser\Result\Result as UAParserResult;
use UserAgentParser\Exception;
use UserAgentParser\Model;

class YzalisUAParser extends AbstractProvider
{
    protected $defaultValues = [
        'Other',
    ];

    private $parser;

    public function getName()
    {
        return 'YzalisUAParser';
    }

    public function getComposerPackageName()
    {
        return 'yzalis/ua-parser';
    }

    /**
     *
     * @param \UAParser\UAParser $parser
     */
    public function setParser(\UAParser\UAParser $parser = null)
    {
        $this->parser = $parser;
    }

    /**
     *
     * @return \UAParser\UAParser
     */
    public function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        $this->parser = new \UAParser\UAParser();

        return $this->parser;
    }

    /**
     *
     * @param UAParserResult $resultRaw
     *
     * @return bool
     */
    private function hasResult(UAParserResult $resultRaw)
    {
        /* @var $browserRaw \UAParser\Result\BrowserResult */
        $browserRaw = $resultRaw->getBrowser();

        if ($browserRaw !== null && $this->isRealResult($browserRaw->getFamily()) === true) {
            return true;
        }

        /* @var $osRaw \UAParser\Result\OperatingSystemResult */
        $osRaw = $resultRaw->getOperatingSystem();

        if ($osRaw !== null && $this->isRealResult($osRaw->getFamily()) === true) {
            return true;
        }

        /* @var $deviceRaw \UAParser\Result\DeviceResult */
        $deviceRaw = $resultRaw->getDevice();

        if ($deviceRaw !== null && $this->isRealResult($deviceRaw->getConstructor()) === true) {
            return true;
        }

        if ($deviceRaw !== null && $this->isRealResult($deviceRaw->getModel()) === true) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param Model\Browser          $browser
     * @param UAResult\BrowserResult $browserRaw
     */
    private function hydrateBrowser(Model\Browser $browser, UAResult\BrowserResult $browserRaw)
    {
        if ($this->isRealResult($browserRaw->getFamily()) === true) {
            $browser->setName($browserRaw->getFamily());
        }

        if ($this->isRealResult($browserRaw->getVersionString()) === true) {
            $browser->getVersion()->setComplete($browserRaw->getVersionString());
        }
    }

    /**
     *
     * @param Model\RenderingEngine          $engine
     * @param UAResult\RenderingEngineResult $renderingEngineRaw
     */
    private function hydrateRenderingEngine(Model\RenderingEngine $engine, UAResult\RenderingEngineResult $renderingEngineRaw)
    {
        if ($this->isRealResult($renderingEngineRaw->getFamily()) === true) {
            $engine->setName($renderingEngineRaw->getFamily());
        }

        if ($this->isRealResult($renderingEngineRaw->getVersion()) === true) {
            $engine->getVersion()->setComplete($renderingEngineRaw->getVersion());
        }
    }

    /**
     *
     * @param Model\OperatingSystem          $os
     * @param UAResult\OperatingSystemResult $resultRaw
     */
    private function hydrateOperatingSystem(Model\OperatingSystem $os, UAResult\OperatingSystemResult $osRaw)
    {
        if ($this->isRealResult($osRaw->getFamily()) === true) {
            $os->setName($osRaw->getFamily());
        }

        if ($this->isRealResult($osRaw->getMajor()) === true) {
            $os->getVersion()->setMajor($osRaw->getMajor());

            if ($this->isRealResult($osRaw->getMinor()) === true) {
                $os->getVersion()->setMinor($osRaw->getMinor());
            }

            if ($this->isRealResult($osRaw->getPatch()) === true) {
                $os->getVersion()->setPatch($osRaw->getPatch());
            }
        }
    }

    /**
     *
     * @param Model\UserAgent       $device
     * @param UAResult\DeviceResult $deviceRaw
     */
    private function hydrateDevice(Model\Device $device, UAResult\DeviceResult $deviceRaw)
    {
        if ($this->isRealResult($deviceRaw->getModel()) === true) {
            $device->setModel($deviceRaw->getModel());
        }

        if ($this->isRealResult($deviceRaw->getConstructor()) === true) {
            $device->setBrand($deviceRaw->getConstructor());
        }

        // removed desktop type, since it's a default value and not really detected
        if ($this->isRealResult($deviceRaw->getType()) === true && $deviceRaw->getType() !== 'desktop') {
            $device->setType($deviceRaw->getType());
        }

        if ($this->isMobile($deviceRaw) === true) {
            $device->setIsMobile(true);
        }
    }

    /**
     *
     * @param  UAResult\DeviceResult $deviceRaw
     * @return bool
     */
    private function isMobile(UAResult\DeviceResult $deviceRaw)
    {
        if ($deviceRaw->getType() === 'mobile') {
            return true;
        }

        if ($deviceRaw->getType() === 'tablet') {
            return true;
        }

        return false;
    }

    public function parse($userAgent, array $headers = [])
    {
        $parser = $this->getParser();

        /* @var $resultRaw \UAParser\Result\Result */
        $resultRaw = $parser->parse($userAgent);

        /*
         * No result found?
         */
        if ($this->hasResult($resultRaw) !== true) {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /* @var $emailRaw \UAParser\Result\EmailClientResult */
        // currently not used...any idea to implement it?

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw($resultRaw);

        /*
         * Bot detection is currently not possible
         */

        $this->hydrateBrowser($result->getBrowser(), $resultRaw->getBrowser());
        $this->hydrateRenderingEngine($result->getRenderingEngine(), $resultRaw->getRenderingEngine());
        $this->hydrateOperatingSystem($result->getOperatingSystem(), $resultRaw->getOperatingSystem());
        $this->hydrateDevice($result->getDevice(), $resultRaw->getDevice());

        return $result;
    }
}
