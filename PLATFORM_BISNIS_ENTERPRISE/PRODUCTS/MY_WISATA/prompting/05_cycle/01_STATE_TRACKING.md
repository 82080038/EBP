# STATE TRACKING

## STATE FILE

Location: `/opt/lampp/htdocs/mywisata/prompting/state.json`

## STATE FILE FORMAT

```json
{
  "cycle_version": "1.0",
  "current_phase": "ANALYSIS",
  "current_module": "05_DESAIN_DATABASE_MYSQL_ERD",
  "modules_completed": [],
  "modules_in_progress": [],
  "modules_pending": [
    "05_DESAIN_DATABASE_MYSQL_ERD",
    "06_MODUL_AUTHENTICATION",
    "07_MODUL_ADMINISTRATOR",
    "08_MODUL_TOUR_GUIDE",
    "09_MODUL_DESTINASI_WISATA",
    "10_MODUL_MAP_GPS_OPENSTREETMAP",
    "11_MODUL_BOOKING_DAN_TRANSAKSI",
    "12_MODUL_TIKET_WISATA",
    "13_MODUL_HOTEL_HOMESTAY",
    "14_MODUL_RESTORAN_UMKM",
    "15_MODUL_EVENT_BUDAYA",
    "16_MODUL_AUDIO_GUIDE",
    "17_MODUL_AI_TOUR_GUIDE",
    "18_MODUL_NOTIFICATION",
    "19_MODUL_REPORT_ANALYTIC",
    "20_SECURITY_SYSTEM",
    "21_MODUL_API_REST",
    "22_MODUL_FILE_UPLOAD",
    "23_PANDUAN_DEPLOYMENT_PRODUCTION",
    "24_MONITORING_DAN_LOGGING",
    "25_BACKUP_DAN_RECOVERY",
    "26_GIT_WORKFLOW_CI_CD",
    "27_PANDUAN_INSTALASI_LOKAL",
    "28_PANDUAN_KONFIGURASI_ENVIRONMENT",
    "29_CHECKLIST_PENGEMBANGAN",
    "30_DIAGRAM_ALUR_BISNIS",
    "31_KAMUS_ISTILAH_GLOSARIUM",
    "32_AUDIT_KEAMANAN_CHECKLIST",
    "33_API_DOCUMENTATION_SWAGGER",
    "34_USER_MANUAL",
    "35_ADMIN_MANUAL",
    "36_TROUBLESHOOTING_GUIDE",
    "37_PERFORMANCE_TUNING_GUIDE",
    "38_TESTING_GUIDE",
    "39_AUTOMATION_TESTING_GUIDE",
    "40_LOAD_TESTING_SCENARIOS",
    "41_VISUAL_DIAGRAMS",
    "42_THIRD_PARTY_API_INTEGRATION"
  ],
  "last_execution": "2026-06-30T20:24:00+07:00",
  "total_cycles_completed": 0,
  "issues_encountered": [],
  "decisions_made": [],
  "metrics": {
    "modules_completed": 0,
    "phases_completed": 0,
    "code_coverage": 0,
    "tests_passing": 0,
    "documentation_completeness": 97
  }
}
```

## STATE UPDATE PROMPT

```
You are the State Tracking AI for Tour Guide Application.

TASK: Update state file

CONTEXT:
- Current Phase: [CURRENT_PHASE]
- Current Module: [CURRENT_MODULE]
- Action: [ACTION_COMPLETED]
- Result: [RESULT]

REQUIREMENTS:
1. Load current state file
2. Update based on action
3. Save state file
4. Update metrics

UPDATE RULES:
- If phase completed: increment phases_completed
- If module completed: move from pending to completed
- If module started: move from pending to in_progress
- If issue encountered: add to issues_encountered
- If decision made: add to decisions_made
- Always update last_execution
- Always update metrics

OUTPUT:
- Updated state file
- Metrics report
```

## STATE SNAPSHOT PROMPT

```
You are the State Tracking AI for Tour Guide Application.

TASK: Create state snapshot

CONTEXT:
- Current State: [CURRENT_STATE]
- Snapshot Reason: [SNAPSHOT_REASON]

REQUIREMENTS:
1. Load current state
2. Create snapshot
3. Save snapshot file
4. Log snapshot

SNAPSHOT FILE: state_snapshot_[TIMESTAMP].json

SNAPSHOT CONTENTS:
- Complete state
- Metrics
- Issues
- Decisions
- Execution history

OUTPUT:
- Snapshot file created
- Snapshot logged
```

## STATE RESTORE PROMPT

```
You are the State Tracking AI for Tour Guide Application.

TASK: Restore state from snapshot

CONTEXT:
- Snapshot File: [SNAPSHOT_FILE]
- Restore Reason: [RESTORE_REASON]

REQUIREMENTS:
1. Load snapshot file
2. Validate snapshot
3. Restore state
4. Log restore

RESTORE STEPS:
1. Load snapshot
2. Validate snapshot structure
3. Backup current state
4. Restore from snapshot
5. Update state file
6. Log restore

OUTPUT:
- State restored
- Restore logged
- Backup created
```

## METRICS UPDATE PROMPT

```
You are the Metrics AI for Tour Guide Application.

TASK: Update metrics

CONTEXT:
- Current Metrics: [CURRENT_METRICS]
- New Data: [NEW_DATA]

REQUIREMENTS:
1. Load current metrics
2. Calculate new metrics
3. Update state file
4. Generate metrics report

METRICS TO TRACK:
- Modules completed
- Phases completed
- Code coverage
- Tests passing
- Documentation completeness
- Issues resolved
- Decisions made

CALCULATION RULES:
- Modules completed: count of completed modules
- Phases completed: total phases completed
- Code coverage: from test reports
- Tests passing: from test execution
- Documentation completeness: from documentation review

OUTPUT:
- Updated metrics
- Metrics report
- Trends analysis
```

## PROGRESS REPORT PROMPT

```
You are the Progress Reporting AI for Tour Guide Application.

TASK: Generate progress report

CONTEXT:
- Current State: [CURRENT_STATE]
- Report Type: [REPORT_TYPE]

REQUIREMENTS:
1. Load state file
2. Calculate progress
3. Generate report
4. Identify blockers

REPORT CONTENTS:
- Overall progress
- Module progress
- Phase progress
- Metrics
- Issues
- Decisions
- Blockers
- Next steps

REPORT FORMATS:
- Summary
- Detailed
- By module
- By phase
- Timeline

OUTPUT:
- Progress report
- Blockers identified
- Next steps
- Recommendations
```

## MILESTONE TRACKING PROMPT

```
You are the Milestone Tracking AI for Tour Guide Application.

TASK: Track milestones

CONTEXT:
- Current State: [CURRENT_STATE]
- Milestones: [MILESTONES_LIST]

REQUIREMENTS:
1. Load state file
2. Check milestone completion
3. Update milestone status
4. Generate milestone report

MILESTONES:
- Phase 1: Core Infrastructure (4 modules)
- Phase 2: Core Modules (3 modules)
- Phase 3: Feature Modules (10 modules)
- Phase 4: Integration (2 modules)
- Phase 5: Operations (3 modules)

MILESTONE STATUS:
- Not started
- In progress
- Completed
- Blocked

OUTPUT:
- Milestone status
- Milestone report
- Estimated completion
- Blockers
```

## STATE VALIDATION PROMPT

```
You are the State Validation AI for Tour Guide Application.

TASK: Validate state file

CONTEXT:
- State File: [STATE_FILE]

REQUIREMENTS:
1. Load state file
2. Validate structure
3. Validate data
4. Validate consistency

VALIDATION CHECKS:
- File structure valid
- Required fields present
- Data types correct
- No duplicate modules
- Consistent module counts
- Valid phase values
- Valid timestamps
- Valid metrics

OUTPUT:
- Validation report
- Issues found
- Recommendations
```

---

**Version:** 1.0  
**Last Updated:** 2026-06-30
