# Installation Instructions

## Prerequisites

- **OJS**: 3.3.x or 3.4.x
- **PHP**: 7.3 or higher
- **PHP Extensions**: GD or Imagick, mbstring, zip
- **Shell Access**: For installing TCPDF library

## Installation Steps

### Step 1: Install the Plugin

Clone or download the plugin to your OJS installation:

```bash
cd /path/to/ojs/plugins/generic/
git clone https://github.com/ssemerikov/plugin.git reviewerCertificate
cd reviewerCertificate
```

### Step 2: Install TCPDF Library

The plugin requires TCPDF for PDF generation. Install it in the plugin's `lib/` directory:

```bash
cd lib/
wget https://github.com/tecnickcom/TCPDF/archive/refs/tags/6.6.5.tar.gz
tar -xzf 6.6.5.tar.gz
mv TCPDF-6.6.5 tcpdf
rm 6.6.5.tar.gz
```

**Verify installation:**
```bash
ls tcpdf/tcpdf.php
# Should show: tcpdf/tcpdf.php
```

#### Alternative: Manual Download

If `wget` is not available on your server:

1. Download TCPDF from: https://github.com/tecnickcom/TCPDF/releases/tag/6.6.5
2. Extract the archive
3. Upload the extracted folder to: `/plugins/generic/reviewerCertificate/lib/`
4. Rename the folder to `tcpdf`
5. Verify `tcpdf/tcpdf.php` exists

#### Alternative: Use OJS's TCPDF (if available)

If your OJS installation already has TCPDF, the plugin will automatically detect and use it. Check if it exists:

```bash
ls /path/to/ojs/lib/pkp/lib/vendor/tecnickcom/tcpdf/tcpdf.php
```

If this file exists, you can skip installing TCPDF in the plugin directory.

### Step 3: Set Permissions

Ensure proper file permissions:

```bash
cd /path/to/ojs/plugins/generic/reviewerCertificate/
chmod -R 755 .
```

### Step 4: Enable the Plugin

1. Log in to OJS as **Administrator** or **Journal Manager**
2. Navigate to: **Settings → Website → Plugins**
3. Find "Reviewer Certificate Plugin" in the list
4. Click the **checkbox** to enable it
5. Click **Settings** to configure certificate options

### Step 5: Configure Certificate Settings

1. In the plugin settings:
   - Set certificate template text
   - Choose fonts and colors
   - Set minimum completed reviews requirement
   - Enable QR code verification (optional)
2. Click **Preview Certificate** to test your design
3. Save settings

## Troubleshooting

### Error: "TCPDF library not found"

This means TCPDF is not installed. Follow Step 2 above to install it.

**Quick fix:**
```bash
cd /path/to/ojs/plugins/generic/reviewerCertificate/lib/
wget https://github.com/tecnickcom/TCPDF/archive/refs/tags/6.6.5.tar.gz
tar -xzf 6.6.5.tar.gz
mv TCPDF-6.6.5 tcpdf
```

### Error: "Permission denied"

Set proper permissions:
```bash
chmod -R 755 /path/to/ojs/plugins/generic/reviewerCertificate/
```

### Background Images Not Working

Create upload directory with proper permissions:
```bash
mkdir -p /path/to/ojs/files/journals/[JOURNAL_ID]/reviewerCertificate/
chmod -R 775 /path/to/ojs/files/journals/[JOURNAL_ID]/reviewerCertificate/
chown -R www-data:www-data /path/to/ojs/files/journals/[JOURNAL_ID]/reviewerCertificate/
```

(Replace `www-data` with your web server user, might be `apache`, `nginx`, etc.)

### Plugin Not Appearing

1. Clear OJS cache:
   - **Settings → Website → Clear Data Cache**
2. Check file permissions
3. Check OJS error logs: `/path/to/ojs/files/error.log`

## Updating

To update the plugin to the latest version:

```bash
cd /path/to/ojs/plugins/generic/reviewerCertificate/
git pull origin main
```

Then clear OJS cache in the admin interface.

## Uninstallation

1. Disable the plugin in OJS admin
2. Remove the plugin directory:
   ```bash
   rm -rf /path/to/ojs/plugins/generic/reviewerCertificate/
   ```

The plugin's database tables will remain. To remove them, run:

```sql
DROP TABLE IF EXISTS certificates;
```

## About PEAR Packages

**You do NOT need to install these PEAR packages:**
- ❌ File_PDF (0.3.3) - Too old, limited features
- ❌ XML_fo2pdf (0.98) - Requires Java/Apache FOP, unnecessary

**This plugin uses TCPDF** which is modern, actively maintained, and provides all necessary features including:
- QR codes
- Custom fonts
- Images/backgrounds
- Unicode support
- Professional PDF output

## Support

- **Issues**: https://github.com/ssemerikov/plugin/issues
- **Documentation**: See README.md
- **OJS Forums**: https://forum.pkp.sfu.ca/

## License

GNU General Public License v3.0
