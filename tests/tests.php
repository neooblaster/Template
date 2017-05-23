<?php
class Test extends PHPUnit_Framework_TestCase
{
    public function testOne() {
        $this->assertSame(2, MaFonction());
        $this->assertSame(2, MaFonction(2));
    }
}


function MaFonction($arg=null){
    if(!is_null($arg)){
        return $arg;
    } else {
        return 2;
    }
}