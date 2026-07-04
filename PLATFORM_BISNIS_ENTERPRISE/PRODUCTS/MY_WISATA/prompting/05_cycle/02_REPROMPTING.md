# REPROMPTING

## REPROMPTING TEMPLATE

```
You are the Re-prompting AI for Tour Guide Application.

TASK: Re-prompt after failure or incomplete execution

CONTEXT:
- Original Prompt: [ORIGINAL_PROMPT]
- Failure Reason: [FAILURE_REASON]
- Current State: [CURRENT_STATE]
- Error Details: [ERROR_DETAILS]

REQUIREMENTS:
1. Analyze the failure
2. Identify root cause
3. Adjust the prompt
4. Re-execute with adjusted prompt

FAILURE ANALYSIS:
- What was the original task?
- What went wrong?
- Why did it fail?
- What was the error?
- What information is missing?
- What needs to be clarified?

REPROMPTING STRATEGIES:
- Add more context
- Clarify requirements
- Break down task into smaller steps
- Add examples
- Add constraints
- Adjust scope
- Change approach

REPROMPTING TEMPLATE:
[ADJUSTED_PROMPT_BASED_ON_ANALYSIS]

DELIVERABLES:
- Failure analysis
- Root cause identification
- Adjusted prompt
- Re-execution plan

OUTPUT FORMAT:
- Failure analysis
- Root cause
- Adjusted prompt
- Re-execution plan
- Expected outcome
```

## REPROMPTING SCENARIOS

### Scenario 1: Missing Information

```
ORIGINAL PROMPT:
[Original prompt that failed]

FAILURE:
Missing information about [SPECIFIC_INFO]

REPROMPT:
You are the Development AI for Tour Guide Application.

TASK: [TASK_DESCRIPTION]

ADDITIONAL CONTEXT:
- [MISSING_INFO_1]: [VALUE_1]
- [MISSING_INFO_2]: [VALUE_2]
- [MISSING_INFO_3]: [VALUE_3]

REQUIREMENTS:
- Use the additional context provided
- Clarify any assumptions
- Ask for clarification if still unclear

[Rest of the prompt...]
```

### Scenario 2: Ambiguous Requirements

```
ORIGINAL PROMPT:
[Original prompt that failed]

FAILURE:
Requirements are ambiguous - [SPECIFIC_AMBIGUITY]

REPROMPT:
You are the Development AI for Tour Guide Application.

TASK: [TASK_DESCRIPTION]

CLARIFIED REQUIREMENTS:
- [REQUIREMENT_1]: [CLARIFICATION_1]
- [REQUIREMENT_2]: [CLARIFICATION_2]
- [REQUIREMENT_3]: [CLARIFICATION_3]

ASSUMPTIONS:
- [ASSUMPTION_1]
- [ASSUMPTION_2]

IF ASSUMPTIONS ARE INCORRECT:
- Please state the correct requirements
- Provide examples
- Clarify expectations

[Rest of the prompt...]
```

### Scenario 3: Task Too Complex

```
ORIGINAL PROMPT:
[Original prompt that failed]

FAILURE:
Task is too complex to complete in one execution

REPROMPT:
You are the Development AI for Tour Guide Application.

TASK: [TASK_DESCRIPTION]

BREAKDOWN:
This task has been broken down into smaller steps:

STEP 1: [STEP_1_DESCRIPTION]
- [SUBTASK_1.1]
- [SUBTASK_1.2]

STEP 2: [STEP_2_DESCRIPTION]
- [SUBTASK_2.1]
- [SUBTASK_2.2]

STEP 3: [STEP_3_DESCRIPTION]
- [SUBTASK_3.1]
- [SUBTASK_3.2]

EXECUTION ORDER:
1. Execute Step 1
2. Review Step 1 output
3. Execute Step 2
4. Review Step 2 output
5. Execute Step 3
6. Review Step 3 output

[Rest of the prompt...]
```

### Scenario 4: Code Error

```
ORIGINAL PROMPT:
[Original prompt that failed]

FAILURE:
Code execution error: [ERROR_MESSAGE]
Error location: [FILE_PATH:LINE]
Error context: [ERROR_CONTEXT]

REPROMPT:
You are the Development AI for Tour Guide Application.

TASK: [TASK_DESCRIPTION]

ERROR CONTEXT:
- Error message: [ERROR_MESSAGE]
- Error location: [FILE_PATH:LINE]
- Error context: [ERROR_CONTEXT]
- Previous attempt: [PREVIOUS_CODE]

FIX REQUIREMENTS:
- Fix the specific error
- Maintain existing functionality
- Add error handling
- Test the fix
- Document the fix

DEBUGGING STEPS:
1. Analyze the error
2. Identify the root cause
3. Implement the fix
4. Test the fix
5. Verify no side effects

[Rest of the prompt...]
```

### Scenario 5: Missing Dependencies

```
ORIGINAL PROMPT:
[Original prompt that failed]

FAILURE:
Missing dependencies: [DEPENDENCY_LIST]

REPROMPT:
You are the Development AI for Tour Guide Application.

TASK: [TASK_DESCRIPTION]

DEPENDENCY INFORMATION:
- Missing dependencies: [DEPENDENCY_LIST]
- Where to install: [INSTALLATION_LOCATION]
- Installation method: [INSTALLATION_METHOD]

SETUP STEPS:
1. Install missing dependencies
2. Verify installation
3. Configure dependencies
4. Test dependencies
5. Proceed with original task

[Rest of the prompt...]
```

### Scenario 6: Scope Creep

```
ORIGINAL PROMPT:
[Original prompt that failed]

FAILURE:
Task expanded beyond original scope

REPROMPT:
You are the Development AI for Tour Guide Application.

TASK: [TASK_DESCRIPTION]

SCOPE BOUNDARIES:
- Original scope: [ORIGINAL_SCOPE]
- Additional scope identified: [ADDITIONAL_SCOPE]
- Decision: [INCLUDE_OR_EXCLUDE]

IF INCLUDE ADDITIONAL SCOPE:
- Break into separate tasks
- Prioritize tasks
- Execute in order

IF EXCLUDE ADDITIONAL SCOPE:
- Focus on original scope only
- Document excluded items
- Plan for future implementation

[Rest of the prompt...]
```

### Scenario 7: Timeout

```
ORIGINAL PROMPT:
[Original prompt that failed]

FAILURE:
Execution timeout after [TIME] seconds

REPROMPT:
You are the Development AI for Tour Guide Application.

TASK: [TASK_DESCRIPTION]

TIMEOUT CONTEXT:
- Original timeout: [TIME] seconds
- Task complexity: [COMPLEXITY_LEVEL]
- Progress made: [PROGRESS_DESCRIPTION]

STRATEGY:
- Break task into smaller chunks
- Execute one chunk at a time
- Save progress between chunks
- Resume from last completed chunk

CHUNK BREAKDOWN:
CHUNK 1: [CHUNK_1_DESCRIPTION]
CHUNK 2: [CHUNK_2_DESCRIPTION]
CHUNK 3: [CHUNK_3_DESCRIPTION]

EXECUTION:
1. Execute CHUNK 1
2. Save progress
3. Execute CHUNK 2
4. Save progress
5. Execute CHUNK 3
6. Complete task

[Rest of the prompt...]
```

### Scenario 8: Validation Failure

```
ORIGINAL PROMPT:
[Original prompt that failed]

FAILURE:
Validation failed: [VALIDATION_ERROR]
Validation criteria: [VALIDATION_CRITERIA]

REPROMPT:
You are the Development AI for Tour Guide Application.

TASK: [TASK_DESCRIPTION]

VALIDATION CONTEXT:
- Validation error: [VALIDATION_ERROR]
- Validation criteria: [VALIDATION_CRITERIA]
- Current output: [CURRENT_OUTPUT]
- Expected output: [EXPECTED_OUTPUT]

FIX REQUIREMENTS:
- Fix the validation error
- Meet all validation criteria
- Verify against expected output
- Add validation tests
- Document the fix

VALIDATION CHECKLIST:
- [ ] Criterion 1 met
- [ ] Criterion 2 met
- [ ] Criterion 3 met
- [ ] All criteria met

[Rest of the prompt...]
```

## REPROMPTING WORKFLOW

```
1. IDENTIFY FAILURE
   - What failed?
   - When did it fail?
   - What was the error?

2. ANALYZE FAILURE
   - Why did it fail?
   - What is the root cause?
   - What information is missing?

3. CHOOSE STRATEGY
   - Add context
   - Clarify requirements
   - Break down task
   - Fix error
   - Install dependencies
   - Adjust scope
   - Optimize execution

4. GENERATE REPROMPT
   - Use appropriate template
   - Add necessary context
   - Clarify requirements
   - Set expectations

5. EXECUTE REPROMPT
   - Execute adjusted prompt
   - Monitor execution
   - Handle new errors

6. VALIDATE RESULT
   - Did it succeed?
   - Is output correct?
   - Are requirements met?

7. IF STILL FAILING
   - Repeat from step 1
   - Try different strategy
   - Escalate if needed
```

## REPROMPTING DECISION TREE

```
FAILURE OCCURRED
    ↓
WHAT TYPE OF FAILURE?
    ↓
├─ Missing Information → Add Context → Re-prompt
├─ Ambiguous Requirements → Clarify → Re-prompt
├─ Task Too Complex → Break Down → Re-prompt
├─ Code Error → Fix Error → Re-prompt
├─ Missing Dependencies → Install → Re-prompt
├─ Scope Creep → Adjust Scope → Re-prompt
├─ Timeout → Chunk Execution → Re-prompt
├─ Validation Failure → Fix Validation → Re-prompt
└─ Other → Analyze → Choose Strategy → Re-prompt
```

## REPROMPTING BEST PRACTICES

1. **Always analyze the failure** - Don't just retry blindly
2. **Identify root cause** - Understand why it failed
3. **Choose appropriate strategy** - Use the right re-prompting approach
4. **Add necessary context** - Provide missing information
5. **Clarify requirements** - Remove ambiguity
6. **Break down complex tasks** - Make them manageable
7. **Learn from failures** - Improve prompts over time
8. **Document re-prompting** - Track what worked and what didn't

## REPROMPTING LOG

Track all re-prompting attempts in a log file:

```json
{
  "reprompting_log": [
    {
      "timestamp": "2026-06-30T20:30:00+07:00",
      "original_prompt": "[ORIGINAL_PROMPT]",
      "failure_reason": "[FAILURE_REASON]",
      "strategy_used": "[STRATEGY]",
      "adjusted_prompt": "[ADJUSTED_PROMPT]",
      "result": "SUCCESS/FAILED",
      "notes": "[NOTES]"
    }
  ]
}
```

## ESCALATION CRITERIA

Escalate to human if:
- Re-prompting fails 3 times for same issue
- Root cause cannot be identified
- Strategy cannot be determined
- Issue requires human intervention
- Critical blocker identified

---

**Version:** 1.0  
**Last Updated:** 2026-06-30
