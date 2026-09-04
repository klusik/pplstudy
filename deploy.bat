@echo off

rem Project: PPL Study
rem File: deploy.bat
rem Module Type: Windows Wrapper Script
rem
rem Purpose:
rem   Provides a reliable Windows entry point for deployment packaging or FTP
rem   upload through the PowerShell deployment workflow.
rem
rem Responsibilities:
rem   - Locate Windows PowerShell directly
rem   - Forward command-line arguments to scripts\deploy.ps1
rem   - Preserve the PowerShell exit code
rem   - Avoid PHP CLI and application bootstrap dependencies during packaging
rem
rem Author: Rudolf Klusal
rem License: MIT License for application code. See LICENSE.
rem
rem Notes:
rem   - Keep comments and docstrings intact when modifying this file.

setlocal
set "POWERSHELL_EXE=%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe"
if not exist "%POWERSHELL_EXE%" (
    echo Windows PowerShell was not found at "%POWERSHELL_EXE%".
    exit /b 1
)

"%POWERSHELL_EXE%" -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\deploy.ps1" %*
set "EXIT_CODE=%ERRORLEVEL%"
endlocal & exit /b %EXIT_CODE%
