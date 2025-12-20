# Quick Installation Guide

## 🚀 Get Started in 3 Steps

### Step 1: Copy Files
Copy the entire `image-tool` folder to your project's public directory:
```
your-project/
└── public/
    └── image-tool/    ← Copy here
```

### Step 2: Check Requirements
Ensure PHP GD extension is installed:
```bash
php -m | grep -i gd
```

If not installed:
```bash
# Ubuntu/Debian
sudo apt-get install php-gd

# macOS (Homebrew)
brew install php-gd

# Windows
# Enable in php.ini: extension=gd
```

### Step 3: Access the Tool
Open in your browser:
```
http://localhost/image-tool/
```
or
```
http://your-domain.com/image-tool/
```

## ✅ That's It!

The tool is now ready to use. No database, no complex configuration needed.

## 📁 Portable

To use in another project:
1. Copy the entire `image-tool` folder
2. Paste into the new project's public directory
3. Done!

## 🔧 Optional Configuration

Edit `includes/config.php` to customize:
- Maximum file size
- Allowed file types
- Default quality
- Temp directory location

## 🆘 Troubleshooting

**Can't upload files?**
- Check `upload_max_filesize` in php.ini (default: 2M)
- Increase to 10M or more: `upload_max_filesize = 10M`

**Memory errors?**
- Increase `memory_limit` in php.ini
- Recommended: `memory_limit = 256M`

**Permission errors?**
- Ensure temp directory is writable
- Run: `chmod 777 /tmp` (or your temp directory)

## 📖 Full Documentation

See `README.md` for complete documentation and features.
