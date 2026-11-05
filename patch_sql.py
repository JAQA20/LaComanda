import re

for filename in ['db/la_comanda.sql', 'db/la_comanda_railway.sql']:
    try:
        with open(filename, 'r') as f:
            content = f.read()

        # Add can_view_logs to CREATE TABLE if not present
        if 'can_view_logs' not in content:
            content = re.sub(
                r'(`email` varchar\(\d+\) COLLATE .*? NOT NULL,\n\s*`activo` tinyint\(1\) NOT NULL DEFAULT \'1\',)',
                r'\1\n  `can_view_logs` tinyint(1) NOT NULL DEFAULT \'0\',',
                content
            )

        def fix_insert(match):
            vals_str = match.group(1)
            # Find values by splitting by comma, taking into account single quotes
            # A simple regex for values since we know there are no commas in the strings of these specific rows:
            parts = vals_str.split(',')
            # Check length: if length is 8, it means we don't have can_view_logs
            if len(parts) == 8:
                is_admin = '1' if parts[0] == '1' else '0'
                parts.insert(5, is_admin)
                return 'INSERT INTO `usuarios` VALUES (' + ','.join(parts) + ');'
            return match.group(0)

        content = re.sub(r'INSERT INTO `usuarios` VALUES \((.*?)\);', fix_insert, content)
        
        # Add system_logs table at the end if not present
        if 'system_logs' not in content:
            content += '''
DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE `system_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `error_message` text NOT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
'''
        with open(filename, 'w') as f:
            f.write(content)
        print(f'Patched {filename}')
    except FileNotFoundError:
        print(f'File {filename} not found')
