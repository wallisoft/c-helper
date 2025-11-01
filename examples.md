# c-helper Examples

## Example 1: AI Adds a New Feature

**Human:** "Claude, add a user authentication module to my Express app"

**Claude does:**

```bash
# 1. Create auth.js
curl -X PUT "http://localhost:8888/api/file?project=my-api&path=middleware/auth.js" \
  -H "X-API-Key: $KEY" \
  -d '{"content": "const jwt = require(\"jsonwebtoken\");\n..."}'

# 2. Update routes
curl -X PUT "http://localhost:8888/api/file?project=my-api&path=routes/protected.js" \
  -H "X-API-Key: $KEY" \
  -d '{"content": "const auth = require(\"../middleware/auth\");\n..."}'

# 3. Sync to filesystem
curl -X POST "http://localhost:8888/api/sync?project=my-api" -H "X-API-Key: $KEY"

# 4. Run tests
curl -X POST "http://localhost:8888/api/exec" \
  -H "X-API-Key: $KEY" \
  -d '{"project": "my-api", "command": "npm test"}'
```

## Example 2: Refactor Across Multiple Files

**Human:** "Rename all instances of `getUserData` to `fetchUserProfile`"

**Claude does:**

```bash
# 1. Find all files
curl -X POST "http://localhost:8888/api/exec" \
  -H "X-API-Key: $KEY" \
  -d '{"project": "my-app", "command": "grep -r \"getUserData\" src/"}'

# 2. Edit each file via API
# ... (Claude loops through results)

# 3. Sync all changes
curl -X POST "http://localhost:8888/api/sync?project=my-app" -H "X-API-Key: $KEY"
```

## Example 3: Generate Documentation

**Human:** "Document all my API endpoints"

**Claude does:**

```bash
# 1. Read existing routes
curl "http://localhost:8888/api/file?project=my-api&path=routes/api.js" \
  -H "X-API-Key: $KEY"

# 2. Generate markdown docs
curl -X PUT "http://localhost:8888/api/file?project=my-api&path=docs/API.md" \
  -H "X-API-Key: $KEY" \
  -d '{"content": "# API Documentation\n\n## GET /api/users\n..."}'

# 3. Sync to disk
curl -X POST "http://localhost:8888/api/sync?project=my-api" -H "X-API-Key: $KEY"
```

## Example 4: Fix Bugs Across Codebase

**Human:** "Fix all console.log statements to use our logger"

**Claude does:**

```bash
# 1. Find all console.log
curl -X POST "http://localhost:8888/api/exec" \
  -H "X-API-Key: $KEY" \
  -d '{"project": "my-app", "command": "find src -name \"*.js\" -exec grep -l \"console.log\" {} \\;"}'

# 2. Read and fix each file
# ... (Claude processes each file)

# 3. Sync changes
curl -X POST "http://localhost:8888/api/sync?project=my-app" -H "X-API-Key: $KEY"

# 4. Verify
curl -X POST "http://localhost:8888/api/exec" \
  -H "X-API-Key: $KEY" \
  -d '{"project": "my-app", "command": "npm run lint"}'
```

## Example 5: Token Comparison

### Traditional Workflow (❌ Token Heavy)

```
1. Upload main.py (5KB) → 5K tokens
2. AI edits, sends back → 5K tokens  
3. Human: "Add error handling"
4. Upload main.py again → 5K tokens
5. AI edits → 5K tokens
6. Human: "Add logging"
7. Upload main.py AGAIN → 5K tokens
8. AI edits → 5K tokens

Total: 30K tokens for 3 edits
```

### c-helper Workflow (✅ Token Light)

```
1. AI: PUT /api/file → 500 tokens
2. Human: "Add error handling"
3. AI: PUT /api/file → 500 tokens
4. Human: "Add logging"  
5. AI: PUT /api/file → 500 tokens

Total: 1.5K tokens for 3 edits
```

**95% token savings!** 🎉
