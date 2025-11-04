# Timeout Configuration for Batch Certificate Generation

This plugin includes timeout configuration files to prevent web server timeouts during batch certificate generation.

## Configuration Files

### `.user.ini` (PHP FastCGI/CGI)
- Sets `max_execution_time = 300` (5 minutes)
- Sets `max_input_time = 300`
- Works on PHP-FPM and FastCGI environments
- **Most likely to work on shared hosting**

### `.htaccess` (Apache)
- Sets `FcgidIOTimeout = 300` for mod_fcgid
- Sets PHP timeout values for mod_php
- Requires `AllowOverride` enabled
- May not work on all shared hosting

## How It Works

When you install the plugin, these files are automatically included in:
```
plugins/generic/reviewerCertificate/.user.ini
plugins/generic/reviewerCertificate/.htaccess
```

The web server should automatically read these settings and apply longer timeouts for batch operations.

## Testing the Configuration

After installing/updating the plugin:

1. **Test via web interface:**
   - Go to Settings > Website > Plugins > Reviewer Certificate > Settings
   - Try generating certificates for 1-2 reviewers
   - If it succeeds without timeout → Configuration is working!
   - If it still times out → Use CLI method below

2. **Verify settings are loaded:**
   ```bash
   # Create a test PHP file in the plugin directory
   echo "<?php phpinfo(); ?>" > phpinfo_test.php
   ```

   Then visit: `https://yoursite.com/plugins/generic/reviewerCertificate/phpinfo_test.php`

   Look for:
   - `max_execution_time` should be `300`
   - Delete the file after checking for security

## If Timeouts Still Occur

The configuration files may not work if:
- Your hosting provider overrides these settings
- Nginx is used (doesn't read .htaccess)
- Server has disabled user-level PHP configuration
- Network timeouts occur before PHP timeout

### Solution: Use CLI Batch Generator

The plugin includes command-line tools that work regardless of web server configuration:

**Method 1: Automatic (parses config.inc.php)**
```bash
cd /path/to/ojs/plugins/generic/reviewerCertificate
php BATCH_GENERATE_SIMPLE.php 4 33 59 5
```

**Method 2: Manual (explicit credentials)**
```bash
php BATCH_GENERATE_MANUAL.php [db_name] [db_user] [db_pass] localhost [journal_id] [reviewer_ids...]
```

**Example:**
```bash
php BATCH_GENERATE_MANUAL.php mydb_name mydb_user 'password' localhost 4 33 59 5
```

These CLI tools:
- ✅ Never time out
- ✅ Show real-time progress
- ✅ Work on any hosting environment
- ✅ Process unlimited reviewers
- ✅ Provide detailed error messages

## For System Administrators

If you have server access, increase timeouts globally:

### Nginx + PHP-FPM
Edit `/etc/nginx/sites-available/your-site`:
```nginx
location ~ \.php$ {
    fastcgi_read_timeout 300;
    fastcgi_send_timeout 300;
}
```

Edit `/etc/php/8.1/fpm/pool.d/www.conf`:
```ini
request_terminate_timeout = 300
```

Restart services:
```bash
sudo systemctl restart nginx php8.1-fpm
```

### Apache + mod_fcgid
Edit `/etc/apache2/mods-available/fcgid.conf`:
```apache
FcgidIOTimeout 300
FcgidBusyTimeout 300
```

Edit `/etc/apache2/apache2.conf`:
```apache
TimeOut 300
```

Restart Apache:
```bash
sudo systemctl restart apache2
```

### PHP-FPM (Global)
Edit `/etc/php/8.1/fpm/php.ini`:
```ini
max_execution_time = 300
max_input_time = 300
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.1-fpm
```

## Troubleshooting

### Check if .user.ini is loaded
```bash
cd /path/to/plugins/generic/reviewerCertificate
php -r "echo ini_get('max_execution_time');"
```
Should return `300` if working.

### Check if .htaccess is processed
Look in Apache error logs:
```bash
tail -f /var/log/apache2/error.log
```

### Still timing out?
Enable debug logging in the plugin to see exactly where it hangs:
- Check OJS error logs
- Look for "VERSION_20251104_1500" entries
- See if INSERT operations are hanging

For support, provide:
- Server type (Apache/Nginx)
- PHP version and SAPI (check with `php -v` and `php -i | grep "Server API"`)
- Hosting type (shared/VPS/dedicated)
- Error logs showing timeout
