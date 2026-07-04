# PROMPTING SYSTEM
# Tour Guide Application - Autonomous Development Framework

## OVERVIEW

This prompting system enables autonomous, proactive development of the Tour Guide Application through structured prompting cycles.

## FOLDER STRUCTURE

```
prompting/
├── README.md                          # This file
├── 01_development/                    # Development prompting templates
│   └── 01_MODULE_DEVELOPMENT.md       # Module development prompts
├── 02_testing/                        # Testing prompting templates
│   └── 01_TEST_GENERATION.md          # Test generation prompts
├── 03_revision/                       # Revision prompting templates
│   └── 01_CODE_REVIEW.md             # Code review prompts
├── 04_improvement/                    # Improvement prompting templates
│   └── 01_CODE_OPTIMIZATION.md       # Code optimization prompts
└── 05_cycle/                          # Cycle management prompts
    ├── 00_MASTER_PROMPTING_CYCLE.md  # Master cycle documentation
    ├── 01_STATE_TRACKING.md          # State tracking template
    ├── 02_REPROMPTING.md             # Re-prompting template
    └── 03_ADJUST_PROMPTING.md        # Adjust prompting template
```

## HOW TO USE

### 0. Setup Configuration (WAJIB)

Sebelum menggunakan sistem prompting, **WAJIB** mengkonfigurasi file `config.json`:

```bash
# Edit file konfigurasi
prompting/config.json
```

File ini berisi:
- **Environment config** untuk Linux & Windows (paths, database credentials, passwords)
- **API keys** untuk third-party services
- **Permissions** untuk auto-execution
- **Starting point** untuk development
- **Preferences** untuk coding style dan architecture

Lihat [`README_SETUP.md`](README_SETUP.md) untuk panduan setup lengkap.

### 1. Autonomous Development

Use the master prompting cycle from `05_cycle/00_MASTER_PROMPTING_CYCLE.md`:

```
You are the Autonomous Development AI for Tour Guide Application.

OBJECTIVE:
Execute the complete prompting cycle to develop the Tour Guide Application autonomously.

START_FROM_MODULE: [MODULE_NAME]

[Rest of the master prompt...]
```

**Note:** AI akan membaca `prompting/config.json` untuk mendapatkan konfigurasi environment secara otomatis.

### 2. Manual Development

Use specific prompting templates:

- **Development:** `01_development/01_MODULE_DEVELOPMENT.md`
- **Testing:** `02_testing/01_TEST_GENERATION.md`
- **Revision:** `03_revision/01_CODE_REVIEW.md`
- **Improvement:** `04_improvement/01_CODE_OPTIMIZATION.md`

### 3. Re-prompting

When a prompt fails or needs adjustment, use `05_cycle/02_REPROMPTING.md`.

### 4. Adjust Prompting

When the development direction needs adjustment, use `05_cycle/03_ADJUST_PROMPTING.md`.

## CYCLE PHASES

### Phase 1: Analysis
- Review documentation
- Identify current state
- Define next task
- Generate development plan

### Phase 2: Development
- Execute development prompt
- Generate code
- Review code quality
- Self-correction

### Phase 3: Testing
- Generate test cases
- Execute tests
- Analyze results
- Fix issues

### Phase 4: Revision
- Review implementation
- Compare with requirements
- Identify gaps
- Generate revision prompt

### Phase 5: Improvement
- Analyze performance
- Identify optimization opportunities
- Generate improvement prompt
- Apply improvements

### Phase 6: Documentation
- Update documentation
- Add code comments
- Update API docs
- Sync with master docs

### Phase 7: Cycle Restart
- Save state
- Update progress tracking
- Plan next iteration
- Restart from phase 1

## STATE TRACKING

Track progress using the state tracking template in `05_cycle/01_STATE_TRACKING.md`.

State file location: `/opt/lampp/htdocs/mywisata/prompting/state.json`

## MODULE PRIORITY

See the complete module priority order in `05_cycle/00_MASTER_PROMPTING_CYCLE.md`.

## BEST PRACTICES

1. **Always read documentation first** - Never code without understanding requirements
2. **Always test before revision** - Ensure implementation works before reviewing
3. **Always revise before completion** - Quality assurance is critical
4. **Always document changes** - Maintain documentation parity
5. **Be proactive** - Anticipate issues before they occur
6. **Be autonomous** - Make decisions independently when possible
7. **Track everything** - Maintain complete state tracking
8. **Learn from errors** - Improve prompts based on failures

## ERROR HANDLING

If a phase fails:
1. Log the error
2. Identify root cause
3. Generate recovery prompt
4. Execute recovery
5. Retry the phase
6. If retry fails 3 times, escalate to human

## ESCALATION CRITERIA

Escalate to human if:
- Critical security issues
- Database corruption
- Unrecoverable errors
- Ambiguous requirements
- Architecture conflicts

## PROGRESS TRACKING

Track progress using:
- State file: `state.json`
- TODO list: Updated after each phase
- Module completion: Mark modules as completed

## SUPPORT

For issues with the prompting system:
1. Check the master cycle documentation
2. Review the specific prompting template
3. Check the state file
4. Re-prompt if needed
5. Adjust prompting if direction changes

---

**Version:** 1.0  
**Last Updated:** 2026-06-30  
**Status:** Ready for Autonomous Execution
