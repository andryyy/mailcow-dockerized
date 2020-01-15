<?php

class IndentTest extends PHPUnit_Framework_TestCase {

    protected $Y;

    protected function setUp() {
        $this->Y = Spyc::YAMLLoad(__DIR__."/indent_1.yaml");
    }

    public function testIndent_1() {
        $this->assertEquals (array ('child_1' => 2, 'child_2' => 0, 'child_3' => 1), $this->Y['root']);
    }

    public function testIndent_2() {
        $this->assertEquals (array ('child_1' => 1, 'child_2' => 2), $this->Y['root2']);
    }

    public function testIndent_3() {
        $this->assertEquals (array (array ('resolutions' => array (1024 => 768, 1920 => 1200), 'producer' => 'Nec')), $this->Y['display']);
    }

    public function testIndent_4() {
        $this->assertEquals (array (
            array ('resolutions' => array (1024 => 768)),
            array ('resolutions' => array (1920 => 1200)),
        ), $this->Y['displays']);
    }

    public function testIndent_5() {
        $this->assertEquals (array (array (
            'row' => 0,
            'col' => 0,
            'headsets_affected' => array (
                array (
                    'ports' => array (0),
                    'side' => 'left',
                )
            ),
            'switch_function' => array (
                'ics_ptt' => true
            )
        )), $this->Y['nested_hashes_and_seqs']);
    }

    public function testIndent_6() {
        $this->assertEquals (array (
            'h' => array (
                array ('a' => 'b', 'a1' => 'b1'),
                array ('c' => 'd')
            )
        ), $this->Y['easier_nest']);
    }

    public function testIndent_space() {
        $this->assertEquals ("By four\n  spaces", $this->Y['one_space']);
    }

    public function testListAndComment() {
        $this->assertEquals (array ('one', 'two', 'three'), $this->Y['list_and_comment']);
    }

    public function testAnchorAndAlias() {
        $this->assertEquals (array ('database' => 'rails_dev', 'adapter' => 'mysql', 'host' => 'localhost'), $this->Y['development']);
        $this->assertEquals (array (1 => 'abc'), $this->Y['zzz']);
    }

}
