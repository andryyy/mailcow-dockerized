<?php

namespace Tests;

trait MightNotMakeAssertions
{
    /**
     * This is a shim to support PHPUnit for php 5.6 and 7.0.
     *
     * It has to be named something that doesn't collide with existing
     * TestCase methods as we can't support PHP return types right now
     *
     * @return void
     */
    public function noAssertionsMade()
    {
        foreach (class_parents($this) as $parent) {
            if (method_exists($parent, 'expectNotToPerformAssertions')) {
                parent::expectNotToPerformAssertions();
                return;
            }
        }

        $this->assertTrue(true);
    }
}
