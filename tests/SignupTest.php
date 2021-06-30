<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class SignupTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSignup()
    {
        $data = [
            'invited_by' => 5279184141,
            'firstname' => 'John',
            'lastname'  => 'Age',
            'country'   => 'NG',
            'phone'     => '09149394943',
            'email'     => 'john@vscompany.com',
            'pass'      => 'johnthemayor'
        ];

        $this->post("/auth/signup", $data);

        $this->seeStatusCode(200);
        $this->seeJsonStructure(['status', 'message', 'data']);
    }
}
