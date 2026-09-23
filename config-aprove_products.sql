USE daily_hair;

UPDATE aprove_products
SET status = 'Em análise'
WHERE status = 'pending';

UPDATE aprove_products
SET status = 'Aprovado'
WHERE status = 'approved';