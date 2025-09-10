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
        $existingAccounts = Db::table('account')->count();
        if ($existingAccounts > 0) {
            echo "Contas já existem no banco ({$existingAccounts} registros). Pulando seeder...\n";
            return;
        }

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

        $createdAccounts = [];
        foreach ($accounts as $account) {
            Db::table('account')->insert($account);
            $createdAccounts[] = $account;
        }

        $this->saveClientIdsToFile($createdAccounts);
    }

    private function saveClientIdsToFile(array $accounts): void
    {
        $storagePath = BASE_PATH . '/storage/client_ids.txt';
        
        $content = "=== IDs dos Clientes Criados ===\n";
        $content .= "Data: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($accounts as $account) {
            $content .= "Nome: {$account['name']}\n";
            $content .= "ID: {$account['id']}\n";
            $content .= "Saldo: R$ " . number_format($account['balance'], 2, ',', '.') . "\n";
            $content .= "---\n";
        }
        
        file_put_contents($storagePath, $content);
        
        echo "IDs dos clientes salvos em: {$storagePath}\n";
        echo $content;
    }
}
