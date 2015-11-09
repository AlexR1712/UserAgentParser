<?php
namespace UserAgentParser\Provider;

use UserAgentParser\Exception;
use UserAgentParser\Model;
use Woothee\Classifier;
use Woothee\DataSet;

class Woothee extends AbstractProvider
{
    private $parser;

    public function getName()
    {
        return 'Woothee';
    }

    /**
     *
     * @return \Woothee\Classifier
     */
    private function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        $parser = new Classifier();

        $this->parser = $parser;

        return $this->parser;
    }

    /**
     *
     * @param array $resultRaw
     *
     * @return bool
     */
    private function hasResult(array $resultRaw)
    {
        foreach ($resultRaw as $value) {
            if ($value !== DataSet::VALUE_UNKNOWN) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @param  array   $resultRaw
     * @return boolean
     */
    private function isBot(array $resultRaw)
    {
        if ($resultRaw['category'] === DataSet::DATASET_CATEGORY_CRAWLER) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param mixed $value
     *
     * @return bool
     */
    private function isRealResult($value)
    {
        if ($value === DataSet::VALUE_UNKNOWN) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param array $resultRaw
     *
     * @return bool
     */
    private function isMobile(array $resultRaw)
    {
        /*
         * Available types...
         *
         * const DATASET_CATEGORY_PC = 'pc';
         * const DATASET_CATEGORY_SMARTPHONE = 'smartphone';
         * const DATASET_CATEGORY_MOBILEPHONE = 'mobilephone';
         * const DATASET_CATEGORY_CRAWLER = 'crawler';
         * const DATASET_CATEGORY_APPLIANCE = 'appliance';
         * const DATASET_CATEGORY_MISC = 'misc';
         */

        if ($resultRaw['category'] === DataSet::DATASET_CATEGORY_SMARTPHONE) {
            return true;
        }

        if ($resultRaw['category'] === DataSet::DATASET_CATEGORY_MOBILEPHONE) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param array $resultRaw
     *
     * @return bool
     */
    private function isTouch(array $resultRaw)
    {
        if ($resultRaw['category'] === DataSet::DATASET_CATEGORY_SMARTPHONE) {
            return true;
        }

        return false;
    }

    public function parse($userAgent)
    {
        $parser = $this->getParser();

        $resultRaw = $parser->parse($userAgent);

        /*
         * No result found?
         */
        if ($this->hasResult($resultRaw) !== true) {
            throw new Exception\NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent();
        $result->setProviderResultRaw($resultRaw);

        /*
         * Bot detection
         */
        if ($this->isBot($resultRaw) === true) {
            $bot = $result->getBot();
            $bot->setIsBot(true);

            if ($this->isRealResult($resultRaw['name']) === true) {
                $bot->setName($resultRaw['name']);
            }

            return $result;
        }

        /*
         * Browser
         */
        $browser = $result->getBrowser();

        if ($this->isRealResult($resultRaw['name']) === true) {
            $browser->setName($resultRaw['name']);
        }

        if ($this->isRealResult($resultRaw['version']) === true) {
            $browser->getVersion()->setComplete($resultRaw['version']);
        }

        /*
         * renderingEngine
         */
        $renderingEngine = $result->getRenderingEngine();

        /*
         * operatingSystem
         */
        $operatingSystem = $result->getOperatingSystem();

        // @todo ... filled OS is mixed! Examples: iPod, iPhone, Android...
        // split it by hand for device/OS?

        if ($this->isRealResult($resultRaw['os_version']) === true) {
            $operatingSystem->getVersion()->setComplete($resultRaw['os_version']);
        }

        /*
         * device
         */
        $device = $result->getDevice();

        // @todo ... filled OS is mixed! Examples: iPod, iPhone, Android...
        // @todo vendor is filled with device and/or browser

        $device->setIsMobile($this->isMobile($resultRaw));
        $device->setIsTouch($this->isTouch($resultRaw));

        return $result;
    }
}
