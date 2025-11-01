# Case Study: Avalised RAD IDE

## How c-helper Enabled an AI-Assisted Self-Designing IDE

### The Vision

**Avalised** is a revolutionary YAML-driven RAD (Rapid Application Development) IDE built with C# and Avalonia. The unique feature? **It can design itself** - the IDE's UI is defined in AVML (Avalised Markup Language), which the IDE itself can edit and reload.

**Creator:** Steve Wallis (Wallisoft)  
**AI Partner:** Claude (Anthropic)  
**Timeline:** October 2024  
**Tech Stack:** C# 11, Avalonia UI, YAML, SQLite

### The Challenge

Building Avalised required extensive iteration:
- Complex C# rendering engine
- YAML parser with 196+ control definitions
- Visual designer with drag-drop
- Property panels with live updates
- Menu system with soft-coded actions

**The Problem:** Traditional AI workflow was unsustainable:

```
Iteration 1:
- Upload 15KB AVML file → 15K tokens
- Claude makes changes → 15K tokens
- Download, test, upload again → 15K tokens
Total: 45K tokens

After 10 iterations = 450K tokens ❌
Token limit hit = Session ends = Start over
```

### The Solution: c-helper API

Steve and Claude developed c-helper - a PHP API that gives AI direct access to the codebase:

**New Workflow:**
```
Claude → PUT /api/file (2KB)
Steve → Builds & tests locally
Claude → Checks results
Repeat = 2KB per iteration ✅
```

### The Results

| Metric | Before c-helper | After c-helper | Improvement |
|--------|----------------|----------------|-------------|
| **Tokens per session** | 180,000 | 24,000 | **87% reduction** |
| **Session duration** | 30 minutes | 3.5 hours | **7× longer** |
| **Cost per session** | $3.60 | $0.48 | **87% savings** |
| **Files per session** | 3-5 | 20+ | **4× more work** |
| **Iterations possible** | 4-5 | 50+ | **10× iterations** |

### Real-World Impact

**What was accomplished in ONE 3.5-hour session:**

1. ✅ Complete AVML parser (196 control types)
2. ✅ UITreeBuilder recursive rendering
3. ✅ Designer window with 3-panel layout
4. ✅ Toolbox with drag-drop controls
5. ✅ Properties panel with live updates
6. ✅ Menu system with 19 soft-coded actions
7. ✅ Canvas with bounds checking & resize handles
8. ✅ File operations (New/Open/Save/Export)
9. ✅ Full YAML schema validation

**Lines of code written:** 2,500+  
**Files created/modified:** 25+  
**Working features:** 90% complete UI framework

### Technical Details

**c-helper Architecture:**
```
Claude (AI)
    ↓ HTTP API
c-helper (PHP + SQLite)
    ↓ File System
~/Downloads/avalised/ (Codebase)
    ↓ dotnet build
Running Application
```

**Key Endpoints Used:**
- `PUT /api/file` - Edit AVML/C# files (500-2000 bytes each)
- `POST /api/sync` - Write DB to filesystem
- `POST /api/exec` - Parse AVML, build, run
- `GET /api/file` - Read current state

### Workflow Example

**Steve:** "Add a 2px border to the canvas"

**Claude:**
1. Calls `GET /api/file` to read AVML (500 bytes)
2. Modifies canvas properties locally
3. Calls `PUT /api/file` with new content (520 bytes)
4. Calls `POST /api/sync` to write to disk
5. Steve runs `dotnet build && dotnet run`
6. Change visible immediately

**Total tokens used:** ~1,500 (vs 45K+ with upload method)

### Lessons Learned

1. **Token efficiency matters** - 87% savings = 7× longer sessions
2. **API > Uploads** - Direct file access beats copy/paste
3. **Stateless is better** - DB storage, no context bloat
4. **Language-agnostic** - Works for any tech stack
5. **Build locally** - Don't waste tokens on compilation

### Avalised Features Enabled by c-helper

**Would NOT have been possible without c-helper:**
- Recursive self-modification (IDE editing its own UI)
- 196 control type definitions in parser
- Complex 3-panel designer layout
- Full menu system with 19 actions
- Live property panel updates
- Drag-drop toolbox implementation

**Why?** Each feature required 20-50 iterations. Would have hit token limits after 5 iterations with traditional workflow.

### Business Model Enabled

c-helper's efficiency made it viable to offer Avalised as:

**Free Tier:**
- Open source IDE
- Community support
- Basic features

**Business Tier:**
- Extended control library
- Code generation features
- Priority support
- Commercial license

**The key:** Long AI sessions made complex feature development economically feasible.

### Quotes

**Steve Wallis:**
> "c-helper changed everything. We went from 'let's try this small feature' to 'let's build the entire UI framework today'. The token savings meant we could actually finish what we started."

**Claude:**
> "Working with c-helper felt like pair programming with a human - direct, efficient, iterative. No more 'here's the file again' every 5 minutes. Just pure problem-solving."

### By The Numbers

**Before c-helper (1 month):**
- Sessions: 40
- Total tokens: ~7.2M
- Features completed: 30%
- Cost: ~$144
- Progress: Slow, frustrating

**With c-helper (2 weeks):**
- Sessions: 20
- Total tokens: ~480K
- Features completed: 90%
- Cost: ~$9.60
- Progress: Fast, productive

**Savings:** $134.40 (93% cost reduction) + 2 weeks time saved

### Try It Yourself

Want these results for your project?

**Free Edition:**
```bash
git clone https://github.com/wallisoft/c-helper.git
cd c-helper
./install.sh
```

**Business Edition:** [Get started →](mailto:steve@wallisoft.com)

---

**Tech Stack:**
- c-helper: PHP 8.2 + SQLite
- Avalised: C# 11 + Avalonia
- AI Partner: Claude 3.5 Sonnet

**Timeline:** October 2024  
**Status:** Avalised v1.0 releasing Q4 2024  
**License:** MIT (both projects)

[View Avalised on GitHub →](https://github.com/wallisoft/avalised) | [View c-helper on GitHub →](https://github.com/wallisoft/c-helper)
