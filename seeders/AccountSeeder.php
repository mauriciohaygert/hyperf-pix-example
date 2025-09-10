<?php

declare(strict_types=1);

use Hyperf\Database\Seeders\Seeder;
use Hyperf\DbConnection\Db;
use Ramsey\Uuid\Uuid;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $accounts = [
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'João Silva',
                'balance' => 1500.75,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'Maria Santos',
                'balance' => 2300.50,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'Pedro Oliveira',
                'balance' => 850.25,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'Ana Costa',
                'balance' => 3200.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($accounts as $account) {
            Db::table('account')->insert($account);
        }
    }
}
