ALTER TABLE daily_hair.aprove_products
MODIFY COLUMN status ENUM(
    'Aprovado',
    'Em análise',
    'Recusado'
) NOT NULL DEFAULT 'Em análise';