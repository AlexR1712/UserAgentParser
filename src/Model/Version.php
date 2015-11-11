<?php
namespace UserAgentParser\Model;

class Version
{
    private $major;

    private $minor;

    private $patch;

    private $complete;

    public function setMajor($major)
    {
        if ($major !== null) {
            $major = (int) $major;
        }

        $this->major = $major;

        $this->calculateComplete();
    }

    public function getMajor()
    {
        return $this->major;
    }

    public function setMinor($minor)
    {
        if ($minor !== null) {
            $minor = (int) $minor;
        }

        $this->minor = $minor;

        $this->calculateComplete();
    }

    public function getMinor()
    {
        return $this->minor;
    }

    public function setPatch($patch)
    {
        if ($patch !== null) {
            $patch = (int) $patch;
        }

        $this->patch = $patch;

        $this->calculateComplete();
    }

    public function getPatch()
    {
        return $this->patch;
    }

    /**
     * Set from the complete version string.
     *
     * @param string $complete
     */
    public function setComplete($complete)
    {
        $this->complete = $complete;

        $this->hydrateVersionParts($complete);
    }

    public function getComplete()
    {
        if ($this->complete === null) {
            $this->calculateComplete();
        }

        return $this->complete;
    }

    /**
     *
     * @param string $completeVersion
     */
    private function hydrateVersionParts($completeVersion)
    {
        $parts = $this->getParts($completeVersion);

        $this->setMajor($parts['major']);
        $this->setMinor($parts['minor']);
        $this->setPatch($parts['patch']);
    }

    /**
     *
     * @param unknown $version
     *
     * @return array
     */
    private function getParts($version)
    {
        $parts = explode('.', $version);

        $versionParts = [
            'major' => null,
            'minor' => null,
            'patch' => null,
        ];
        if (isset($parts[0]) && $parts[0] != '') {
            $versionParts['major'] = (int) $parts[0];
        }
        if (isset($parts[1]) && $parts[1] != '') {
            $versionParts['minor'] = (int) $parts[1];
        }
        if (isset($parts[2]) && $parts[2] != '') {
            $versionParts['patch'] = (int) $parts[2];
        }

        return $versionParts;
    }

    /**
     *
     * @return string
     */
    private function calculateComplete()
    {
        if ($this->getMajor() === null) {
            return;
        }

        $version = $this->getMajor();

        if ($this->getMinor() !== null) {
            $version .= '.' . $this->getMinor();
        }

        if ($this->getPatch() !== null) {
            $version .= '.' . $this->getPatch();
        }

        $this->complete = $version;
    }

    public function toArray()
    {
        return [
            'major' => $this->getMajor(),
            'minor' => $this->getMinor(),
            'patch' => $this->getPatch(),

            'complete' => $this->getComplete(),
        ];
    }
}
