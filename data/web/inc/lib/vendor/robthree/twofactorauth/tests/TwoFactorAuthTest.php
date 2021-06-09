<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use RobThree\Auth\TwoFactorAuthException;
use RobThree\Auth\TwoFactorAuth;

class TwoFactorAuthTest extends TestCase
{
    use MightNotMakeAssertions;

    /**
     * @return void
     */
    public function testConstructorThrowsOnInvalidDigits()
    {
        $this->expectException(TwoFactorAuthException::class);

        new TwoFactorAuth('Test', 0);
    }

    /**
     * @return void
     */
    public function testConstructorThrowsOnInvalidPeriod()
    {
        $this->expectException(TwoFactorAuthException::class);

        new TwoFactorAuth('Test', 6, 0);
    }

    /**
     * @return void
     */
    public function testConstructorThrowsOnInvalidAlgorithm()
    {
        $this->expectException(TwoFactorAuthException::class);

        new TwoFactorAuth('Test', 6, 30, 'xxx');
    }

    /**
     * @return void
     */
    public function testGetCodeReturnsCorrectResults()
    {
        $tfa = new TwoFactorAuth('Test');
        $this->assertEquals('543160', $tfa->getCode('VMR466AB62ZBOKHE', 1426847216));
        $this->assertEquals('538532', $tfa->getCode('VMR466AB62ZBOKHE', 0));
    }

    /**
     * @return void
     */
    public function testEnsureAllTimeProvidersReturnCorrectTime()
    {
        $tfa = new TwoFactorAuth('Test', 6, 30, 'sha1');
        $tfa->ensureCorrectTime(array(
            new \RobThree\Auth\Providers\Time\NTPTimeProvider(),                         // Uses pool.ntp.org by default
            //new \RobThree\Auth\Providers\Time\NTPTimeProvider('time.google.com'),      // Somehow time.google.com and time.windows.com make travis timeout??
            new \RobThree\Auth\Providers\Time\HttpTimeProvider(),                        // Uses google.com by default
            new \RobThree\Auth\Providers\Time\HttpTimeProvider('https://github.com'),
            new \RobThree\Auth\Providers\Time\HttpTimeProvider('https://yahoo.com'),
        ));
        $this->noAssertionsMade();
    }

    /**
     * @return void
     */
    public function testVerifyCodeWorksCorrectly()
    {
        $tfa = new TwoFactorAuth('Test', 6, 30);
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 1, 1426847190));
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 0, 1426847190 + 29));	//Test discrepancy
        $this->assertFalse($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 0, 1426847190 + 30));	//Test discrepancy
        $this->assertFalse($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 0, 1426847190 - 1));	//Test discrepancy

        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 1, 1426847205 + 0));	//Test discrepancy
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 1, 1426847205 + 35));	//Test discrepancy
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 1, 1426847205 - 35));	//Test discrepancy

        $this->assertFalse($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 1, 1426847205 + 65));	//Test discrepancy
        $this->assertFalse($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 1, 1426847205 - 65));	//Test discrepancy

        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 2, 1426847205 + 65));	//Test discrepancy
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 2, 1426847205 - 65));	//Test discrepancy
    }

    /**
     * @return void
     */
    public function testVerifyCorrectTimeSliceIsReturned()
    {
        $tfa = new TwoFactorAuth('Test', 6, 30);

        // We test with discrepancy 3 (so total of 7 codes: c-3, c-2, c-1, c, c+1, c+2, c+3
        // Ensure each corresponding timeslice is returned correctly
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '534113', 3, 1426847190, $timeslice1));
        $this->assertEquals(47561570, $timeslice1);
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '819652', 3, 1426847190, $timeslice2));
        $this->assertEquals(47561571, $timeslice2);
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '915954', 3, 1426847190, $timeslice3));
        $this->assertEquals(47561572, $timeslice3);
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 3, 1426847190, $timeslice4));
        $this->assertEquals(47561573, $timeslice4);
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '348401', 3, 1426847190, $timeslice5));
        $this->assertEquals(47561574, $timeslice5);
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '648525', 3, 1426847190, $timeslice6));
        $this->assertEquals(47561575, $timeslice6);
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '170645', 3, 1426847190, $timeslice7));
        $this->assertEquals(47561576, $timeslice7);

        // Incorrect code should return false and a 0 timeslice
        $this->assertFalse($tfa->verifyCode('VMR466AB62ZBOKHE', '111111', 3, 1426847190, $timeslice8));
        $this->assertEquals(0, $timeslice8);
    }

    /**
     * @return void
     */
    public function testGetCodeThrowsOnInvalidBase32String1()
    {
        $tfa = new TwoFactorAuth('Test');

        $this->expectException(TwoFactorAuthException::class);

        $tfa->getCode('FOO1BAR8BAZ9');    //1, 8 & 9 are invalid chars
    }

    /**
     * @return void
     */
    public function testGetCodeThrowsOnInvalidBase32String2()
    {
        $tfa = new TwoFactorAuth('Test');

        $this->expectException(TwoFactorAuthException::class);

        $tfa->getCode('mzxw6===');        //Lowercase
    }

    /**
     * @return void
     */
    public function testKnownBase32DecodeTestVectors()
    {
        // We usually don't test internals (e.g. privates) but since we rely heavily on base32 decoding and don't want
        // to expose this method nor do we want to give people the possibility of implementing / providing their own base32
        // decoding/decoder (as we do with Rng/QR providers for example) we simply test the private base32Decode() method
        // with some known testvectors **only** to ensure base32 decoding works correctly following RFC's so there won't
        // be any bugs hiding in there. We **could** 'fool' ourselves by calling the public getCode() method (which uses
        // base32decode internally) and then make sure getCode's output (in digits) equals expected output since that would
        // mean the base32Decode() works as expected but that **could** hide some subtle bug(s) in decoding the base32 string.

        // "In general, you don't want to break any encapsulation for the sake of testing (or as Mom used to say, "don't
        // expose your privates!"). Most of the time, you should be able to test a class by exercising its public methods."
        //                                                           Dave Thomas and Andy Hunt -- "Pragmatic Unit Testing
        $tfa = new TwoFactorAuth('Test');

        $method = new \ReflectionMethod(TwoFactorAuth::class, 'base32Decode');
        $method->setAccessible(true);

        // Test vectors from: https://tools.ietf.org/html/rfc4648#page-12
        $this->assertEquals('', $method->invoke($tfa, ''));
        $this->assertEquals('f', $method->invoke($tfa, 'MY======'));
        $this->assertEquals('fo', $method->invoke($tfa, 'MZXQ===='));
        $this->assertEquals('foo', $method->invoke($tfa, 'MZXW6==='));
        $this->assertEquals('foob', $method->invoke($tfa, 'MZXW6YQ='));
        $this->assertEquals('fooba', $method->invoke($tfa, 'MZXW6YTB'));
        $this->assertEquals('foobar', $method->invoke($tfa, 'MZXW6YTBOI======'));
    }

    /**
     * @return void
     */
    public function testKnownBase32DecodeUnpaddedTestVectors()
    {
        // See testKnownBase32DecodeTestVectors() for the rationale behind testing the private base32Decode() method.
        // This test ensures that strings without the padding-char ('=') are also decoded correctly.
        // https://tools.ietf.org/html/rfc4648#page-4:
        //   "In some circumstances, the use of padding ("=") in base-encoded data is not required or used."
        $tfa = new TwoFactorAuth('Test');

        $method = new \ReflectionMethod(TwoFactorAuth::class, 'base32Decode');
        $method->setAccessible(true);

        // Test vectors from: https://tools.ietf.org/html/rfc4648#page-12
        $this->assertEquals('', $method->invoke($tfa, ''));
        $this->assertEquals('f', $method->invoke($tfa, 'MY'));
        $this->assertEquals('fo', $method->invoke($tfa, 'MZXQ'));
        $this->assertEquals('foo', $method->invoke($tfa, 'MZXW6'));
        $this->assertEquals('foob', $method->invoke($tfa, 'MZXW6YQ'));
        $this->assertEquals('fooba', $method->invoke($tfa, 'MZXW6YTB'));
        $this->assertEquals('foobar', $method->invoke($tfa, 'MZXW6YTBOI'));
    }

    /**
     * @return void
     */
    public function testKnownTestVectors_sha1()
    {
        //Known test vectors for SHA1: https://tools.ietf.org/html/rfc6238#page-15
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';   //== base32encode('12345678901234567890')
        $tfa = new TwoFactorAuth('Test', 8, 30, 'sha1');
        $this->assertEquals('94287082', $tfa->getCode($secret, 59));
        $this->assertEquals('07081804', $tfa->getCode($secret, 1111111109));
        $this->assertEquals('14050471', $tfa->getCode($secret, 1111111111));
        $this->assertEquals('89005924', $tfa->getCode($secret, 1234567890));
        $this->assertEquals('69279037', $tfa->getCode($secret, 2000000000));
        $this->assertEquals('65353130', $tfa->getCode($secret, 20000000000));
    }

    /**
     * @return void
     */
    public function testKnownTestVectors_sha256()
    {
        //Known test vectors for SHA256: https://tools.ietf.org/html/rfc6238#page-15
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZA';   //== base32encode('12345678901234567890123456789012')
        $tfa = new TwoFactorAuth('Test', 8, 30, 'sha256');
        $this->assertEquals('46119246', $tfa->getCode($secret, 59));
        $this->assertEquals('68084774', $tfa->getCode($secret, 1111111109));
        $this->assertEquals('67062674', $tfa->getCode($secret, 1111111111));
        $this->assertEquals('91819424', $tfa->getCode($secret, 1234567890));
        $this->assertEquals('90698825', $tfa->getCode($secret, 2000000000));
        $this->assertEquals('77737706', $tfa->getCode($secret, 20000000000));
    }

    /**
     * @return void
     */
    public function testKnownTestVectors_sha512()
    {
        //Known test vectors for SHA512: https://tools.ietf.org/html/rfc6238#page-15
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNA';   //== base32encode('1234567890123456789012345678901234567890123456789012345678901234')
        $tfa = new TwoFactorAuth('Test', 8, 30, 'sha512');
        $this->assertEquals('90693936', $tfa->getCode($secret, 59));
        $this->assertEquals('25091201', $tfa->getCode($secret, 1111111109));
        $this->assertEquals('99943326', $tfa->getCode($secret, 1111111111));
        $this->assertEquals('93441116', $tfa->getCode($secret, 1234567890));
        $this->assertEquals('38618901', $tfa->getCode($secret, 2000000000));
        $this->assertEquals('47863826', $tfa->getCode($secret, 20000000000));
    }
}
