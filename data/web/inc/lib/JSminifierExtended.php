<?php

use MatthiasMullie\Minify\JS;

class JSminifierExtended extends JS {

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