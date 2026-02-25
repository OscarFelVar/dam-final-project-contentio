# Contentio -- GitHub Project Configuration (Up to Sprint 0)

## Project Overview

This document describes the GitHub configuration and project management
setup for the **Contentio** final project (FP DAM), covering all
configuration decisions and structure defined up to **Sprint 0 --
Setup**.

------------------------------------------------------------------------

# 1. Repository Structure

The project follows a separated architecture:

-   `/backend` → Laravel 11 (API REST)
-   `/frontend` → Vue 3 (SPA)
-   `/docker` → Docker configuration files
-   `docker-compose.yml`
-   `README.md`

Branch strategy:

-   `main` → Stable branch
-   `develop` → Integration branch

------------------------------------------------------------------------

# 2. GitHub Project Board Configuration

## Workflow Columns (Kanban)

-   Backlog (Grey)
-   In Progress (Yellow)
-   Testing (Purple)
-   Done (Green)

Optional: - Blocked (Red)

------------------------------------------------------------------------

# 3. Custom Fields Configuration

## Status

Single select field used to control workflow state.

## Priority

Single select: - High - Medium - Low

## Size

Task complexity estimation: - XS - S - M - L - XL

## Story Points (Estimate)

Fibonacci scale: - 1 - 2 - 3 - 5 - 8

Used to measure sprint workload and velocity.

## Start Date

Filled when task moves to In Progress.

## Target Date

Used to assign sprint deadline.

------------------------------------------------------------------------

# 4. Labels Strategy

## Technical Area Labels

-   backend
-   frontend
-   database
-   infrastructure
-   devops

## Work Type Labels

-   feature
-   setup
-   configuration
-   refactor
-   bug

## Special State Labels

-   blocked
-   risk

------------------------------------------------------------------------

# 5. Sprint 0 -- Setup (22/02 -- 28/02)

## Sprint Goal

Prepare full development environment and base architecture.

------------------------------------------------------------------------

## Tasks Breakdown

### S0-01 -- Setup Git repository structure

Priority: Medium\
Story Points: 1\
Size: S\
Labels: infrastructure, setup

------------------------------------------------------------------------

### S0-02 -- Configure Docker development environment

Priority: High\
Story Points: 5\
Size: L\
Labels: infrastructure, devops, setup

------------------------------------------------------------------------

### S0-03 -- Initialize Laravel backend

Priority: High\
Story Points: 3\
Size: M\
Labels: backend, setup

------------------------------------------------------------------------

### S0-04 -- Initialize Vue frontend

Priority: High\
Story Points: 3\
Size: M\
Labels: frontend, setup

------------------------------------------------------------------------

### S0-05 -- Configure CORS between frontend and backend

Priority: High\
Story Points: 2\
Size: S/M\
Labels: backend, configuration

------------------------------------------------------------------------

### S0-06 -- Create initial database schema

Priority: Medium\
Story Points: 3\
Size: M\
Labels: database, backend

------------------------------------------------------------------------

## Total Sprint 0 Story Points

17 Story Points

Distribution: - 1 small task (1 SP) - 1 large task (5 SP) - 3 medium
tasks (3 SP) - 1 small-medium task (2 SP)

Balanced sprint workload for a single developer.

------------------------------------------------------------------------

# 6. Workflow Execution Rules

-   Only 1 task in progress at a time.
-   Start Date filled when task moves to In Progress.
-   Task moves to Done only when:
    -   Code implemented
    -   Manually tested
    -   No console errors
    -   Changes committed and pushed

------------------------------------------------------------------------

# 7. Professional Practices Applied

-   Clear issue naming convention: \[S0-XX\] Task Name
-   Consistent English usage
-   Relative estimation using Story Points
-   Logical priority assignment
-   Structured sprint planning
-   Clean Kanban flow

------------------------------------------------------------------------

Document generated as part of the project management setup for
Contentio.
