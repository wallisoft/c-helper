# c-helper 🤖⚡

**Universal AI Development Proxy** - Turn any codebase into an AI-accessible workspace

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP 7.4+](https://img.shields.io/badge/PHP-7.4+-blue.svg)](https://php.net)

## 📥 Download

**Latest Release:** [c-helper-v1.0.tar.gz](https://github.com/wallisoft/c-helper/releases/download/v1.0.0/c-helper-v1.0.tar.gz) (8KB)

```bash
# Quick install
curl -L https://github.com/wallisoft/c-helper/releases/download/v1.0.0/c-helper-v1.0.tar.gz | tar xz
cd c-helper
./install.sh
```

## What is c-helper?

c-helper is a lightweight HTTP API that bridges the gap between AI assistants (like Claude, ChatGPT, etc.) and your development environment. It enables AI to directly edit files, run builds, and execute commands **without** uploading your entire codebase to the AI's context window.

### The Problem

Traditional AI coding workflows waste massive amounts of tokens:
- 📤 Upload 15KB file → AI edits → Download → Upload again
- 🔄 Repeat 10 times = **180KB+ tokens burned**
- 💸 $$$$ wasted on redundant file transfers

### The Solution

```
AI ←→ c-helper API ←→ Your Codebase
     (HTTP)            (Direct access)
```

With c-helper:
- ✅ AI edits via API → 2KB per request
- ✅ File stays on disk → No re-uploads
- ✅ **85%+ token savings** → Work longer, iterate faster

## Pricing & Editions

### 🆓 Free Edition (Open Source)
**Perfect for:** Individual developers, hobby projects, learning

**Includes:**
- ✅ Full REST API server
- ✅ File editing & sync
- ✅ Command execution
- ✅ Auto-detect build systems
- ✅ SQLite database
- ✅ API key authentication
- ✅ CORS support
- ✅ Docker deployment
- ✅ Community support (GitHub Issues)

**License:** MIT - Use freely, modify, redistribute

```bash
# Get started free
git clone https://github.com/wallisoft/c-helper.git
cd c-helper
./install.sh
```

---

### 💼 Business Edition
**Perfect for:** Teams, enterprises, production deployments

**Everything in Free, plus:**
- ⚡ **Command Whitelisting** - Security controls for production
- 🔐 **LDAP/SSO Integration** - Enterprise authentication
- 📊 **Usage Analytics Dashboard** - Track API usage & costs
- 🔄 **Rate Limiting** - Per-user/project quotas
- 📝 **Audit Logging** - Compliance & security tracking
- 🎫 **Priority Support** - Direct access to maintainers
- 🔧 **Custom Integrations** - Tailored to your workflow
- 📖 **Training Materials** - Onboarding guides & videos
- 🚀 **Deployment Assistance** - Help setting up in your environment

**Pricing:**
- **Small Team (1-5 devs):** £99/month or £990/year (save £198)
- **Team (6-20 devs):** £249/month or £2,490/year (save £498)
- **Enterprise (21+ devs):** Custom pricing - [Contact us](mailto:steve@wallisoft.com)

**30-day free trial available** - No credit card required

[**Get Business Edition →**](mailto:steve@wallisoft.com?subject=c-helper%20Business%20Edition)

---

## Features

🎯 **Language-Agnostic** - Works with ANY codebase (Python, C#, JavaScript, Rust, etc.)  
🔐 **Secure** - API key authentication, configurable permissions  
🪶 **Lightweight** - Pure PHP + SQLite, no dependencies  
⚡ **Fast** - Direct filesystem access, minimal overhead  
🔧 **Flexible** - REST API works with any AI that can make HTTP requests  
📦 **Self-Contained** - Single PHP file + database, deploy anywhere

## Quick Start (Free Edition)

### Installation

```bash
# Clone or download
git clone https://github.com/wallisoft/c-helper.git
cd c-helper

# Generate API key
export C_HELPER_KEY="your-secret-key-$(date +%s)"

# Start server
php -S 0.0.0.0:8888 c-helper.php
```

**That's it!** Your API is running on `http://localhost:8888`

### Register Your First Project

```bash
curl -X POST http://localhost:8888/api/projects \
  -H "X-API-Key: $C_HELPER_KEY" \
  -H "Content-Type: application/json" \
  -d '{"name": "my-app", "path": "/home/user/my-app"}'
```

### Let AI Edit a File

```bash
# AI writes this via API
curl -X PUT "http://localhost:8888/api/file?project=my-app&path=src/main.py" \
  -H "X-API-Key: $C_HELPER_KEY" \
  -H "Content-Type: application/json" \
  -d '{"content": "print(\"Hello from AI!\")"}'

# Sync to filesystem
curl -X POST "http://localhost:8888/api/sync?project=my-app" \
  -H "X-API-Key: $C_HELPER_KEY"

# Build the project
curl -X POST "http://localhost:8888/api/build?project=my-app" \
  -H "X-API-Key: $C_HELPER_KEY"
```

## API Reference

### Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/file?project=X&path=Y` | Read file |
| `PUT` | `/api/file?project=X&path=Y` | Write file to DB |
| `POST` | `/api/sync?project=X` | Sync DB → filesystem |
| `POST` | `/api/build?project=X` | Auto-detect & build |
| `POST` | `/api/exec` | Execute command |
| `GET` | `/api/projects` | List projects |
| `POST` | `/api/projects` | Register project |

Full API documentation: [API.md](API.md)

## Use Cases

### 1. AI Pair Programming
AI assistant edits your code directly, you see changes live, iterate instantly.

### 2. Automated Refactoring
AI reads codebase structure, makes systematic changes across 100+ files.

### 3. Documentation Generation
AI scans code, generates/updates docs, commits via API.

### 4. CI/CD Integration
AI proposes changes → c-helper creates branch → Human reviews PR.

## Real-World Example: Avalised

**The Project:** A YAML-driven RAD IDE with C# Avalonia renderer that designs itself  
**The Challenge:** Long AI sessions hitting token limits  
**The Solution:** c-helper API for direct file access

**Results:**
- 📉 Token usage: 180K → 24K per session (**87% reduction**)
- ⏱️ Session length: 30 min → 3.5 hours (**7× longer**)
- 💰 Cost savings: $3.60 → $0.48 per session
- 🚀 Productivity: Build entire UI framework in one session

[Read the full case study →](CASE-STUDY-AVALISED.md)

## Security Considerations

⚠️ c-helper executes arbitrary commands - **use with caution!**

**Production Best Practices:**
- ✅ Use strong API keys (`openssl rand -hex 32`)
- ✅ Bind to localhost only (`127.0.0.1:8888`)
- ✅ Use reverse proxy with HTTPS (nginx/caddy)
- ✅ Implement rate limiting (Business Edition)
- ✅ Run as non-root user
- ✅ Whitelist allowed commands (Business Edition)

## Roadmap

**v1.1** (Q1 2025)
- [ ] Command whitelisting (Business)
- [ ] Rate limiting per API key (Business)
- [ ] WebSocket support for live updates
- [ ] Python/Node.js SDKs

**v1.2** (Q2 2025)
- [ ] VS Code extension
- [ ] GitHub App integration
- [ ] Usage analytics dashboard (Business)
- [ ] LDAP/SSO integration (Business)

**v2.0** (Q3 2025)
- [ ] Multi-user support
- [ ] Project-level permissions
- [ ] Integrated git operations
- [ ] AI usage cost tracking

## Contributing

Contributions welcome! This is a simple tool solving a real problem. Keep it:
- **Small** - One file if possible
- **Fast** - No unnecessary abstractions
- **Practical** - Solve real dev workflow issues

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## Support

**Free Edition:** Community support via [GitHub Issues](https://github.com/wallisoft/c-helper/issues)

**Business Edition:** 
- Email: steve@wallisoft.com
- Priority response: 24 hours
- Custom integrations available

## License

Free Edition: MIT License - Use it, fork it, sell it, whatever!

Business Edition: Commercial license - See [LICENSE-BUSINESS.md](LICENSE-BUSINESS.md)

## Credits

**Created by:**
- Steve Wallis - Wallisoft (Concept & Implementation)
- Claude (Anthropic) - AI coding buddy & documentation

**Born from:** The Avalised project - A YAML-driven RAD IDE that designs itself

## Why "c-helper"?

Short for "Claude helper" (originally), but works with ANY AI that can make HTTP requests. The "c" can stand for:
- **Claude** (Anthropic)
- **ChatGPT** (OpenAI)
- **Copilot** (GitHub)
- **Cody** (Sourcegraph)
- **Cursor** (Cursor.sh)

...or just **"Coding helper"** ⚡

---

**⭐ Star this repo if c-helper saved you tokens!**

Made with 🇬🇧 English understatement in Eastbourne, UK

[Download Latest Release](https://github.com/wallisoft/c-helper/releases/latest) | [Report Bug](https://github.com/wallisoft/c-helper/issues) | [Request Feature](https://github.com/wallisoft/c-helper/issues) | [Get Business Edition](mailto:steve@wallisoft.com)
