@echo off
:: This script creates a shortcut in the Windows Startup folder to run the monitoring script at boot
:: It will run the monitor in the background automatically when Windows starts

echo Setting up ServerPulse Auto-Start Shortcut
echo =========================================

set STARTUP_FOLDER=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup
set SCRIPT_DIR=%~dp0
set SHORTCUT_NAME=ServerPulse Monitoring.lnk
set VBS_FILE=%SCRIPT_DIR%start-monitor.vbs

echo Creating shortcut in: %STARTUP_FOLDER%

:: Create a Windows Script Host object to create the shortcut
powershell -Command "$WshShell = New-Object -ComObject WScript.Shell; $Shortcut = $WshShell.CreateShortcut('%STARTUP_FOLDER%\%SHORTCUT_NAME%'); $Shortcut.TargetPath = '%VBS_FILE%'; $Shortcut.WorkingDirectory = '%SCRIPT_DIR%'; $Shortcut.Description = 'Start ServerPulse Monitoring in background'; $Shortcut.Save();"

if exist "%STARTUP_FOLDER%\%SHORTCUT_NAME%" (
    echo Success! Shortcut created successfully.
    echo.
    echo ServerPulse will now automatically start monitoring in the background when Windows starts.
    echo.
    echo You can also double-click start-monitor.vbs to start monitoring immediately.
) else (
    echo Error: Failed to create shortcut.
)

echo.
echo Press any key to exit...
pause > nul
