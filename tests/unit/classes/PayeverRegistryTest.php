<?php

class PayeverRegistryTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSet()
    {
        $product = new \stdClass();
        PayeverRegistry::set(PayeverRegistry::LAST_INWARD_PROCESSED_PRODUCT, $product);
        $this->assertSame($product, PayeverRegistry::get(PayeverRegistry::LAST_INWARD_PROCESSED_PRODUCT));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetSetCaseException()
    {
        PayeverRegistry::set('unexpected_key', true);
    }
}
