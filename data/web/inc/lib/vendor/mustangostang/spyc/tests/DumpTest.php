<?php

class DumpTest extends PHPUnit_Framework_TestCase {

    private $files_to_test = array();

    public function setUp() {
      $this->files_to_test = array (__DIR__.'/../spyc.yaml', 'failing1.yaml', 'indent_1.yaml', 'quotes.yaml');
    }

    public function testShortSyntax() {
      $dump = spyc_dump(array ('item1', 'item2', 'item3'));
      $awaiting = "- item1\n- item2\n- item3\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testDump() {
      foreach ($this->files_to_test as $file) {
        $yaml = spyc_load(file_get_contents($file));
        $dump = Spyc::YAMLDump ($yaml);
        $yaml_after_dump = Spyc::YAMLLoad ($dump);
        $this->assertEquals ($yaml, $yaml_after_dump);
      }
    }

    public function testDumpWithQuotes() {
      $Spyc = new Spyc();
      $Spyc->setting_dump_force_quotes = true;
      foreach ($this->files_to_test as $file) {
        $yaml = $Spyc->load(file_get_contents($file));
        $dump = $Spyc->dump ($yaml);
        $yaml_after_dump = Spyc::YAMLLoad ($dump);
        $this->assertEquals ($yaml, $yaml_after_dump);
      }
    }

    public function testDumpArrays() {
      $dump = Spyc::YAMLDump(array ('item1', 'item2', 'item3'));
      $awaiting = "---\n- item1\n- item2\n- item3\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testNull() {
        $dump = Spyc::YAMLDump(array('a' => 1, 'b' => null, 'c' => 3));
        $awaiting = "---\na: 1\nb: null\nc: 3\n";
        $this->assertEquals ($awaiting, $dump);
    }

    public function testNext() {
        $array = array("aaa", "bbb", "ccc");
        #set arrays internal pointer to next element
        next($array);
        $dump = Spyc::YAMLDump($array);
        $awaiting = "---\n- aaa\n- bbb\n- ccc\n";
        $this->assertEquals ($awaiting, $dump);
    }

    public function testDumpingMixedArrays() {
        $array = array();
        $array[] = 'Sequence item';
        $array['The Key'] = 'Mapped value';
        $array[] = array('A sequence','of a sequence');
        $array[] = array('first' => 'A sequence','second' => 'of mapped values');
        $array['Mapped'] = array('A sequence','which is mapped');
        $array['A Note'] = 'What if your text is too long?';
        $array['Another Note'] = 'If that is the case, the dumper will probably fold your text by using a block.  Kinda like this.';
        $array['The trick?'] = 'The trick is that we overrode the default indent, 2, to 4 and the default wordwrap, 40, to 60.';
        $array['Old Dog'] = "And if you want\n to preserve line breaks, \ngo ahead!";
        $array['key:withcolon'] = "Should support this to";

        $yaml = Spyc::YAMLDump($array,4,60);
    }

    public function testMixed() {
        $dump = Spyc::YAMLDump(array(0 => 1, 'b' => 2, 1 => 3));
        $awaiting = "---\n0: 1\nb: 2\n1: 3\n";
        $this->assertEquals ($awaiting, $dump);
    }

    public function testDumpNumerics() {
      $dump = Spyc::YAMLDump(array ('404', '405', '500'));
      $awaiting = "---\n- \"404\"\n- \"405\"\n- \"500\"\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testDumpAsterisks() {
      $dump = Spyc::YAMLDump(array ('*'));
      $awaiting = "---\n- '*'\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testDumpAmpersands() {
      $dump = Spyc::YAMLDump(array ('some' => '&foo'));
      $awaiting = "---\nsome: '&foo'\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testDumpExclamations() {
      $dump = Spyc::YAMLDump(array ('some' => '!foo'));
      $awaiting = "---\nsome: '!foo'\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testDumpExclamations2() {
      $dump = Spyc::YAMLDump(array ('some' => 'foo!'));
      $awaiting = "---\nsome: foo!\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testDumpApostrophes() {
      $dump = Spyc::YAMLDump(array ('some' => "'Biz' pimpt bedrijventerreinen"));
      $awaiting = "---\nsome: \"'Biz' pimpt bedrijventerreinen\"\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testDumpNumericHashes() {
      $dump = Spyc::YAMLDump(array ("titel"=> array("0" => "", 1 => "Dr.", 5 => "Prof.", 6 => "Prof. Dr.")));
      $awaiting = "---\ntitel:\n  0: \"\"\n  1: Dr.\n  5: Prof.\n  6: Prof. Dr.\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testEmpty() {
      $dump = Spyc::YAMLDump(array("foo" => array()));
      $awaiting = "---\nfoo: [ ]\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testHashesInKeys() {
      $dump = Spyc::YAMLDump(array ('#color' => '#ffffff'));
      $awaiting = "---\n\"#color\": '#ffffff'\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testParagraph() {
      $dump = Spyc::YAMLDump(array ('key' => "|\n  value"));
      $awaiting = "---\nkey: |\n  value\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testParagraphTwo() {
      $dump = Spyc::YAMLDump(array ('key' => 'Congrats, pimpt bedrijventerreinen pimpt bedrijventerreinen pimpt bedrijventerreinen!'));
      $awaiting = "---\nkey: >\n  Congrats, pimpt bedrijventerreinen pimpt\n  bedrijventerreinen pimpt\n  bedrijventerreinen!\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testString() {
      $dump = Spyc::YAMLDump(array ('key' => array('key_one' => 'Congrats, pimpt bedrijventerreinen!')));
      $awaiting = "---\nkey:\n  key_one: Congrats, pimpt bedrijventerreinen!\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testStringLong() {
      $dump = Spyc::YAMLDump(array ('key' => array('key_one' => 'Congrats, pimpt bedrijventerreinen pimpt bedrijventerreinen pimpt bedrijventerreinen!')));
      $awaiting = "---\nkey:\n  key_one: >\n    Congrats, pimpt bedrijventerreinen pimpt\n    bedrijventerreinen pimpt\n    bedrijventerreinen!\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testStringDoubleQuote() {
      $dump = Spyc::YAMLDump(array ('key' => array('key_one' =>  array('key_two' => '"Système d\'e-réservation"'))));
      $awaiting = "---\nkey:\n  key_one:\n    key_two: |\n      Système d'e-réservation\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testLongStringDoubleQuote() {
      $dump = Spyc::YAMLDump(array ('key' => array('key_one' =>  array('key_two' => '"Système d\'e-réservation bedrijventerreinen pimpt" bedrijventerreinen!'))));
      $awaiting = "---\nkey:\n  key_one:\n    key_two: |\n      \"Système d'e-réservation bedrijventerreinen pimpt\" bedrijventerreinen!\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testStringStartingWithSpace() {
      $dump = Spyc::YAMLDump(array ('key' => array('key_one' => "    Congrats, pimpt bedrijventerreinen \n    pimpt bedrijventerreinen pimpt bedrijventerreinen!")));
      $awaiting = "---\nkey:\n  key_one: |\n    Congrats, pimpt bedrijventerreinen\n    pimpt bedrijventerreinen pimpt bedrijventerreinen!\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testPerCentOne() {
      $dump = Spyc::YAMLDump(array ('key' => "%name%, pimpts bedrijventerreinen!"));
      $awaiting = "---\nkey: '%name%, pimpts bedrijventerreinen!'\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testPerCentAndSimpleQuote() {
      $dump = Spyc::YAMLDump(array ('key' => "%name%, pimpt's bedrijventerreinen!"));
      $awaiting = "---\nkey: \"%name%, pimpt's bedrijventerreinen!\"\n";
      $this->assertEquals ($awaiting, $dump);
    }

    public function testPerCentAndDoubleQuote() {
      $dump = Spyc::YAMLDump(array ('key' => '%name%, pimpt\'s "bed"rijventerreinen!'));
      $awaiting = "---\nkey: |\n  %name%, pimpt's \"bed\"rijventerreinen!\n";
      $this->assertEquals ($awaiting, $dump);
    }

}
