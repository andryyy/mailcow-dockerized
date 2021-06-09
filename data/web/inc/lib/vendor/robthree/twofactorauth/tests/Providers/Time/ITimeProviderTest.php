<?php

namespace Tests\Providers\Time;

use PHPUnit\Framework\TestCase;
use Tests\MightNotMakeAssertions;
use RobThree\Auth\TwoFactorAuthException;
use RobThree\Auth\TwoFactorAuth;

class ITimeProviderTest extends TestCase
{
    use MightNotMakeAssertions;

    /**
     * @return void
     */
    public function testEnsureCorrectTimeDoesNotThrowForCorrectTime()
    {
        $tpr1 = new TestTimeProvider(123);
        $tpr2 = new TestTimeProvider(128);

        $tfa = new TwoFactorAuth('Test', 6, 30, 'sha1', null, null, $tpr1);
        $tfa->ensureCorrectTime(array($tpr2));   // 128 - 123 = 5 => within default leniency

        $this->noAssertionsMade();
    }

    /**
     * @return void
     */
    public function testEnsureCorrectTimeThrowsOnIncorrectTime()
    {
        $tpr1 = new TestTimeProvider(123);
        $tpr2 = new TestTimeProvider(124);

        $tfa = new TwoFactorAuth('Test', 6, 30, 'sha1', null, null, $tpr1);

        $this->expectException(TwoFactorAuthException::class);

        $tfa->ensureCorrectTime(array($tpr2), 0);    // We force a leniency of 0, 124-123 = 1 so this should throw
    }

    /**
     * @return void
     */
    public function testEnsureDefaultTimeProviderReturnsCorrectTime()
    {
        $tfa = new TwoFactorAuth('Test', 6, 30, 'sha1');
        $tfa->ensureCorrectTime(array(new TestTimeProvider(time())), 1);    // Use a leniency of 1, should the time change between both time() calls

        $this->noAssertionsMade();
    }
}
