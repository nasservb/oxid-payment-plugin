<?php

class PayeverRequestHelperTest extends \PHPUnit\Framework\TestCase
{
    /** @var PayeverRequestHelper */
    protected $helper;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->helper = new PayeverRequestHelper();
    }

    public function testGetQueryData()
    {
        $this->assertEmpty($this->helper->getQueryData());
    }

    public function testGetServer()
    {
        $this->assertNotEmpty($this->helper->getServer());
    }

    public function testGetRequestContent()
    {
        $this->assertEmpty($this->helper->getRequestContent());
    }

    public function testGetHeader()
    {
        $_SERVER['HTTP_X_PayEver_SigNaTure'] = $signature = 'some-signature';
        $this->assertEquals($signature, $this->helper->getHeader('X-PAYEVER-SIGNATURE'));
        unset($_SERVER['HTTP_X_PayEver_SigNaTure']);
    }
}
