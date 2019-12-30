<?php

namespace App\Console;

use App\Helpers\Password;
use App\Model\ClientModel;
use App\Model\UserModel;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Class Migrations
 * @package App\Console
 * @author Jerfeson Guerreiro <jerfeson_guerreiro@hotmail.com>
 */
class Migrations extends Command
{

    public function migrate()
    {
        $connection = DB::connection('default');
        $schema = $connection->getSchemaBuilder();

        //oauth_scope
        $schema->dropIfExists("oauth_scope");
        $schema->dropIfExists("oauth_refresh_token");
        $schema->dropIfExists("oauth_access_token");
        $schema->dropIfExists("user");
        $schema->dropIfExists("user_profile");
        $schema->dropIfExists("oauth_auth_code");
        $schema->dropIfExists("oauth_session");
        $schema->dropIfExists("client");
        $schema->dropIfExists("oauth_client");


        $schema->create("oauth_scope", function ($table) {
            $table->increments('id')->unsigned();
            $table->string('description', 255);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        //oauth_client
        $schema->create("oauth_client", function ($table) {
            $table->increments('id')->unsigned();
            $table->string('client_secret', 255);
            $table->string('client_id', 255);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        //oauth_session
        $schema->create("oauth_session", function ($table) {
            $table->increments('id')->unsigned();
            $table->integer('oauth_client_id')->unsigned();
            $table->foreign('oauth_client_id')->references('id')->on('oauth_client');
            $table->string('owner_type', 255);
            $table->string('owner_id', 255);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        //oauth_auth_code
        $schema->create("oauth_auth_code", function ($table) {
            $table->increments('id')->unsigned();
            $table->integer('oauth_session_id')->unsigned();
            $table->foreign('oauth_session_id')->references('id')->on('oauth_session');
            $table->integer('expire_time')->nullable();
            $table->string('client_redirect_id', 255);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });


        //user
        $schema->create("user_profile", function ($table) {
            $table->increments('id')->unsigned();
            $table->string('name', 45);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });


        //client
        $schema->create("client", function ($table) {
            $table->increments('id')->unsigned();

            //FK
            $table->integer('oauth_client_id')->unsigned();
            $table->foreign('oauth_client_id')->references('id')->on('oauth_client');
            $table->string('name', 45);
            $table->tinyInteger('status');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });


        //user
        $schema->create("user", function ($table) {
            $table->increments('id')->unsigned();

            //FK
            $table->integer('user_profile_id')->unsigned();
            $table->foreign('user_profile_id')->references('id')->on('user_profile');
            $table->integer('client_id')->unsigned();
            $table->foreign('client_id')->references('id')->on('client');

            $table->string('username', 45);
            $table->string('password', 255);
            $table->tinyInteger('status');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });



        //oauth_access_token
        $schema->create("oauth_access_token", function ($table) {
            $table->increments('id')->unsigned();
            $table->string('oauth_client_id', 255);

            //FK
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('user');

            $table->string('access_token', 255);
            $table->dateTime('expiry_date_time');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        //oauth_access_token
        $schema->create("oauth_refresh_token", function ($table) {
            $table->increments('id')->unsigned();

            //FK
            $table->integer('oauth_access_token_id')->unsigned();
            $table->foreign('oauth_access_token_id')->references('id')->on('oauth_access_token');

            $table->integer('expire_time')->nullable();
            $table->string('refresh_token', 255);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        $this->insertData($connection);
    }

    private function insertData($connection)
    {
        $date = new \DateTime();
        $nameClient = "Administration";

        $connection->table('user_profile')->insert([
            'name' => 'Administrator',
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        $connection->table('oauth_client')->insert([
            'client_secret' => Password::hash($nameClient), //  Define secret pattern
            'client_id' => base64_encode($nameClient), //  Define id pattern
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        $connection->table('client')->insert([
            'name' => $nameClient,
            'oauth_client_id' => 1,
            'status' => ClientModel::STATUS_ACTIVE,
            'created_at' => $date,
            'updated_at' => $date,
        ]);


        $connection->table('user')->insert([
            'user_profile_id' => 1,
            'client_id' => 1,
            'username' => 'admin',
            'password' => Password::hash('admin'),
            'status' => UserModel::STATUS_ACTIVE,
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }
}