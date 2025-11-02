# TCPDF Library Directory

This directory should contain the TCPDF library for PDF generation.

## Installation

Download TCPDF 6.6.5 and extract it here:

```bash
cd /path/to/ojs/plugins/generic/reviewerCertificate/lib/
wget https://github.com/tecnickcom/TCPDF/archive/refs/tags/6.6.5.tar.gz
tar -xzf 6.6.5.tar.gz
mv TCPDF-6.6.5 tcpdf
rm 6.6.5.tar.gz
```

After installation, you should have:
- `lib/tcpdf/tcpdf.php`
- `lib/tcpdf/config/`
- `lib/tcpdf/fonts/`
- etc.

## Alternative: Direct Download

If wget is not available:

1. Go to: https://github.com/tecnickcom/TCPDF/releases/tag/6.6.5
2. Download the source code (tar.gz or zip)
3. Extract to this directory and rename folder to `tcpdf`

The plugin will automatically detect and use this bundled TCPDF library.
