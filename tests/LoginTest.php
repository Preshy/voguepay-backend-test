<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class LoginTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testLogin()
    {
        $data = [
            'email'     => 'precious@vesicash.com',
            'pass'      => 'admin'
        ];

        $this->post("/auth/login", $data);
        
        $this->seeStatusCode(200);
        $this->seeJsonStructure(['status', 'message', 'data']);
    }
}
