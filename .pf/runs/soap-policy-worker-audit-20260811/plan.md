# Plan: SOAP Policy Worker Audit

Date: 2026-08-11

## Goal

Validate the corrected SOAP policy with Process Forge shell-workers:

- `ext-soap` is required by Composer/GitHub build metadata.
- Joomla package installation does not block when SOAP is absent.
- Joomla post-install/post-update message warns that tracking will not work without SOAP.
- The built release ZIP contains the corrected installer and language strings.

## Tasks

- T30: Composer/GitHub SOAP requirement audit.
- T31: Joomla installer SOAP warning audit.
- T32: Package archive and Joomla local smoke audit.
- T33: Reviewer summary after T30-T32 finish.

## Product Code Policy

Workers must not edit product code.
