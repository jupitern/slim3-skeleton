<?php
/**
 * Created by PhpStorm.
 * User: Jerfeson Guerreiro
 * Date: 26/11/18
 * Time: 20:56
 */

namespace App\Console;


class Jobby extends Command
{
    
    public function init()
    {
        try {
            $this->test();
        } catch (\Exception $e) {
        
        }
        
        $this->jobby->run();
    }
    
    /**
     * @throws \Jobby\Exception
     */
    private function test()
    {
        
        $this->jobby->add('CommandExample', [
            'closure' => function () {
                $fp = fopen('data.txt', 'w');
                fwrite($fp, 'Working');
                fclose($fp);
                return true;
            },
            'schedule' => '* * * * *',
        ]);
        
    }
}