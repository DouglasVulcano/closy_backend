<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar se os índices já existem antes de criar
        $indexes = DB::select("SHOW INDEX FROM leads WHERE Key_name = 'idx_leads_campaign_status'");
        
        if (empty($indexes)) {
            Schema::table('leads', function (Blueprint $table) {
                // Índice composto crítico para performance
                $table->index(['campaign_id', 'status'], 'idx_leads_campaign_status');
            });
        }

        // Índice para campanhas se não existir
        $campaignIndexes = DB::select("SHOW INDEX FROM campaigns WHERE Key_name = 'idx_campaigns_user_status'");
        
        if (empty($campaignIndexes)) {
            Schema::table('campaigns', function (Blueprint $table) {
                $table->index(['user_id', 'status'], 'idx_campaigns_user_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verificar se os índices existem antes de remover
        try {
            $indexes = DB::select("SHOW INDEX FROM leads WHERE Key_name = 'idx_leads_campaign_status'");
            
            if (!empty($indexes)) {
                DB::statement('ALTER TABLE leads DROP INDEX idx_leads_campaign_status');
            }
        } catch (\Exception $e) {
            // Ignorar erros se o índice não puder ser removido (foreign key constraints)
            Log::info('Could not drop index idx_leads_campaign_status: ' . $e->getMessage());
        }

        try {
            $campaignIndexes = DB::select("SHOW INDEX FROM campaigns WHERE Key_name = 'idx_campaigns_user_status'");
            
            if (!empty($campaignIndexes)) {
                // Verificar se não é um índice de foreign key antes de tentar remover
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'campaigns' 
                    AND COLUMN_NAME = 'user_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if (empty($foreignKeys)) {
                    DB::statement('ALTER TABLE campaigns DROP INDEX idx_campaigns_user_status');
                }
            }
        } catch (\Exception $e) {
            // Ignorar erros se o índice não puder ser removido (foreign key constraints)
            Log::info('Could not drop index idx_campaigns_user_status: ' . $e->getMessage());
        }
    }
};