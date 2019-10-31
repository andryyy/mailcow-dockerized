<?php

use MatthiasMullie\Minify\CSS;

class CSSminifierExtended extends CSS {

    public function getDataHash() {
        return sha1(json_encode($this->accessProtected($this,'data')));
    }

    private function accessProtected($obj, $prop) {
        $reflection = new ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }

}