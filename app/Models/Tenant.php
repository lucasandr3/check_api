<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'data',
        'schema_name',
        'database_name',
        'status',
        'settings',
    ];

    protected $casts = [
        'data' => 'array',
        'settings' => 'array',
        'status' => 'string',
    ];

    // Não usar auto-incremento, permitir IDs customizados
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Inicializar conexão com o schema do tenant
     */
    public function makeCurrent(): void
    {
        // Configurar o search_path para o schema do tenant
        DB::statement("SET search_path TO {$this->schema_name}, public");
        
        // Armazenar o tenant atual na sessão/cache
        app()->instance('current_tenant', $this);
    }

    /**
     * Criar schema no PostgreSQL
     */
    public function createSchema(): bool
    {
        try {
            DB::statement("CREATE SCHEMA IF NOT EXISTS {$this->schema_name}");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Deletar schema no PostgreSQL
     */
    public function deleteSchema(): bool
    {
        try {
            DB::statement("DROP SCHEMA IF EXISTS {$this->schema_name} CASCADE");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verificar se o schema existe
     */
    public function schemaExists(): bool
    {
        $result = DB::select(
            "SELECT schema_name FROM information_schema.schemata WHERE schema_name = ?", 
            [$this->schema_name]
        );
        
        return !empty($result);
    }

    /**
     * Executar migrations no schema do tenant
     */
    public function runMigrations(): bool
    {
        try {
            // Fazer backup do search_path atual
            $currentSearchPath = DB::select("SHOW search_path")[0]->search_path;
            
            // Configurar search_path para o tenant
            DB::statement("SET search_path TO {$this->schema_name}");
            
            // Criar tabela migrations se não existir
            if (!$this->migrationTableExists()) {
                $this->createMigrationTable();
            }
            
            // Executar migrations
            $this->executeMigrations();
            
            // Restaurar search_path
            DB::statement("SET search_path TO {$currentSearchPath}");
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verificar se tabela migrations existe
     */
    private function migrationTableExists(): bool
    {
        $result = DB::select(
            "SELECT table_name FROM information_schema.tables WHERE table_schema = ? AND table_name = 'migrations'",
            [$this->schema_name]
        );
        
        return !empty($result);
    }

    /**
     * Criar tabela migrations
     */
    private function createMigrationTable(): void
    {
        DB::statement("
            CREATE TABLE {$this->schema_name}.migrations (
                id SERIAL PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INTEGER NOT NULL
            )
        ");
    }

    /**
     * Executar migrations do diretório tenant
     */
    private function executeMigrations(): void
    {
        $migrationPath = database_path('migrations/tenant');
        
        if (!is_dir($migrationPath)) {
            return;
        }
        
        $migrationFiles = glob($migrationPath . '/*.php');
        
        if (empty($migrationFiles)) {
            return;
        }
        
        // Obter migrations já executadas
        $executedMigrations = DB::select("SELECT migration FROM {$this->schema_name}.migrations");
        $executedList = array_column($executedMigrations, 'migration');
        
        // Calcular próximo batch
        $maxBatch = DB::select("SELECT COALESCE(MAX(batch), 0) as max_batch FROM {$this->schema_name}.migrations");
        $batch = $maxBatch[0]->max_batch + 1;
        
        foreach ($migrationFiles as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            
            if (in_array($migrationName, $executedList)) {
                continue;
            }
            
            try {
                // Executar migration usando include para evitar problemas de classe
                $migration = include $file;
                
                if ($migration && is_object($migration) && method_exists($migration, 'up')) {
                    $migration->up();
                    
                    // Registrar na tabela migrations
                    DB::insert(
                        "INSERT INTO {$this->schema_name}.migrations (migration, batch) VALUES (?, ?)",
                        [$migrationName, $batch]
                    );
                }
            } catch (\Exception $e) {
                // Log do erro mas continue com outras migrations
                \Log::error("Erro ao executar migration {$migrationName}: " . $e->getMessage());
            }
        }
    }

    /**
     * Obter tenant atual
     */
    public static function current(): ?self
    {
        return app('current_tenant');
    }

    /**
     * Verificar se há um tenant ativo
     */
    public static function hasCurrent(): bool
    {
        return app()->bound('current_tenant');
    }

    /**
     * Resetar tenant atual
     */
    public static function forgetCurrent(): void
    {
        app()->forgetInstance('current_tenant');
        DB::statement("SET search_path TO public");
    }

    /**
     * Relacionamento com domínios
     */
    public function domains()
    {
        return $this->hasMany(TenantDomain::class);
    }

    /**
     * Relacionamento com usuários
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope para tenants ativos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Mutator para schema_name
     */
    public function setSchemaNameAttribute($value)
    {
        $this->attributes['schema_name'] = $value ?: "tenant_{$this->id}";
    }

    /**
     * Accessor para schema_name
     */
    public function getSchemaNameAttribute($value)
    {
        return $value ?: "tenant_{$this->id}";
    }

    /**
     * Accessor para name (usando data)
     */
    public function getNameAttribute()
    {
        return $this->data['name'] ?? null;
    }

    /**
     * Mutator para name (usando data)
     */
    public function setNameAttribute($value)
    {
        $data = $this->data ?? [];
        $data['name'] = $value;
        $this->data = $data;
    }
}