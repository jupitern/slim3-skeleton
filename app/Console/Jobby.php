<?php

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
                $fp = fopen('storage\\data.txt', 'a');
                fwrite($fp, 'Working');
                fclose($fp);
                return true;
            },
            'schedule' => '* * * * *',
        ]);
        
    }
}