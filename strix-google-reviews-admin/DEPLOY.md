# 🚀 Deploy to GitHub

Complete guide for deploying Strix Google Reviews Admin Panel to GitHub.

## 📋 Prerequisites

- Git installed on your system
- GitHub account
- Repository initialized (completed ✓)

## 🎯 Step-by-Step Deployment

### 1. Create GitHub Repository

1. Go to [GitHub.com](https://github.com) and sign in
2. Click the **"+"** icon → **"New repository"**
3. Repository name: `strix-google-reviews-admin`
4. Description: `Admin panel companion for Strix Google Reviews WordPress plugin`
5. Choose **Public** or **Private** (recommend Private for initial development)
6. **DO NOT** initialize with README, .gitignore, or license (already done)
7. Click **"Create repository"**

### 2. Connect Local Repository to GitHub

After creating the repository, GitHub will show you commands. Use these:

```bash
# Add the remote repository
git remote add origin https://github.com/YOUR_USERNAME/strix-google-reviews-admin.git

# Push to GitHub
git push -u origin main
```

Replace `YOUR_USERNAME` with your actual GitHub username.

### 3. Verify Deployment

1. Go to your repository on GitHub
2. Confirm all files are uploaded:
   - `strix-google-reviews-admin.php` (main plugin file)
   - `README.md` (GitHub documentation)
   - `readme.txt` (WordPress plugin info)
   - `USAGE.md` (detailed usage guide)
   - `assets/css/admin-dashboard.css`
   - `assets/js/admin-custom.js`
   - `index.php`
   - `.gitignore`

## 📝 Repository Structure

```
strix-google-reviews-admin/
├── strix-google-reviews-admin.php    # Main plugin file
├── README.md                         # GitHub documentation
├── readme.txt                        # WordPress plugin info
├── USAGE.md                          # Detailed usage guide
├── DEPLOY.md                         # This deployment guide
├── .gitignore                        # Git ignore rules
├── index.php                         # Security file
└── assets/
    ├── css/
    │   └── admin-dashboard.css       # Dashboard styling
    └── js/
        └── admin-custom.js           # Custom JavaScript
```

## 🏷️ GitHub Repository Settings

### Repository Description
```
Admin panel companion for the Strix Google Reviews WordPress plugin.
Provides developers with comprehensive settings and users with a modern interface
for Google Business Profile integration.
```

### Topics/Tags
```
wordpress-plugin
google-reviews
admin-panel
wordpress
php
google-maps-api
business-reviews
```

### Website
```
https://strixmedia.ru
```

## 🔄 Future Development Workflow

### Making Changes
```bash
# Create feature branch
git checkout -b feature/new-feature

# Make changes and commit
git add .
git commit -m "Add new feature description"

# Push to GitHub
git push origin feature/new-feature

# Create Pull Request on GitHub
```

### Releases
1. Update version in `strix-google-reviews-admin.php`
2. Update changelog in `README.md`
3. Create git tag: `git tag v1.0.1`
4. Push tag: `git push origin v1.0.1`
5. Create release on GitHub

## 📦 Creating WordPress Plugin Zip

For distribution or testing:

```bash
# Create zip file (exclude .git and development files)
cd ..
zip -r strix-google-reviews-admin.zip strix-google-reviews-admin \
  -x "*.git*" "*.DS_Store" "DEPLOY.md"
```

## 🔒 Security Considerations

- **API Keys**: Never commit real API keys to repository
- **Debug Mode**: Disable debug mode in production
- **Access Control**: Use proper WordPress capabilities
- **Input Validation**: All user inputs are sanitized

## 📞 Support

- **Issues**: Use GitHub Issues for bug reports
- **Discussions**: Use GitHub Discussions for questions
- **Email**: support@strixmedia.ru

---

**Deployed by Strix Media Development Team** 🚀