---
name: do-feature
description: Use when implementing a feature or scenario from openspec. Phase 1 writes the failing test and tells the user which test to run and what to commit. Phase 2 implements the feature and gives the verification and commit commands.
---

# Do Feature

Implement an OpenSpec scenario test-first.

## Phase 1: Failing test

1. Search the codebase for existing patterns before writing anything.
2. Write one functional test for the requested scenario.
3. After the test file is saved, give the user the exact command to run the
   failing test BEFORE committing:

       vendor/bin/phpunit --filter <testName>

   The expected result is RED because the new test fails.
4. Wait for the user to confirm the test result. If the result is not RED for
   the right reason, fix the test first.
5. After the user confirms RED, give the user the exact test commit command:

       git add <test files>
       git commit -m "test: add failing test for <scenario>"

Do NOT write implementation code in Phase 1.

## Phase 2: Implementation

1. Implement the smallest production change that satisfies the scenario.
2. After the implementation files are saved, give the user the exact command
   to run the full suite BEFORE committing:

       make test

   The expected result is GREEN.
3. Wait for the user to confirm the test result. If the suite is not GREEN,
   fix the implementation before continuing.
4. After the user confirms GREEN, give the user the exact implementation
   commit command:

       git add <implementation files>
       git commit -m "feat: implement <scenario>"

## Rules

- Never run tests or Git commands yourself.
- Always provide exact commands for the user to run.
- Stop at the end of the requested phase.
- If code, tests, and spec disagree, `openspec/specs/` wins.
