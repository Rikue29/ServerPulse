Set WshShell = CreateObject("WScript.Shell")
WshShell.Run chr(34) & "C:\laragon\www\ServerPulse\run-monitor.bat" & Chr(34), 0
Set WshShell = Nothing
