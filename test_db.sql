-- Verifica struttura tabelle
SELECT 
    t.name AS TableName,
    c.name AS ColumnName,
    ty.name AS DataType,
    c.max_length AS MaxLength,
    c.is_nullable AS IsNullable
FROM 
    sys.tables t
    INNER JOIN sys.columns c ON t.object_id = c.object_id
    INNER JOIN sys.types ty ON c.user_type_id = ty.user_type_id
WHERE 
    t.name IN ('articles', 'categories')
ORDER BY 
    t.name, c.column_id;

-- Verifica relazioni
SELECT 
    OBJECT_NAME(f.parent_object_id) AS TableName,
    COL_NAME(fc.parent_object_id, fc.parent_column_id) AS ColumnName,
    OBJECT_NAME(f.referenced_object_id) AS ReferenceTableName,
    COL_NAME(fc.referenced_object_id, fc.referenced_column_id) AS ReferenceColumnName
FROM 
    sys.foreign_keys AS f
    INNER JOIN sys.foreign_key_columns AS fc ON f.object_id = fc.constraint_object_id
WHERE 
    OBJECT_NAME(f.parent_object_id) = 'articles';

-- Verifica dati
SELECT 'Articoli per categoria' AS Info,
    c.name AS Categoria,
    COUNT(a.name) AS NumeroArticoli
FROM 
    dbo.categories c
    LEFT JOIN dbo.articles a ON c.category_id = a.category_id
GROUP BY 
    c.name
ORDER BY 
    c.name; 