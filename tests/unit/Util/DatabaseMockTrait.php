<?php

trait DatabaseMockTrait
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseInterface|oxLegacyDb */
    protected $database;

    /**
     * Builds database mock for different versions of oxid
     */
    protected function buildDatabaseMock()
    {
        if (interface_exists('OxidEsales\EshopCommunity\Core\Database\Adapter\DatabaseInterface', false)) {
            $this->database = $this->getMockBuilder(
                OxidEsales\EshopCommunity\Core\Database\Adapter\DatabaseInterface::class
            )
                ->disableOriginalConstructor()
                ->getMock();
        } else {
            $this->database = $this->getMockBuilder(oxLegacyDb::class)
                ->disableOriginalConstructor()
                ->getMock();
        }
    }
}
