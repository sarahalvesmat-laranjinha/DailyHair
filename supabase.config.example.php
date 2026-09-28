<?php
// Copie para supabase.config.php (ignorado pelo Git).
// No Supabase: Connect > Session pooler > View parameters.
// Use a senha do BANCO, não a chave anon/service_role.
return [
    'SUPABASE_DB_HOST' => 'aws-0-SUA-REGIAO.pooler.supabase.com',
    'SUPABASE_DB_PORT' => '5432',
    'SUPABASE_DB_NAME' => 'postgres',
    'SUPABASE_DB_USER' => 'postgres.SEU-PROJECT-REF',
    'SUPABASE_DB_PASSWORD' => 'PREENCHA-SOMENTE-NO-ARQUIVO-LOCAL',
    'SUPABASE_DB_SSLMODE' => 'require',
    'SUPABASE_DB_SCHEMA' => 'dailyhair',
];
